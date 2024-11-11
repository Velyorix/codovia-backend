<?php

namespace App\Http\Controllers;

use App\Events\ArticleCreated;
use App\Events\ArticleDeleted;
use App\Events\ArticleUpdated;
use App\Models\ArticleVersion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Notification;

class ArticleController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        return Article::paginate($perPage);
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

        event(new ArticleCreated($article));

        return response()->json($article, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return $article->load('tags');
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

        event(new ArticleUpdated($article));

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
        $query = $request->input('query');
        $category = $request->input('category');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $perPage = $request->input('per_page', 10);

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        $articles = Article::search($query);

        if ($category) {
            $articles = $articles->where('category_id', $category);
        }

        if ($dateFrom) {
            $articles = $articles->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $articles = $articles->where('created_at', '<=', $dateTo);
        }

        $results = $articles->paginate($perPage);

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

        event(new ArticleDeleted($article));

        return response()->json(null, 204);
    }

    public function attachTags(Request $request, Article $article){
        $request->validate(['tags' => 'array']);
        $tags = Tag::whereIn('id', $request->tags)->pluck('id');
        $article->tags()->sync($tags);
        return response()->json(['message' => 'Tags successfully associated with the article.'], 200);
    }

    public function detachTags(Request $request, Article $article){
        $request->validate(['tags' => 'array']);
        $tags = Tag::whereIn('id', $request->tags)->pluck('id');
        $article->tags()->detach($tags);

        return response()->json(['message' => 'Tags successfully detached from the article.'], 200);
    }
}
