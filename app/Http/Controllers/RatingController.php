<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Rating;

class RatingController extends Controller{

    public function rate(Request $request, Article $article){
        $request->validate([
            'rating' => 'required|integer|between:1,5'
        ]);

        $user = auth()->user();

        $rating = Rating::updateOrCreate(
            ['user_id' => $user->id, 'article_id' => $article->id],
            ['rating' => $request->rating]
        );

        return response()->json([
            'message' => 'Rating submitted successfully.',
            'rating' => $rating
        ], 200);
    }

    public function getRatings(Article $article){
        $average = $article->averageRating();
        $ratings = $article->ratings()->with('user:id,name')->get();

        return response()->json([
            'average_rating' => $average,
            'ratings' => $ratings,
        ], 200);
    }

}
