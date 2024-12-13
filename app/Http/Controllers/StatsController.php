<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use App\Models\Rating;
use Illuminate\Http\Request;

class StatsController extends Controller{

    public function adminStats(){
        $totalUsers = User::count();
        $totalArticles = Article::count();
        $totalComments = Comment::count();
        $totalRatings = Rating::count();
        $averageRating = Rating::avg('rating');
        $totalCategory = Category::count();
        $totalTags = Tag::count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_articles' => $totalArticles,
            'total_comments' => $totalComments,
            'total_ratings' => $totalRatings,
            'total_category' => $totalCategory,
            'total_tags' => $totalTags,
            'average_rating' => round($averageRating, 2),
        ], 200);
    }

    public function editorStats(){
        $user = auth('api')->user();

        $articlesCount = Article::where('user_id', $user->id)->count();
        $commentsCount = Comment::where('user_id', $user->id)->count();
        $totalStars = Rating::whereIn('article_id', Article::where('user_id', $user->id)->pluck('id'))->sum('rating');

        return response()->json([
            'articles_count' => $articlesCount,
            'comments_count' => $commentsCount,
            'total_stars' => $totalStars,
        ], 200);
    }
}
