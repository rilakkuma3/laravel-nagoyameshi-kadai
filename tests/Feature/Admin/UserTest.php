<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_cannot_access_admin_users_index()
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_users_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_users_index()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_guest_user_cannot_access_admin_user_show()
    {
        $response = $this->get('/admin/users/1');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_user_show()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/users/1');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_user_show()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/users/1');
        $response->assertStatus(200);
    }
}
