<?php

namespace App\Listeners;

use App\Events\ArticleCreated;
use App\Models\Notification;
use App\Models\Article;
use App\Models\User;

class SendArticleCreatedNotification {
    public function handle(ArticleCreated $event) {
        $article = $event->article;

        foreach (User::all() as $user){
            Notification::create([
                'user_id' => $user->id,
                'type' => 'new_article',
                'message' => "A new article titled '{$article->title}' has been published.",
                'data' => ['article_id' => $article->id],
                'is_read' => false
            ]);
        }
    }
}
