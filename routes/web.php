<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



require __DIR__.'/auth.php';

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');
    Route::resource('users', Admin\UserController::class);
    Route::resource('restaurants', RestaurantController::class);
});

Route::get('admin/restaurants/index', [Admin\RestaurantController::class, 'index'])->name('admin.restaurants.index');

Route::get('admin/restaurants/create', [Admin\RestaurantController::class, 'create'])->name('admin.restaurants.create');

Route::get('admin/restaurants/show={restaurant}', [Admin\RestaurantController::class, 'show'])->name('admin.restaurants.show');

Route::resource('restaurants', RestaurantController::class, )->only('store', 'update', 'destroy');

Route::get('admin/restaurants/edit', [Admin\RestaurantController::class, 'edit'])->name('admin.restaurants.edit');


