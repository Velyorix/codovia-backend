<?php
namespace App\Listeners;

use App\Events\CommentAdded;
use App\Models\Notification;
use App\Models\User;

class SendCommentAddedNotification {
    public function handle(CommentAdded $event) {
        $comment = $event->comment;
        $article = $comment->article;

        $users = $article->comments->pluck('user')->unique();
        foreach ($users as $user){
            Notification::create([
                'user_id' => $user->id,
                'type' => 'new_comment',
                'message' => "A new comment has been added to the article '{$article->title}'.",
                'data' => ['article_id' => $article->id, 'comment_id' => $comment->id],
                'is_read' => false
            ]);
        }
    }
}
