<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Auth::user()->categories;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $category = Auth::user()->categories()->create([
            'name' => $request->name,
        ]);

        return Response::json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Auth::user()->categories()->find($id);

        if (!$category) {
            return Response::json(['message' => 'Category not found.'], 404);
        }

        return Response::json($category, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Auth::user()->categories()->find($id);

        if (!$category) {
            return Response::json(['message' => 'Category not found.'], 404);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return Response::json($category, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Ensure the category belongs to the authenticated user (implicit with route model binding
        // and a policy or a check in the Category model relationship if set up this way).
        // If not using a policy, you would add:
        if ($category->user_id !== Auth::id()) {
            return Response::json(['message' => 'Unauthorized.'], 403);
        }

        $category->expenses()->delete(); // Delete associated expenses

        $category->delete();
        return Response::json(['message' => 'Category deleted successfully.'], 200);
    }
}