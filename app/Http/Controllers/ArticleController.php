<?php

namespace App\Http\Controllers;

use App\Events\ArticleCreated;
use App\Events\ArticleDeleted;
use App\Events\ArticleUpdated;
use App\Models\ArticleVersion;
use App\Models\ModerationHistory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Notification;
use PHPMailer\PHPMailer\PHPMailer;
use Spatie\Permission\Models\Permission;

class ArticleController extends Controller
{

    /**
     * Display a listing of the public resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $articles = Article::where('status', 'public')
            ->with(['user:id,name', 'category:id,name,parent_id', 'tags:id,name'])
            ->paginate($perPage);

        return response()->json($articles, 200);
    }

    /**
     * Display a listing of all resources with pagination.
     */
    public function showAllArticles(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $articles = Article::with(['user:id,name', 'category:id,name,parent_id', 'tags:id,name'])
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $articles,
            'message' => 'All articles retrieved successfully.',
        ], 200);
    }


    /**
     * Display a listing of the review resource.
     */
    public function listUnderReview(Request $request)
    {
        if (!auth('api')->check() || !auth('api')->user()->can('manage articles')) {

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this page.'
            ], 403);

        }

        $perPage = $request->input('per_page', 10);
        $articles = Article::where('status', 'under_review')
            ->with(['user:id,name', 'category:id,name', 'tags:id,name'])
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $articles,
            'message' => 'Articles under review retrieved successfully.'
        ], 200);
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

        $data['status'] = 'under_review';

        $article = Article::create($data);

        ModerationHistory::create([
            'moderator_id' => auth()->id(),
            'user_id' => $article->user_id,
            'action' => 'Article created and flagged for review',
            'details' => "Article ID {$article->id} created and flagged for review.",
        ]);

        $author = User::find($article->user_id);
        $author->notifications()->create([
            'type' => 'article_status',
            'message' => "Your article '{$article->title}' is under review.",
            'data' => ['article_id' => $article->id],
            'is_read' => false,
        ]);

        $this->sendEmail(
            $author->email,
            'Your Article is Under Review',
            view('emails.article_under_review', ['article' => $article])->render()
        );

        $staffMembers = User::role('admin')->orWhereHas('permissions', function ($query) {
            $query->where('name', 'manage articles');
        })->get();

        foreach ($staffMembers as $staff) {
            $staff->notifications()->create([
                'type' => 'new_article_review',
                'message' => "A new article '{$article->title}' is awaiting review.",
                'data' => ['article_id' => $article->id],
                'is_read' => false,
            ]);
        }

        event(new ArticleCreated($article));

        return response()->json($article, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {

        $permission = Permission::where('name', 'manage articles')->first();
        $user = auth('api')->user();

        if ($article->status === 'under_review' &&
            (!$user->hasPermissionTo($permission))) {
            return response()->json(['message' => 'Unauthorized - Insufficient Permissions'], 403);
        }

        // Charger les relations nécessaires
        $article->load([
            'user:id,name',
            'category:id,name,parent_id',
            'tags:id,name',
        ]);

        return response()->json($article, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'status' => 'nullable|string|in:public,under_review,rejected',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if (array_key_exists('content', $data) && $data['content'] !== $article->content) {
            ArticleVersion::create([
                'article_id' => $article->id,
                'content' => $article->content,
                'version' => $article->versions()->count() + 1,
            ]);
        }

        $article->fill($data);
        $isUpdated = $article->isDirty() ? $article->save() : false;

        if (!$isUpdated) {
            return response()->json([
                'success' => false,
                'message' => 'No changes were made to the article.',
            ], 200);
        }

        if ($request->has('tags')) {
            $article->tags()->sync($data['tags']);
        }

        event(new ArticleUpdated($article));

        return response()->json([
            'success' => true,
            'message' => 'Article updated successfully.',
            'data' => $article,
        ], 200);
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

    public function getUserFavorites(Request $request){
        $user = auth('api')->user();

        if (!$user){
            return response()->json([
                'message' => 'Not Authenticated.'
            ], 401);
        }

        $perPage = $request->input('per_page', 10);
        $favorites = $user->favoriteArticles()->with('tags')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $favorites,
            'message' => 'User favorites retrieved successfully'
        ], 200);
    }

    /**
     * Send email using PHPMailer.
     */
    private function sendEmail($recipient, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT');

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($recipient);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
        } catch (Exception $e) {
            logger("PHPMailer Error: " . $mail->ErrorInfo);
        }
    }

}
