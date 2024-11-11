<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('articles/search', [ArticleController::class, 'search']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);
Route::get('articles/{article}/comments', [CommentController::class, 'index']);


Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::get('/articles/{article}/ratings', [RatingController::class, 'getRatings']);

Route::get('tags', [TagController::class, 'index']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('articles', [ArticleController::class, 'store'])->middleware('can:manage articles');
    Route::put('articles/{article}', [ArticleController::class, 'update'])->middleware('can:manage articles');
    Route::delete('articles/{article}', [ArticleController::class, 'destroy'])->middleware('can:manage articles');
    Route::post('articles/{article}/comments', [CommentController::class, 'store']);
    Route::get('articles/{article}/history', [ArticleController::class, 'history']);
    Route::post('articles/{article}/restore/{versionId}', [ArticleController::class, 'restoreVersion']);
    Route::post('/articles/{article}/rate', [RatingController::class, 'rate']);

    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->middleware('can:manage comments');


    Route::post('categories', [CategoryController::class, 'store'])->middleware('can:manage categories');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('can:manage categories');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:manage categories');

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/articles/{article}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/articles/{article}/favorite', [FavoriteController::class, 'destroy']);

    Route::post('tags', [TagController::class, 'store'])->middleware(['can:manage tags']);
    Route::delete('tags/{tag}', [TagController::class, 'destroy'])->middleware(['can:manage tags']);
    Route::post('articles/{article}/tags', [ArticleController::class, 'attachTags'])->middleware(['can:manage tags']);
    Route::delete('articles/{article}/tags', [ArticleController::class, 'detachTags'])->middleware(['can:manage tags']);

    Route::post('articles/{article}/progress', [ReadingProgressController::class, 'updateProgress']);
    Route::get('articles/{article}/progress', [ReadingProgressController::class, 'showProgress']);
    Route::get('reading-history', [ReadingProgressController::class, 'history']);
});
