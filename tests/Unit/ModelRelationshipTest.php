<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Models\Category;
use App\Models\User;
use App\Models\Expense;
use Tests\TestCase;
use DatabaseMigrations;


class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a User has many Categories.
     */
    public function test_user_has_many_categories(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($user->categories->contains($category));
    }

    /**
     * Test that a User has many Expenses.
     */
    public function test_user_has_many_expenses(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($user->expenses->contains($expense));
    }

    /**
     * Test that a Category belongs to a User.
     */
    public function test_category_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $category->user->id);
    }

    /**
     * Test that an Expense belongs to a User.
     */
    public function test_expense_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $expense->user->id);
    }

    /**
     * Test that an Expense belongs to a Category.
     */
    public function test_expense_belongs_to_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $expense = Expense::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($category->id, $expense->category->id);
    }

}