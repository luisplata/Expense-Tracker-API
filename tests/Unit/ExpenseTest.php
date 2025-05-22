<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class ExpenseTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_it_can_create_an_expense_for_an_authenticated_user()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]); // Ensure category belongs to the user

        $expenseData = [
            'product' => 'New Product',
            'price' => 10.99,
            'category_id' => $category->id, // Ensure category belongs to the user
            'timestamp' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/expenses', $expenseData);

        $response->assertStatus(201)
            ->assertJsonFragment(['product' => $expenseData['product']]);

        $this->assertDatabaseHas('expenses', [
            'product' => $expenseData['product'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_it_cannot_create_an_expense_for_an_unauthenticated_user()
    {
        $category = Category::factory()->create(); // Category can belong to any user for this test

        $expenseData = [
            'product' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 1, 100),
            'category_id' => $category->id,
            'timestamp' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson('/api/expenses', $expenseData);

        $response->assertStatus(401);
        $this->assertDatabaseMissing('expenses', ['product' => $expenseData['product']]);
    }

    public function test_it_can_get_all_expenses_for_an_authenticated_user()
    {
        // Create expenses for the authenticated user
        Expense::factory()->count(3)->create(['user_id' => $this->user->id]);
        // Create expenses for another user
        Expense::factory()->count(2)->create(['user_id' => User::factory()->create()->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJsonCount(3); // Only expenses for the authenticated user
    }

    public function test_it_cannot_get_expenses_for_an_unauthenticated_user()
    {
        Expense::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/expenses');

        $response->assertStatus(401);
    }

    public function test_it_can_get_a_single_expense_for_an_authenticated_user()
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/expenses/' . $expense->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['product' => $expense->product]);
    }

    public function test_it_cannot_get_another_users_expense()
    {
        $anotherUser = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/expenses/' . $expense->id);

        $response->assertStatus(404);
    }

    public function test_it_can_update_an_expense_for_an_authenticated_user()
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
    
        $updatedData = [
            'product' => 'Updated Product',
            'price' => 99.99,
            'category_id' => $category->id,
            'timestamp' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/expenses/' . $expense->id, $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment(['product' => 'Updated Product']);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'product' => 'Updated Product',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_it_cannot_update_another_users_expense()
    {
        $anotherUser = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $anotherUser->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $updatedData = [
            'product' => 'Updated Product',
            'price' => 99.99,
            'category_id' => $category->id,
            'timestamp' => $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson('/api/expenses/' . $expense->id, $updatedData);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
            'product' => 'Updated Product',
        ]);
    }

    public function test_it_can_delete_an_expense_for_an_authenticated_user()
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/expenses/' . $expense->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Expense deleted successfully']);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_it_cannot_delete_another_users_expense()
    {
        $anotherUser = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/expenses/' . $expense->id);

        $response->assertStatus(404);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }
}