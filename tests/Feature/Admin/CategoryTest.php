<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_guest_cannot_access_category_index()
    {
        $response = $this->get('/admin/categories');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_category_index()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/categories');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_category()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->post('/admin/categories', [
            'name' => 'テストカテゴリ',
        ]);

        // デバッグのための出力 
        dd($response->getContent());

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => 'テストカテゴリ']);
    }

    public function test_admin_can_update_category()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        $category = Category::factory()->create();

        $response = $this->patch("/admin/categories/{$category->id}", [
            'name' => '更新カテゴリ',
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['name' => '更新カテゴリ']);
    }

    public function test_admin_can_delete_category()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');
        $category = Category::factory()->create();

        $response = $this->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
