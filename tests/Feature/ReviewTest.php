<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;

class ReviewTest extends TestCase
{
    use RefreshDatabase;
    
    // レビュー一覧ページ(indexアクション)
    // 1.未ログインのユーザーは会員側のレビュー一覧ページにアクセスできない
    public function test_guest_cannot_access_reviews_index()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.index', $restaurant));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_notsubscribed_user_can_access_reviews_index()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    // 3.ログイン済みの有料会員は会員側のレビュー一覧ページにアクセスできる
    public function test_subscribed_user_can_access_reviews_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reviews.index', $restaurant));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は会員側のレビュー一覧ページにアクセスできない
    public function test_adminUser_cannot_access_reviews_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reviews.index', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }

    // レビュー投稿ページ(createアクション)
    // 1.未ログインのユーザーは会員側のレビュー投稿ページにアクセスできない
    public function test_guest_cannot_access_reviews_create()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は会員側のレビュー投稿ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_reviews_create()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reviews.create', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は会員側のレビュー投稿ページにアクセスできる
    public function test_subscribed_user_can_access_reviews_create()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reviews.create', $restaurant));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は会員側のレビュー投稿ページにアクセスできない
    public function test_adminUser_cannot_access_reviews_create()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reviews.create', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }

    // レビュー投稿機能(storeアクション)
    // 1.未ログインのユーザーはレビューを投稿できない
    public function test_guest_cannot_store_reviews()
    {
        $restaurant = Restaurant::factory()->create();

        $review = [
            'score' => 1,
            'content' => 'テスト',
            'restaurant_id' => $restaurant->id
        ];

        $response = $this->post(route('restaurants.reviews.store', $restaurant), $review);
        $this->assertDatabaseMissing('reviews', $review);

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はレビューを投稿できない
    public function test_notsubscribed_user_cannot_store_reviews()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $review = [
            'score' => 1,
            'content' => 'テスト',
            'restaurant_id' => $restaurant->id
        ];

        $response = $this->actingAs($user)->post(route('restaurants.reviews.store', $restaurant), $review);
        $this->assertDatabaseMissing('reviews', $review);

        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員はレビューを投稿できる
    public function test_subscribed_user_can_store_reviews()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');

        $review = [
            'score' => 1,
            'content' => 'テスト',
            'restaurant_id' => $restaurant->id
        ];

        $response = $this->actingAs($user)->post(route('restaurants.reviews.store', $restaurant), $review);
        $this->assertDatabaseHas('reviews', $review);

        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // 4.ログイン済みの管理者はレビューを投稿できない
    public function test_adminUser_cannot_store_reviews()
    {
        $restaurant = Restaurant::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $review = [
            'score' => 1,
            'content' => 'テスト',
            'restaurant_id' => $restaurant->id
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reviews.store', $restaurant), $review);

        $response->assertRedirect(route('admin.home'));
    }


    // レビュー編集ページ(editアクション)
    // 1.未ログインのユーザーは会員側のレビュー編集ページにアクセスできない
    public function test_guest_cannot_access_reviews_edit()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はレビュー編集ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_reviews_edit()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は会員側の他人のレビュー編集ページにアクセスできない
    public function test_subscribed_user_cannot_access_others_reviews_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $others_user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $others_user->id
        ]);

        $response = $this->actingAs($user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // 4.ログイン済みの有料会員は会員側の自身のレビュー編集ページにアクセスできる
    public function test_subscribed_user_can_access_reviews_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->get(route('restaurants.reviews.edit', [$restaurant, $review]));
        $response->assertStatus(200);
    }

    // 5.ログイン済みの管理者は会員側のレビュー編集ページにアクセスできない
    public function test_adminUser_cannot_access_reviews_edit()
    {
        $user = User::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reviews.edit', [$restaurant, $review]));

        $response->assertRedirect(route('admin.home'));
    }


    // レビュー更新機能(updateアクション)
    // 1.未ログインのユーザーはレビューを更新できない
    public function test_guest_cannot_update_reviews()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        
        $old_details = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $new_details = [
            'score' => 2,
            'content' => '新テスト',
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->patch(route('restaurants.reviews.update', [$restaurant, $old_details]), $new_details);

        $this->assertDatabaseMissing('reviews', $new_details);
        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はレビューを更新できない
    public function test_notsubscribed_user_cannot_update_reviews()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        
        $old_details = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $new_details = [
            'score' => 2,
            'content' => '新テスト',
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->patch(route('restaurants.reviews.update', [$restaurant, $old_details]), $new_details);

        $this->assertDatabaseMissing('reviews', $new_details);
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は他人のレビューを更新できない
    public function test_subscribed_user_cannot_update_others_reviews()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $others_user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        
        $old_details = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $others_user->id
        ]);

        $new_details = [
            'score' => 2,
            'content' => '新テスト',
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->patch(route('restaurants.reviews.update', [$restaurant, $old_details]), $new_details);

        $this->assertDatabaseMissing('reviews', $new_details);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // 4.ログイン済みの有料会員は自身のレビューを更新できる
    public function test_subscribed_user_can_update_reviews()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();
        
        $old_details = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $new_details = [
            'score' => 2,
            'content' => '新テスト',
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->patch(route('restaurants.reviews.update', [$restaurant, $old_details]), $new_details);

        $this->assertDatabaseHas('reviews', $new_details);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // 5.ログイン済みの管理者はレビューを更新できない
    public function test_adminUser_cannot_update_reviews()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        
        $old_details = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $new_details = [
            'score' => 2,
            'content' => '新テスト',
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('restaurants.reviews.update', [$restaurant, $old_details]), $new_details);

        $this->assertDatabaseMissing('reviews', $new_details);
        $response->assertRedirect(route('admin.home'));
    }


    // レビュー削除機能(destroyアクション)
    // 1.未ログインのユーザーはレビューを削除できない
    public function test_guest_cannot_destroy_reviews()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はレビューを削除できない
    public function test_notsubscribed_user_cannot_destroy_reviews()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は他人のレビューを削除できない
    public function test_subscribed_user_cannot_destroy_others_reviews()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $others_user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $others_user->id
        ]);

        $response = $this->actingAs($user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // 4.ログイン済みの有料会員は自身のレビューを削除できる
    public function test_subscribed_user_can_destroy_reviews()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('restaurants.reviews.index', $restaurant));
    }

    // 5.ログイン済みの管理者はレビューを削除できない
    public function test_adminUser_cannot_destroy_reviews()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $review = Review::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('restaurants.reviews.destroy', [$restaurant, $review]));

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
        $response->assertRedirect(route('admin.home'));
    }
}
