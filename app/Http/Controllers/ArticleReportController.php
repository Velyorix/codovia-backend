<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ModerationHistory;
use Illuminate\Http\Request;

class ArticleReportController extends Controller
{
    public function report(Request $request, Article $article){
        $article->update(['status' => 'under_review']);

        ModerationHistory::create([
            'moderator_id' => auth()->id(),
            'user_id' => $article->user_id,
            'action' => 'Article flagged for review',
            'details' => "Article ID {$article->id} has been flagged for review."
        ]);

        return response()->json(['message' => 'Article flagged for review.'], 200);

    }

    public function review(Request $request){
        $articles = Article::where('status', 'under_review')->get();
        return response()->json($articles, 200);
    }

    public function resolve(Article $article){
        $article->update(['status' => 'public']);

        ModerationHistory::create([
            'moderator_id' => auth()->id(),
            'user_id' => $article->user_id,
            'action' => 'Article reviewed and published',
            'details' => "Article ID {$article->id} reviewed and published.",
        ]);
        return response()->json(['message' => 'Article reviewed and published'], 200);
    }

}
