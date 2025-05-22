<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_can_get_all_categories_for_authenticated_user()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        Category::factory()->count(3)->create(['user_id' => $user->id]);
        Category::factory()->count(2)->create();  // Categories for other users

        $response = $this->getJson('/api/categories', ['Authorization' => "Bearer $token"]);

        $response
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_cannot_get_categories_for_unauthenticated_user()
    {
        $response = $this->getJson('/api/categories');

        $response->assertUnauthorized();
    }

    public function test_can_delete_category_for_authenticated_user()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $response = $this->deleteJson("/api/categories/{$category->id}", [], ['Authorization' => "Bearer $token"]);

        $response
            ->assertOk()
            ->assertJson(['message' => 'Category deleted successfully.']);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_associated_expenses()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $category = Category::factory()->create(['user_id' => $user->id]);
        Expense::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]); // Associate an expense with the category and user

        $response = $this->deleteJson("/api/categories/{$category->id}", [], ['Authorization' => "Bearer $token"]);

        $response
            ->assertStatus(400)
            ->assertJson(['message' => 'Category has associated expenses and cannot be deleted.']);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_for_unauthenticated_user()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertUnauthorized();
    }

    public function test_cannot_delete_another_users_category()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $anotherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $anotherUser->id]);
        $response = $this->deleteJson("/api/categories/{$category->id}", [], ['Authorization' => "Bearer $token"]);

        $response->assertStatus(404);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
