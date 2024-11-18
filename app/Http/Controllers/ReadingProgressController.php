<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;

class ReadingProgressController extends Controller
{
    public function updateProgress(Request $request, Article $article){
        $data = $request->validate(['last_position' => 'required|integer']);

        $progress = ReadingProgress::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'article_id' => $article->id,
            ],
            $data
        );

        return response()->json($progress, 200);
    }


    public function showProgress(Article $article){
        $progress = ReadingProgress::where('user_id', auth()->id())
            ->where('article_id', $article->id)
            ->firstOrFail();

        return response()->json($progress, 200);
    }

    public function history(){
        $history = ReadingProgress::where('user_id', auth()->id())
            ->with('article')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return response()->json($history, 200);
    }

    public function resetProgress(Article $article){
        $progress = ReadingProgress::where('user_id', auth()->id())
        ->where('article_id', $article->id)
        ->first();

        if (!$progress){
            return response()->json(['message' => 'Progress not found'], 404);
        }

        $progress->delete();

        return response()->json(['message' => 'Progress reset successfully'], 200);
    }
}
