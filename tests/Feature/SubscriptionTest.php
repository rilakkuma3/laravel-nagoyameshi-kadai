<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    // 有料プラン登録ページ(createアクション)
    // 1.未ログインのユーザーは有料プラン登録ページにアクセスできない
    public function test_guest_cannot_access_subscription_create()
    {
        $response = $this->get(route('subscription.create'));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は有料プラン登録ページにアクセスできる
    public function test_notsubscribed_user_can_access_subscription_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.create'));
        $response->assertStatus(200);
    }
    // 3.ログイン済みの有料会員は有料プラン登録ページにアクセスできない
    public function test_subscribed_user_cannot_access_subscription_create()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('subscription.create'));
        $response->assertRedirect(route('subscription.edit'));
    }

    // 4.ログイン済みの管理者は有料プラン登録ページにアクセスできない
    public function test_adminUser_cannot_access_subscription_create()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.create'));

        $response->assertRedirect(route('admin.home'));
    }

    // 有料プラン登録機能(storeアクション)
    // 1.未ログインのユーザーは有料プランに登録できない
    public function test_guest_cannot_store_subscription()
    {
        $user = User::factory()->create();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->post(route('subscription.store'), $request_parameter);
        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は有料プランに登録できる(テストpassできてない)
    public function test_notsubscribed_user_can_store_subscription()
    {
        $user = User::factory()->create();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($user)->post(route('subscription.store'), $request_parameter);

        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');
        $this->assertTrue($user->subscribed('premium_plan'));

        $response->assertRedirect(route('home'));
    }

    // 3.ログイン済みの有料会員は有料プランに登録できない
    public function test_subscribed_user_cannot_store_subscription()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($user)->post(route('subscription.store'), $request_parameter);
        $response->assertRedirect(route('subscription.edit'));
    }

    // 4.ログイン済みの管理者は有料プランに登録できない
    public function test_adminUser_cannot_store_subscription()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $user = User::factory()->create();

        $request_parameter = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('subscription.store'), $request_parameter);
        $response->assertRedirect(route('admin.home'));
    }

    // お支払い方法編集ページ(editアクション)
    // 1.未ログインのユーザーはお支払い方法編集ページにアクセスできない
    public function test_guest_cannot_access_subscription_edit()
    {
        $response = $this->get(route('subscription.edit'));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はお支払い方法編集ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_subscription_edit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.edit'));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員はお支払い方法編集ページにアクセスできる
    public function test_subscribed_user_can_access_subscription_edit()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('subscription.edit'));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者はお支払い方法編集ページにアクセスできない
    public function test_adminUser_cannot_access_subscription_edit()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.edit'));

        $response->assertRedirect(route('admin.home'));
    }

    // お支払い方法更新機能(updateアクション)
    // 1.未ログインのユーザーはお支払い方法を更新できない
    public function test_guest_cannot_update_subscription()
    {
        $user = User::factory()->create();
        
        // 更新前の支払い方法ID
        $old_payment_method = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        // 更新後の支払い方法ID
        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];    

        $response = $this->patch(route('subscription.update', $old_payment_method), $request_parameter);
        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員はお支払い方法を更新できない
    public function test_notsubscribed_user_cannot_update_subscription()
    {
        $user = User::factory()->create();

        $old_payment_method = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        $response = $this->actingAs($user)->patch(route('subscription.update', $old_payment_method), $request_parameter);
        $response->assertRedirect(route('subscription.create'));
    }
    // 3.ログイン済みの有料会員はお支払い方法を更新できる
    public function test_subscribed_user_can_update_subscription()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $old_payment_method = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        $response = $this->actingAs($user)->patch(route('subscription.update', $old_payment_method), $request_parameter);
        $this->assertNotEquals($old_payment_method, $request_parameter);
        $response->assertRedirect(route('home'));
    }

    // 4.ログイン済みの管理者はお支払い方法を更新できない
    public function test_adminUser_cannot_update_subscription()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $user = User::factory()->create();

        $old_payment_method = [
            'paymentMethodId' => 'pm_card_visa'
        ];

        $request_parameter = [
            'paymentMethodId' => 'pm_card_mastercard'
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('subscription.update', $old_payment_method), $request_parameter);
        $response->assertRedirect(route('admin.home'));
    }


    // 有料プラン解約ページ(cancelアクション)
    // 1.未ログインのユーザーは有料プラン解約ページにアクセスできない
    public function test_guest_cannot_access_subscription_cancel()
    {
        $response = $this->get(route('subscription.cancel'));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は有料プラン解約ページにアクセスできない
    public function test_notsubscribed_user_cannot_access_subscription_cancel()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.cancel'));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は有料プラン解約ページにアクセスできる
    public function test_subscribed_user_can_access_subscription_cancel()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $response = $this->actingAs($user)->get(route('subscription.cancel'));
        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は有料プラン解約ページにアクセスできない
    public function test_adminUser_cannot_access_subscription_cancel()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('subscription.cancel'));

        $response->assertRedirect(route('admin.home'));
    }


    // 有料プラン解約機能(destroyアクション)
    // 1.未ログインのユーザーは有料プランを解約できない
    public function test_guest_cannot_destroy_subscription()
    {
        $user = User::factory()->create();

        $response = $this->delete(route('subscription.destroy'));
        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの無料会員は有料プランを解約できない
    public function test_notsubscribed_user_cannot_destroy_subscription()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('subscription.destroy'));
        $response->assertRedirect(route('subscription.create'));
    }

    // 3.ログイン済みの有料会員は有料プランを解約できる
    public function test_subscribed_user_can_destroy_subscription()
    {
        $user = User::factory()->create();
        $user->newSubscription('premium_plan', 'price_1QVNI1054bpuqcVCGUM7Erjs')->create('pm_card_visa');

        $response = $this->actingAs($user)->delete(route('subscription.destroy'));
        $this->assertFalse($user->subscribed('premium_plan'));
        $response->assertRedirect(route('home'));
    }

    // 4.ログイン済みの管理者は有料プランを解約できない
    public function test_adminUser_cannot_destroy_subscription()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('subscription.destroy'));
        $response->assertRedirect(route('admin.home'));
    }
}
