<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncController extends Controller
{
    public function getExpenses(Request $request)
    {
        $userId = Auth::id();
        $since = $request->query('since');
        $limit = $request->query('limit');
        $offset = $request->query('offset');

        $query = Expense::withTrashed()->where('user_id', $userId);

        if ($since) {
            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                  ->orWhere('created_at', '>=', $since);
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        $expenses = $query->with('category')
                          ->select('id', 'local_id', 'product', 'price', 'timestamp', 'updated_at', 'deleted_at')
                          ->get();

        $serverTimestamp = Carbon::now()->toIso8601String();

        return response()->json([
            'expenses' => $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'local_id' => $expense->local_id,
                    'productName' => $expense->product,
                    'price' => (float) $expense->price, // Ensure price is a number
                    'category' => $expense->category ? $expense->category->name : null,
                    'timestamp' => Carbon::parse($expense->timestamp)->toIso8601String(),
                    'updated_at' => Carbon::parse($expense->updated_at)->toIso8601String(),
                    'deleted_at' => $expense->deleted_at ? Carbon::parse($expense->deleted_at)->toIso8601String() : null,
                ];
            }),
            'server_timestamp' => $serverTimestamp,
        ]);
    }

    public function syncExpenses(Request $request)
    {
        $userId = Auth::id();
        $createdMap = [];
        $updatedResults = [];
        $deletedCount = 0;
        $errors = [];

        $data = $request->validate([
            'created' => 'array',
            'created.*.local_id' => 'required|string',
            'created.*.productName' => 'required|string|max:255',
            'created.*.price' => 'required|numeric',
            'created.*.category' => 'required|string|max:255',
            'created.*.timestamp' => 'required|date',
            'updated' => 'array',
            'updated.*.id' => 'required|string', // Assuming server ID is a string (UUID or similar)
            'updated.*.productName' => 'required|string|max:255',
            'updated.*.price' => 'required|numeric',
            'updated.*.category' => 'required|string|max:255',
            'updated.*.timestamp' => 'required|date', // Local updated_at
            'deleted_ids' => 'array',
            'deleted_ids.*' => 'required|string', // Assuming server ID is a string
        ]);

        DB::beginTransaction();
        try {
            // Handle Created Expenses
            if (isset($data['created'])) {
                foreach ($data['created'] as $createdExpenseData) {
                    $category = Category::firstOrCreate(
                        ['name' => strtolower($createdExpenseData['category']), 'user_id' => $userId],
                        ['name' => $createdExpenseData['category'], 'user_id' => $userId]
                    );

                    $expense = Expense::create([
                        'user_id' => $userId,
                        'category_id' => $category->id,
                        'local_id' => $createdExpenseData['local_id'],
                        'product' => $createdExpenseData['productName'],
                        'price' => $createdExpenseData['price'],
                        'timestamp' => $createdExpenseData['timestamp'],
                    ]);
                    $createdMap[] = ['local_id' => $createdExpenseData['local_id'], 'server_id' => $expense->id];
                }
            }

            // Handle Updated Expenses
            if (isset($data['updated'])) {
                foreach ($data['updated'] as $updatedExpenseData) {
                    $expenseId = $updatedExpenseData['id'];
                    $expense = Expense::where('id', $expenseId)->where('user_id', $userId)->first();

                    if ($expense) {
                        // Check for duplicates (same user, product name case-insensitive, timestamp) excluding the current expense
                        $duplicate = Expense::where('user_id', $userId)
                            ->whereRaw('LOWER(product) = ?', [strtolower($updatedExpenseData['productName'])])
                            ->where('timestamp', $updatedExpenseData['timestamp'])
                            ->where('id', '!=', $expenseId)
                            ->first();

                        if ($duplicate) {
                            $errors[] = ['type' => 'duplicate_update', 'id' => $expenseId, 'message' => 'Duplicate expense found', 'conflicting_id' => $duplicate->id];
                            $updatedResults[] = ['id' => $expenseId, 'status' => 'skipped', 'message' => 'Duplicate found'];
                        } else {
                            // Basic conflict resolution: Server wins
                            if (Carbon::parse($updatedExpenseData['timestamp'])->greaterThanOrEqualTo($expense->updated_at)) {
                                $category = Category::firstOrCreate(['name' => strtolower($updatedExpenseData['category']), 'user_id' => $userId], ['name' => $updatedExpenseData['category'], 'user_id' => $userId]);
                                $expense->update([
                                    'category_id' => $category->id,
                                    'product' => $updatedExpenseData['productName'],
                                    'price' => $updatedExpenseData['price'],
                                    'timestamp' => $updatedExpenseData['timestamp'], // This might overwrite server timestamp, refine later for merge logic
                                ]);
                                $updatedResults[] = ['id' => $expenseId, 'status' => 'success'];
                            } else {
                                $updatedResults[] = ['id' => $expenseId, 'status' => 'conflict', 'message' => 'Server version is more recent'];
                            }
                        }
                    } else {
                        $updatedResults[] = ['id' => $expenseId, 'status' => 'not_found', 'message' => 'Expense not found'];
                    }
                }
            }

            // Handle Deleted Expenses
            if (isset($data['deleted_ids'])) {
                $deletedCount = Expense::whereIn('id', $data['deleted_ids'])->where('user_id', $userId)->delete(); // Soft delete
            }

            DB::commit();

            return response()->json([
                'created_map' => $createdMap,
                'updated_results' => $updatedResults,
                'deleted_count' => $deletedCount,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = ['type' => 'server_error', 'message' => $e->getMessage()];
            return response()->json([
                'created_map' => [],
                'updated_results' => [],
                'deleted_count' => 0,
                'errors' => $errors,
            ], 500);
        }
    }

    public function replaceAllClientData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expenses' => 'required|array',
            'expenses.*.id' => 'nullable|string', // Server ID
            'expenses.*.local_id' => 'nullable|string', // Client ID
            'expenses.*.product' => 'required|string|max:255',
            'expenses.*.price' => 'required|numeric',
            'expenses.*.category' => 'required|string|max:255',
            'expenses.*.timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = Auth::id();
        $createdCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->input('expenses') as $expenseData) {
                $category = Category::firstOrCreate(
                    ['name' => strtolower($expenseData['category']), 'user_id' => $userId],
                    ['name' => $expenseData['category'], 'user_id' => $userId]
                );

                if (isset($expenseData['id'])) {
                    // Update existing expense if it has a server ID and belongs to the user
                    $expense = Expense::where('id', $expenseData['id'])->where('user_id', $userId)->first();
                    if ($expense) {
                        $expense->update(['product' => $expenseData['product'], 'price' => $expenseData['price'], 'category_id' => $category->id, 'timestamp' => $expenseData['timestamp']]);
                        $updatedCount++;
                    }
                } elseif (isset($expenseData['local_id'])) {
                    // Create new expense if it has a local ID and no server ID
                     Expense::create(['user_id' => $userId, 'local_id' => $expenseData['local_id'], 'product' => $expenseData['product'], 'price' => $expenseData['price'], 'category_id' => $category->id, 'timestamp' => $expenseData['timestamp']]);
                    $createdCount++;
                }
            }

            DB::commit();

            return response()->json(['created_count' => $createdCount, 'updated_count' => $updatedCount]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to sync expenses', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllServerData(Request $request)
    {
        $userId = Auth::id();

        // 2. Retrieve all expenses for the user, including trashed, and eager load the category.
        $expenses = Expense::withTrashed()
                           ->where('user_id', $userId)
                           ->with('category')
                           ->get();

        // 3. Retrieve all categories for the user.
        $categories = Category::where('user_id', $userId)->get();

        // 4. Get all unique product names from the user's expenses.
        $productNames = Expense::where('user_id', $userId)
                                ->distinct()
                                ->pluck('product');

        // 5. Get the current server timestamp.
        $serverTimestamp = Carbon::now()->toIso8601String();

        // 6. Format the expenses data
        $formattedExpenses = $expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'local_id' => $expense->local_id,
                'productName' => $expense->product,
                'price' => (float) $expense->price,
                'category' => $expense->category ? $expense->category->name : null,
                'timestamp' => Carbon::parse($expense->timestamp)->toIso8601String(),
                'updated_at' => Carbon::parse($expense->updated_at)->toIso8601String(),
                'deleted_at' => $expense->deleted_at ? Carbon::parse($expense->deleted_at)->toIso8601String() : null,
            ];
        });

        return response()->json([
            'expenses' => $formattedExpenses,
            'categories' => $categories, // Assuming category model has necessary attributes (id, name)
            'productNames' => $productNames,
            'server_timestamp' => $serverTimestamp,
        ]);
    }
}
