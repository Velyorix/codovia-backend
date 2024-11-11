<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Article;

class FavoriteController extends Controller
{
    public function store(Request $request, Article $article){
        $user = auth()->user();

        if ($user->favorites()->where('article_id', $article->id)->exists()) {
            return response()->json(['message' => 'Article already in favorites'], 409);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'article_id' => $article->id,
        ]);
        return response()->json(['message' => 'Article added to favorites successfully'], 201);
    }

    public function destroy(Request $request, Article $article){
        $user = auth()->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('article_id', $article->id)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Article not found in favorites'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Article removed from favorites'], 200);
    }

    public function index(){
        $user = auth()->user();
        $favorites = $user->favoriteArticles()->paginate(10);

        return response()->json($favorites);
    }
}
