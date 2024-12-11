<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TermController;

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
    Route::resource('categories', Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('restaurants', Admin\RestaurantController::class);
    Route::resource('company', Admin\CompanyController::class)->only(['index', 'edit', 'update']);
    Route::resource('terms', Admin\TermController::class)->only(['index', 'edit', 'update']);

});


