<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleReportController;
use App\Http\Controllers\ArticleStatusController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ModerationHistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\SanctionController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::middleware(['auth:api', 'signed'])->get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully.'], 200);
})->name('verification.verify');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/reset', [AuthController::class, 'sendPasswordResetLink']);
Route::post('/password/update', [AuthController::class, 'resetPassword']);
Route::put('/user/update', [AuthController::class, 'updateProfile']);
Route::get('/user/details', [AuthController::class, 'getUserDetails']);

Route::get('articles/search', [ArticleController::class, 'search']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);
Route::get('articles/{article}/comments', [CommentController::class, 'index']);


Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('/articles/{article}/ratings', [RatingController::class, 'getRatings']);
Route::get('tags', [TagController::class, 'index']);
Route::get('/users/{username}', [UserController::class, 'getPublicProfile']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendEmailVerification']);
    Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
    Route::post('/token/refresh', [AuthController::class, 'refreshToken']);
    Route::post('articles', [ArticleController::class, 'store'])->middleware('can:manage articles');
    Route::put('articles/{article}', [ArticleController::class, 'update'])->middleware('can:manage articles');
    Route::delete('articles/{article}', [ArticleController::class, 'destroy'])->middleware('can:manage articles');
    Route::post('articles/{article}/comments', [CommentController::class, 'store']);
    Route::get('articles/{article}/history', [ArticleController::class, 'history']);
    Route::post('articles/{article}/restore/{versionId}', [ArticleController::class, 'restoreVersion'])->middleware('can:manage articles');
    Route::post('/articles/{article}/rate', [RatingController::class, 'rate']);
    Route::post('articles/{article}/progress', [ReadingProgressController::class, 'updateProgress']);
    Route::delete('/articles/{article}/progress', [ReadingProgressController::class, 'resetProgress']);
    Route::post('/articles/{article}/mark-complete', [ReadingProgressController::class, 'markAsComplete']);
    Route::get('articles/{article}/progress', [ReadingProgressController::class, 'showProgress']);
    Route::get('reading-history', [ReadingProgressController::class, 'history']);
    Route::get('/articles/{article}/is-favorite', [FavoriteController::class, 'isFavorite']);
    Route::get('articles/{article}/tags', [ArticleController::class, 'listTags']);

    Route::post('articles/{article}/accept', [ArticleStatusController::class, 'accept'])->middleware('can:manage articles');
    Route::post('articles/{article}/reject', [ArticleStatusController::class, 'reject'])->middleware('can:manage articles');

    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->middleware('can:manage comments');
    Route::post('/comments/{comment}/report', [CommentController::class, 'report']);
    Route::get('/comments/{comment}/reports', [CommentController::class, 'getReports']);
    Route::get('/admin/reports', [CommentController::class, 'reviewReports'])->middleware('can:manage reports');
    Route::post('/admin/reports/{report}/resolve', [CommentController::class, 'resolveReport'])->middleware('can:manage reports');

    Route::post('categories', [CategoryController::class, 'store'])->middleware('can:manage categories');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('can:manage categories');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:manage categories');

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/articles/{article}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/articles/{article}/favorite', [FavoriteController::class, 'destroy']);

    Route::post('tags', [TagController::class, 'store'])->middleware(['can:manage tags']);
    Route::delete('tags/{tag}', [TagController::class, 'destroy'])->middleware(['can:manage tags']);
    Route::put('tags/{tag}', [TagController::class, 'update'])->middleware(['can:manage tags']);
    Route::post('articles/{article}/tags', [ArticleController::class, 'attachTags'])->middleware(['can:manage tags']);
    Route::delete('articles/{article}/tags', [ArticleController::class, 'detachTags'])->middleware(['can:manage tags']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    Route::get('/profile', [UserController::class, 'getPrivateProfile']);
    Route::put('/profile/update', [UserController::class, 'updatePrivateProfile']);

    Route::get('/admin/sanctions', [SanctionController::class, 'index'])->middleware('can:manage sanctions');
    Route::post('/admin/users/{user}/sanctions', [SanctionController::class, 'store'])->middleware('can:manage sanctions');
    Route::put('/admin/sanctions/{sanction}', [SanctionController::class, 'update'])->middleware('can:manage sanctions');
    Route::delete('/admin/sanctions/{sanction}', [SanctionController::class, 'destroy'])->middleware('can:manage sanctions');
    Route::get('/admin/users/{user}/sanction-status', [SanctionController::class, 'checkSanctionStatus']);

    Route::post('/articles/{article}/flag', [ArticleReportController::class, 'report'])->middleware('can:manage articles');
    Route::get('/admin/articles/reported', [ArticleReportController::class, 'review'])->middleware('can:manage articles');
    Route::post('/admin/articles/{article}/resolve', [ArticleReportController::class, 'resolve'])->middleware('can:manage articles');
    Route::get('admin/articles/under_review', [ArticleController::class, 'listUnderReview'])->middleware(['can:manage articles']);

    Route::get('/admin/stats', [StatsController::class, 'adminStats'])->middleware('can:manage articles');
    Route::get('/editor/stats', [StatsController::class, 'editorStats'])->middleware('auth:api');

    Route::get('/admin/moderation-history', [ModerationHistoryController::class, 'index'])->middleware('can:manage moderation history');
    Route::get('/admin/moderation-history/export', [ModerationHistoryController::class, 'export'])->middleware('can:manage moderation history');
});
