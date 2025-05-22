<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
    public function update(Request $request, string $id)
    {
        $expense = Auth::user()->expenses()->find($id);
        if (!$expense) {
            return response()->json(['message' => 'Expense not found or unauthorized'], 404);
        }

        $validator = Validator::make($request->all(), [
            'product' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $expense->update($validator->validated());
        return response()->json($expense->load('category'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Auth::user()->expenses()->find($id);
        if (!$expense) {
            return response()->json(['message' => 'Expense not found or unauthorized'], 404);
        }
        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully']);
    }
}