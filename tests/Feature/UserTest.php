<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // 会員情報ページ(indexアクション)
    // 1.未ログインのユーザーは会員側の会員情報ページにアクセスできない
    public function test_guest_cannot_access_user_index()
    {
        $response = $this->get(route('user.index'));

        $response->assertRedirect(route('login'));
    }

    // 2.ログイン済みの一般ユーザーは会員側の会員情報ページにアクセスできる
    public function test_user_can_access_admin_user_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertStatus(200);
    }

    // 3.ログイン済みの管理者は会員側の会員情報ページにアクセスできない
    public function test_adminUser_cannot_access_user_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('user.index'));

        $response->assertRedirect(route('admin.home'));
    }

    // 会員情報編集ページ(editアクション)
    // 1.未ログインのユーザーは会員側の会員情報編集ページにアクセスできない
    public function test_guest_cannot_access_user_edit()
    {
        $user = User::factory()->create();

        $response = $this->get(route('user.edit', ['user' => $user->id]));

        $response->assertRedirect(route('user.index'));
    }

    // 2.ログイン済みの一般ユーザーは会員側の他人の会員情報編集ページにアクセスできない
    public function test_user_cannot_access_others_user_edit()
    {
        $user = User::factory()->create();
        $other_user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.edit', ['user' => $other_user->id]));

        $response->assertRedirect(route('user.index'));
    }

    // 3.ログイン済みの一般ユーザーは会員側の自身の会員情報編集ページにアクセスできる
    public function test_user_can_access_user_edit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.edit', ['user' => $user->id]));

        $response->assertStatus(200);
    }

    // 4.ログイン済みの管理者は会員側の会員情報編集ページにアクセスできない
    public function test_adminUser_cannot_access_user_edit()
    {
        $user = User::factory()->create();
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('user.edit', ['user' => $user->id]));

        $response->assertRedirect(route('admin.home'));
    }

    // 会員情報更新機能(updateアクション)
    // 1.未ログインのユーザーは会員情報を更新できない
    public function test_guest_cannot_update_user()
    {
        $old_details = User::factory()->create();
        $new_details = [
            'name' => '新テスト',
            'kana' => 'シンテスト',
            'email' => 'newtest@gmail.com',
            'postal_code' => '1000000',
            'address' => '新テスト',
            'phone_number' => '10000000000',
            'birthday' => '10000000',
            'occupation' => '新テスト'
        ];

        $response = $this->patch(route('user.update', $old_details->id), $new_details);

        $this->assertDatabaseMissing('users', $new_details);
        $response->assertRedirect(route('user.index'));
    }

    // 2.ログイン済みの一般ユーザーは他人の会員情報を更新できない
    public function test_user_cannot_update_others_user()
    {
        $user = User::factory()->create();
        $other_user = User::factory()->create();

        $old_details = User::factory()->create();
        $new_details = [
            'name' => '新テスト',
            'kana' => 'シンテスト',
            'email' => 'newtest@gmail.com',
            'postal_code' => '1000000',
            'address' => '新テスト',
            'phone_number' => '10000000000',
            'birthday' => '10000000',
            'occupation' => '新テスト'
        ];

        $response = $this->actingAs($user)->patch(route('user.update', $other_user->id), $new_details);
        $this->assertDatabaseMissing('users', $new_details);
        $response->assertRedirect(route('user.index'));
    }

    // 3.ログイン済みの一般ユーザーは自身の会員情報を更新できる
    public function test_user_can_update_user()
    {
        $user = User::factory()->create([
            'name' => 'テスト',
            'kana' => 'テスト',
            'email' => 'test@gmail.com',
            'postal_code' => 0000000,
            'address' => 'テスト',
            'phone_number' => 00000000000,
            'birthday' => 20000101,
            'occupation' => 'テスト'
        ]);

        $new_details = [
            'name' => '新テスト',
            'kana' => 'シンテスト',
            'email' => 'newtest@gmail.com',
            'postal_code' => 1000000,
            'address' => '新テスト',
            'phone_number' => 10000000000,
            'birthday' => 20000101,
            'occupation' => '新テスト'
         ];

         $response = $this->actingAs($user)->patch(route('user.update', $user->id), $new_details);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '新テスト',
            'kana' => 'シンテスト',
            'email' => 'newtest@gmail.com',
            'postal_code' => 1000000,
            'address' => '新テスト',
            'phone_number' => 10000000000,
            'birthday' => 20000101,
            'occupation' => '新テスト'
        ]);
        $response->assertRedirect(route('user.index'));
    }

    // 4.ログイン済みの管理者は会員情報を更新できない
    public function test_adminUser_cannot_update_user()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $old_details = User::factory()->create();
        $new_details = [
            'name' => '新テスト',
            'kana' => 'シンテスト',
            'email' => 'newtest@gmail.com',
            'postal_code' => '1000000',
            'address' => '新テスト',
            'phone_number' => '10000000000',
            'birthday' => '10000000',
            'occupation' => '新テスト'
        ];

        $response = $this->actingAs($admin, 'admin')->patch(route('user.update', $old_details->id), $new_details);
        $this->assertDatabaseMissing('users', $new_details);
        $response->assertRedirect(route('admin.home'));
    }


}
