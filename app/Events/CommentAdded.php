<?php
namespace App\Events;

use App\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentAdded {
    use Dispatchable, SerializesModels;

    public $comment;

    public function __construct(Comment $comment) {
        $this->comment = $comment;
    }
}
