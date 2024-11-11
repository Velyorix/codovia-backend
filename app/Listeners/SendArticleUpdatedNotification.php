<?php

namespace App\Listeners;

use App\Events\ArticleUpdated;
use App\Models\Notification;
use App\Models\User;

class SendArticleUpdatedNotification {

    public function handle(ArticleUpdated $event) {
        $article = $event->article;

        foreach (User::all() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'updated_article',
                'message' => "The article titled '{$article->title}' has been updated.",
                'data' => ['article_id' => $article->id],
                'is_read' => false,
            ]);
        }
    }
}
