<?php

namespace App\Http\Controllers;

use App\Events\CommentAdded;
use App\Models\Report;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function report(Request $request, $commentId){
        try {
            $comment = Comment::findOrFail($commentId);

            $request->validate([
                'reason' => 'required|string|max:255',
            ]);

            $report = $comment->reports()->create([
                'user_id' => auth()->id(),
                'reason' => $request->reason,
                'resolved' => false,
            ]);

            return response()->json(['message' => 'Comment reported successfully.'], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }
    }

    public function reviewReports(Request $request){
        $request->validate([
            'status' => 'in:resolved,unresolved',
            'user_id' => 'integer|exists:users,id',
            'comment_id' => 'integer|exists:comments,id',
            'sort' => 'in:asc,desc',
            'per_page' => 'integer|min:1|max:100',
        ]);

        $reports = Report::with(['comment:id,name', 'user:id,name']);

        if ($request->has('status')) {
            $resolved = $request->status === 'resolved';
            $reports->where('resolved', $resolved);
        }

        if ($request->has('user_id')) {
            $reports->where('user_id', $request->user_id);
        }

        if ($request->has('comment_id')) {
            $reports->where('comment_id', $request->comment_id);
        }

        $sortOrder = $request->input('sort', 'desc');
        $reports->orderBy('created_at', $sortOrder);

        $perPage = $request->input('per_page', 10);

        return $reports->orderBy('created_at', $sortOrder)->paginate($perPage);
    }

    public function resolveReport($reportId){
        try {
            $report = Report::findOrFail($reportId);

            if ($report->resolved) {
                return response()->json(['message' => 'This report is already resolved.'], 400);
            }

            $report->update(['resolved' => true]);

            return response()->json(['message' => 'Report resolved successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found.'], 404);
        }
    }

    public function vote(Request $request, Comment $comment){
        $request->validate([
            'vote_type' => 'required|in:upvote,downvote'
        ]);

        $user = auth('api')->user();

        return response()->json(['message' => 'Vote registered successfully.'], 200);
    }
}
