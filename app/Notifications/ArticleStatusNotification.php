<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class ArticleStatusNotification extends Notification{

    use Queueable;

    private $article;
    private $status;
    private $reason;

    public function __construct($article, $status, $reason = null)
    {
        $this->article = $article;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'article_id' => $this->article->id,
            'title' => $this->article->title,
            'status' => $this->status,
            'reason' => $this->reason,
        ];
    }

}
