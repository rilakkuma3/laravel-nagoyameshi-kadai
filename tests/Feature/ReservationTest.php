<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Support\Facades\Hash;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    // 予約一覧ページ(indexアクション)
    // 1.未ログインのユーザーは会員側の予約一覧ページにアクセスできない
    public function test_guest_cannot_access_reservations_index()
    {
        $response = $this->get(route('reservations.index'));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は会員側の予約一覧ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_reservations_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reservations.index'));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は会員側の予約一覧ページにアクセスできる
    public function test_subscribed_user_can_access_reservations_index()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('reservations.index'));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は会員側の予約一覧ページにアクセスできない
    public function test_adminUser_cannot_access_reservations_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('reservations.index'));

        $response->assertRedirect(route('admin.home'));
    }

    // 予約ページ(createアクション)
    // 1.未ログインのユーザーは会員側の予約ページにアクセスできない
    public function test_guest_cannot_access_reservations_create()
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('restaurants.reservations.create', $restaurant));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は会員側の予約ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_reservations_create()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reservations.create', $restaurant));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は会員側の予約ページにアクセスできる
    public function test_subscribed_user_can_access_reservations_create()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($user)->get(route('restaurants.reservations.create', $restaurant));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は会員側の予約ページにアクセスできない
    public function test_adminUser_cannot_access_reservations_create()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $restaurant = Restaurant::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('restaurants.reservations.create', $restaurant));

        $response->assertRedirect(route('admin.home'));
    }

    // 予約機能(storeアクション)
    // 1.未ログインのユーザーは予約できない
    public function test_guest_cannot_store_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $reservation = [
            'reserved_datetime' => now(),
            'number_of_people' => 1,
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->post(route('restaurants.reservations.store', $restaurant), $reservation);
        $this->assertDatabaseMissing('reservations', $reservation);

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は予約できない
    public function test_notsubscribed_user_cannot_store_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $reservation = [
            'reserved_datetime' => now(),
            'number_of_people' => 1,
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->post(route('restaurants.reservations.store', $restaurant), $reservation);
        $this->assertDatabaseMissing('reservations', $reservation);

        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は予約できる
    public function test_subscribed_user_can_store_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');

        $reservation = [
            'reservation_date' => '2024-09-29',
            'reservation_time' => '10:00',
            'number_of_people' => 1,
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($user)->post(route('restaurants.reservations.store', $restaurant), $reservation);
        $this->assertDatabaseHas('reservations', [
            'reserved_datetime' => '2024-09-29 10:00',
            'number_of_people' => 1,
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response->assertRedirect(route('reservations.index'));
    }

    // 4.ログイン済みの管理者は予約できない
    public function test_adminUser_cannot_store_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $reservation = [
            'reserved_datetime' => now(),
            'number_of_people' => 1,
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('restaurants.reservations.store', $restaurant), $reservation);
        $this->assertDatabaseMissing('reservations', $reservation);

        $response->assertRedirect(route('admin.home'));
    }


    // 予約キャンセル機能(destroyアクション)
    // 1.未ログインのユーザーは予約をキャンセルできない
    public function test_guest_cannot_destroy_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は予約をキャンセルできない
    public function test_notsubscribed_user_cannot_destroy_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();

        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);

        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は他人の予約をキャンセルできない
    public function test_subscribed_user_cannot_destroy_others_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');
        $others_user = User::factory()->create();

        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $others_user->id
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);

        $response->assertRedirect(route('reservations.index'));
    }

    // 4.ログイン済みの有料会員は自身の予約をキャンセルできる
    public function test_subscribed_user_can_destroy_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1Q2OJvCn6kb1TaWJwhjmcYCr')->create('pm_card_visa');

        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);

        $response->assertRedirect(route('reservations.index'));
    }

    // 5.ログイン済みの管理者は予約をキャンセルできない
    public function test_adminUser_cannot_destroy_reservations()
    {
        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $reservation = Reservation::factory()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('reservations.destroy', $reservation));
        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);

        $response->assertRedirect(route('admin.home'));
    }

}
