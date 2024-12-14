<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // レビュー一覧ページ(indexアクション)
    public function index(Restaurant $restaurant)
    {
        $user = Auth::user();

        if ($user->subscribed('premium_plan')){
            $reviews = Review::where('restaurant_id', $restaurant->id)->orderBy('created_at', 'desc')->paginate(5);
        } else {
            $reviews = Review::where('restaurant_id', $restaurant->id)->orderBy('created_at', 'desc')->paginate(3);
        }

        return view('reviews.index', compact('restaurant', 'reviews'));
    }

    // レビュー投稿ページ(createアクション)
    public function create(Restaurant $restaurant)
    {
        return view('reviews.create', compact('restaurant'));
    }

    // レビュー投稿機能(storeアクション)
    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'score' => 'required|integer|between:1,5',
            'content' => 'required'
        ]);

        $review = new Review();
        $review->score = $request->input('score');
        $review->content = $request->input('content');
        $review->user_id = Auth::id();
        $review->restaurant_id = $restaurant->id;
        $review->save();

        return to_route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを投稿しました。');
    }

    // レビュー編集ページ(editアクション)
    public function edit(Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()){
            return to_route('restaurants.reviews.index', $restaurant)->with('error_message', '不正なアクセスです。');
        }

        return view('reviews.edit', compact('review', 'restaurant'));
    }

    // レビュー更新機能(updateアクション)
    public function update(Request $request, Restaurant $restaurant, Review $review)
    {
        if ($review->user_id !== Auth::id()){
            return to_route('restaurants.reviews.index', $restaurant)->with('error_message', '不正なアクセスです。');
        }

        $request->validate([
            'score' => 'required|integer|between:1,5',
            'content' => 'required'
        ]);

        $review->score = $request->input('score');
        $review->content = $request->input('content');
        $review->update();
        
        return to_route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを編集しました。');
    }

    // レビュー削除機能(destroyアクション)
    public function destroy(Restaurant $restaurant, Review $review)
    {        
        if ($review->user_id !== Auth::id()){
            return to_route('restaurants.reviews.index', $restaurant)->with('error_message', '不正なアクセスです。');
        }
      
        $review->delete();

        return to_route('restaurants.reviews.index', $restaurant)->with('flash_message', 'レビューを削除しました。');
    }
}
