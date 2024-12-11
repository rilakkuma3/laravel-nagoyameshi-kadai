<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_cannot_access_admin_users_index()
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('admin.login')
    );
    }

    public function test_non_admin_user_cannot_access_admin_users_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertStatus(302);
    }

    public function test_admin_user_can_access_admin_users_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin,'admin')->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_guest_user_cannot_access_admin_user_show()
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_cannot_access_admin_user_show()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.index'));
        $response->assertStatus(302);
    }

    public function test_admin_user_can_access_admin_user_show()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);

        $response = $this->actingAs($admin,'admin')->get(route('admin.users.index'));
        $response->assertStatus(200);
    }
}
