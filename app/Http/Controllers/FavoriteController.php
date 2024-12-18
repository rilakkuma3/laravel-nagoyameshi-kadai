<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;
use App\Models\User;

class FavoriteController extends Controller
{
    // お気に入り一覧ページ(indexアクション)
    public function index()
    {
        $favorite_restaurants = Auth::user()->favorite_restaurants()->orderBy('created_at', 'desc')->paginate(15);

        return view('favorites.index', compact('favorite_restaurants'));
    }

    // お気に入り追加機能(storeアクション)
    public function store($restaurant_id)
    {
        Auth::user()->favorite_restaurants()->attach($restaurant_id);

        return back()->with('flash_message', 'お気に入りに追加しました。');
    }

    // お気に入り解除機能(destroyアクション)
    public function destroy($restaurant_id)
    {
        Auth::user()->favorite_restaurants()->detach($restaurant_id);

        return back()->with('flash_message', 'お気に入りを解除しました。');
    }
}
