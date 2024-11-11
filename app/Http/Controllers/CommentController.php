<?php

namespace App\Http\Controllers;

use App\Events\CommentAdded;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Article;

class CommentController extends Controller
{
    public function index(Request $request, Article $article){
        $perPage = $request->input('per_page', 10);
        return $article->comments()->with('user')->paginate($perPage);
    }

    public function store(Request $request, Article $article){
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $article->comments()->create([
            'content' => $request->content,
            'user_id' => auth()->id(),
        ]);

        event(new CommentAdded($comment));

        return response()->json($comment, 201);
    }

    public function destroy(Comment $comment){
        if (auth()->id() !== $comment->user_id && !auth()->user()->can('manage comments')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(null, 204);
    }
}
