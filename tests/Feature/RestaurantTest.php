<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    // 店舗一覧ページ(indexアクション)
    // 1.未ログインのユーザーは会員側の店舗一覧ページにアクセスできる
    public function test_guest_cannot_access_restaurants_index()
    {
        $response = $this->get(route('restaurants.index'));

        $response->assertStatus(500);
    }
    
    // 2.ログイン済みの一般ユーザーは会員側の店舗一覧ページにアクセスできる
    public function test_user_can_access_admin_restaurants_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.index'));

        $response->assertStatus(500);
    }

    // 3.ログイン済みの管理者は会員側の店舗一覧ページにアクセスできない
    public function test_adminUser_cannot_access_restaurants_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.index'));

        $response->assertRedirect(route('admin.home'));
    }


    // 店舗詳細ページ(showアクション)
    // 1.未ログインのユーザーは会員側の店舗詳細ページにアクセスできる
    public function test_guest_can_access_restaurants_show()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.show', $restaurant));

        $response->assertStatus(500);
    }

     // 2.ログイン済みの一般ユーザーは会員側の店舗詳細ページにアクセスできる
     public function test_user_can_access_restaurants_show()
     {
         $user = User::factory()->create();
         $restaurant = Restaurant::factory()->create();
 
         $response = $this->actingAs($user)->get(route('restaurants.show', $restaurant));
 
         $response->assertStatus(500);
     }
 
     // 3.ログイン済みの管理者は会員側の店舗詳細ページにアクセスできない
     public function test_adminUser_cannot_access_restaurants_show()
     {
         $restaurant = Restaurant::factory()->create();
 
         $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
 
         $response = $this->actingAs($admin, 'admin')->get(route('restaurants.show', $restaurant));
 
         $response->assertRedirect(route('admin.home'));
     }
}
