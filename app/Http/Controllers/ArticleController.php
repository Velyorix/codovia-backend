<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Article::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('manage articles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $article = Article::create($data);

        return response()->json($article, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return $article;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        if (!auth()->user()->can('manage articles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'category_id' => 'exists:categories,id',
        ]);

        $article->update($request->only(['title', 'content', 'category_id']));

        return response()->json($article, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if (!auth()->user()->can('manage articles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $article->delete();

        return response()->json(null, 204);
    }
}
