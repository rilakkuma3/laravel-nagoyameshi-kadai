<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // お気に入り一覧ページ(indexアクション)
    // 1.未ログインのユーザーは会員側のお気に入り一覧ページにアクセスできない
    public function test_guest_cannot_access_favorites_index()
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は会員側のお気に入り一覧ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_favorites_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は会員側のお気に入り一覧ページにアクセスできる
    public function test_subscribed_user_can_access_favorites_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は会員側のお気に入り一覧ページにアクセスできない
    public function test_adminUser_cannot_access_favorites_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('favorites.index'));

        $response->assertRedirect(route('admin.home'));
    }

    // お気に入り追加機能(storeアクション)
    // 1.未ログインのユーザーはお気に入りに追加できない
    public function test_guest_cannot_store_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $restaurant_user = [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->post(route('favorites.store', $restaurant->id), $restaurant_user);
        $this->assertDatabaseMissing('restaurant_user', $restaurant_user);

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はお気に入りに追加できない
    public function test_notsubscribed_user_cannot_store_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $restaurant_user = [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->post(route('favorites.store', $restaurant->id), $restaurant_user);
        $this->assertDatabaseMissing('restaurant_user', $restaurant_user);

        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員はお気に入りに追加できる
    public function test_subscribed_user_can_store_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $restaurant_user = [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->post(route('favorites.store', $restaurant->id), $restaurant_user);
        $this->assertDatabaseHas('restaurant_user', $restaurant_user);

        $response->assertStatus(302);
    }

    // 4.ログイン済みの管理者はお気に入りに追加できない
    public function test_adminUser_cannot_store_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $restaurant_user = [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('favorites.store', $restaurant->id), $restaurant_user);
        $this->assertDatabaseMissing('restaurant_user', $restaurant_user);

        $response->assertRedirect(route('admin.home'));
    }


    // お気に入り解除機能(destroyアクション)
    // 1.未ログインのユーザーはお気に入りを解除できない
    public function test_guest_cannot_destroy_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $restaurant->users()->attach($user->id);

        $response = $this->delete(route('favorites.destroy', $restaurant->id));
        $this->assertDatabaseHas('restaurant_user', [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はお気に入りを解除できない
    public function test_notsubscribed_user_cannot_destroy_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $restaurant->users()->attach($user->id);

        $response = $this->actingAs($user)->delete(route('favorites.destroy', $restaurant->id));
        $this->assertDatabaseHas('restaurant_user', [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員はお気に入りを解除できる
    public function test_subscribed_user_can_destroy_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $restaurant->users()->attach($user->id);

        $response = $this->actingAs($user)->delete(route('favorites.destroy', $restaurant->id));
        $this->assertDatabaseMissing('restaurant_user', [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response->assertStatus(302);
    }

    // 4.ログイン済みの管理者はお気に入りを解除できない
    public function test_adminUser_cannot_destroy_favorites()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $restaurant->users()->attach($user->id);

        $response = $this->actingAs($admin, 'admin')->delete(route('favorites.destroy', $restaurant->id));
        $this->assertDatabaseHas('restaurant_user', [
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response->assertRedirect(route('admin.home'));
    }

}
