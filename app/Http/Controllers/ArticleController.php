<?php

namespace App\Http\Controllers;

use App\Models\ArticleVersion;
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
    public function update(Request $request, Article $article){
        if (!auth()->user()->can('manage articles')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'category_id' => 'exists:categories,id',
        ]);

        ArticleVersion::create([
            'article_id' => $article->id,
            'content' => $article->content,
            'version' => $article->versions()->count() + 1,
        ]);

        $article->update($data);

        return response()->json($article, 200);
    }

    /**
     * Display the version history of an article.
     */
    public function history(Article $article){
        return $article->versions;
    }

    /**
     * Restore a specific version of an article.
     */
    public function restoreVersion(Article $article, $versionId){
        $version = ArticleVersion::where('article_id', $article->id)->findOrFail($versionId);

        $article->update(['content' => $version->content]);

        return response()->json($article, 200);
    }

    /**
     * Search a specific article.
     */
    public function search(Request $request)
    {
        // Extract the 'query' parameter
        $query = $request->input('query');
        $category = $request->input('category');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Validation of required fields
        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        // Start the article query builder
        $articles = Article::query();

        // Search by title or content
        $articles->where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        });

        // Filter by category if provided
        if ($category) {
            $articles->where('category_id', $category);
        }

        // Filter by date range if provided
        if ($dateFrom) {
            $articles->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $articles->whereDate('created_at', '<=', $dateTo);
        }

        // Get the results
        $results = $articles->get();

        // Return the results as a JSON response
        return response()->json($results, 200);
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
