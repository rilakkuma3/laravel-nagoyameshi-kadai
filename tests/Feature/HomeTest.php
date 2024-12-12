<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;


class HomeTest extends TestCase
{
    public function test_guest_can_access_home_index()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(500);
    }
    public function test_user_can_access_admin_home_index()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('home'));
        $response->assertStatus(500);
    }
    public function test_admin_cannot_access_home_index()
    {
        $admin = Admin::create([
            'email'            => 'admin@example.com',
            'password'         => Hash::make('nagoyameshi'),
        ]);
        $response =  $this->actingAs($admin, 'admin')->get('home');
        $response->assertRedirect('admin.home');
    }
}
