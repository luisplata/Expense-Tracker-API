<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Auth::user()->expenses()->with('category')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $expense = Auth::user()->expenses()->create($validator->validated());
        return response()->json($expense->load('category'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $expense = Auth::user()->expenses()->with('category')->find($id);
        if (!$expense) {
            return response()->json(['message' => 'Expense not found or unauthorized'], 404);
        }
        return $expense;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validator = Validator::make($request->all(), [
            'product' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'timestamp' => 'required|date',
        ]);
        $expense->update($validator->validated());
        return response()->json($expense->load('category'), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json(['message' => 'Expense not found'], 404);
        }
        $expense->delete(); // Soft delete the expense
        return response()->json(['message' => 'Expense deleted successfully']);
    }
    
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.product' => 'required|string',
            '*.price' => 'required|numeric',
            '*.category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        foreach ($validator->validated() as $expenseData) {
            $category = Category::firstOrCreate(
                ['name' => strtolower($expenseData['category'])],  // Case-insensitive lookup
                ['name' => $expenseData['category'], 'user_id' => Auth::id()]  // Create with original case and user_id
            );

            Auth::user()->expenses()->create([
                'product' => $expenseData['product'],
                'price' => $expenseData['price'],
                'category_id' => $category->id,
            ]);
        }

        return response()->json(['message' => 'Expenses processed successfully'], 201);
    }
}
