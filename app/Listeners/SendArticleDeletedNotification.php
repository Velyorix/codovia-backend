<?php

namespace App\Listeners;

use App\Events\ArticleDeleted;
use App\Models\Notification;
use App\Models\User;

class SendArticleDeletedNotification {
    public function handle(ArticleDeleted $event) {
        $article = $event->article;

        foreach (User::all() as $user) {
            Notification::created([
                'user_id' => $user->id,
                'type' => 'deleted_article',
                'message' => "The article titled '{$article->title}' has been deleted.",
                'data' => ['article_id' => $article->id],
                'is_read' => false
            ]);
        }
    }
}
