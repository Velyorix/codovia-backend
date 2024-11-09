<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::with('children')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('manage categories')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $category->load('children', 'articles');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        if (!auth()->user()->can('manage categories')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($request->only(['name', 'description', 'parent_id']));

        return response()->json($category, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if (!auth()->user()->can('manage categories')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
