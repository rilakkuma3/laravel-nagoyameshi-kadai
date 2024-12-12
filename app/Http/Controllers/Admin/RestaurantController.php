<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\RegularHoliday;

class RestaurantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $keyword = $request->keyword;

        if ($keyword !== null) {
            $restaurants = Restaurant::where('name', 'like', "%{$keyword}%")->paginate(15);
            $total = $restaurants->total();
        } else {
            $restaurants = Restaurant::paginate(15);
            $total = Restaurant::count();
        }

        return view('admin.restaurants.index', compact('restaurants', 'total', 'keyword'));
    }

    public function show($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function create()
    {
        $categories = Category::all();
       
        $regular_holidays = RegularHoliday::all();
        return view('admin.restaurants.create',compact('categories', 'regular_holidays'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'image|max:2048',
            'description' => 'required',
            'lowest_price' => 'required|numeric|min:0|lte:highest_price',
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|digits:7',
            'address' => 'required',
            'opening_time' => 'required|before:closing_time',
            'closing_time' => 'required|after:opening_time',
            'seating_capacity' => 'required|numeric|min:0'
        ]);

        $restaurant = new Restaurant();
        $restaurant->name = $request->input('name');
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');
       


        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('public/restaurants');
            $restaurant->image = basename($image);
        } else {
            $restaurant->image = '';
        }

        $restaurant->save();

        $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);

        $regular_holiday_ids = $request->input('regular_holiday_ids');
        $restaurant->regular_holidays()->sync($regular_holiday_ids);

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を登録しました。');
    }

    public function edit($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $categories = Category::all();
        $category_ids = $restaurant->categories->pluck('id')->toArray();

        $regular_holidays = RegularHoliday::all();

        return view('admin.restaurants.edit', compact('restaurant','categories', 'category_ids','regular_holidays'));
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'image' => 'image|max:2048',
            'description' => 'required',
            'lowest_price' => 'required|numeric|min:0|lte:highest_price',
            'highest_price' => 'required|numeric|min:0|gte:lowest_price',
            'postal_code' => 'required|digits:7',
            'address' => 'required',
            'opening_time' => 'required|before:closing_time',
            'closing_time' => 'required|after:opening_time',
            'seating_capacity' => 'required|numeric|min:0'
        ]);

        $restaurant->name = $request->input('name');
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('public/restaurants');
            $restaurant->image = basename($image);
        }

        $restaurant->save();

        $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);

        $regular_holiday_ids = $request->input('regular_holiday_ids');
        $restaurant->regular_holidays()->sync($regular_holiday_ids);


        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を編集しました。');
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を削除しました。');
    }
}
