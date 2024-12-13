<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ModerationHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;


class ArticleStatusController extends Controller {

    public function accept(Request $request, Article $article) {
        if ($article->status !== 'under_review') {
            return response()->json(['message' => 'Article is not under review.'], 400);
        }

        $article->update(['status' => 'public']);

        ModerationHistory::create([
            'moderator_id' => auth('api')->id(),
            'user_id' => $article->user_id,
            'action' => 'Accepted Article',
            'details' => "Article ID {$article->id} was accepted and published."
        ]);

        $author = User::find($article->user_id);
        Notification::create([
            'user_id' => $author->id,
            'type' => 'article_status',
            'message' => "Your article '{$article->title}' has been accepted and published.",
            'data' => ['article_id' => $article->id],
            'is_read' => false,
        ]);

        $this->sendEmail(
            $author->email,
            'Your Article has been Accepted',
            view('emails.article_accepted', ['article' => $article])->render()
        );

        return response()->json(['message' => 'Article accepted and published successfully.'], 200);
    }

    public function reject(Request $request, Article $article) {
        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        if ($article->status !== 'under_review') {
            return response()->json(['message' => 'Article is not under review.'], 400);
        }

        $article->update(['status' => 'rejected']);

        ModerationHistory::create([
            'moderator_id' => auth('api')->id(),
            'user_id' => $article->user_id,
            'action' => 'Rejected Article',
            'details' => "Article ID {$article->id} was rejected. Reason: {$data['reason']}"
        ]);

        $author = User::find($article->user_id);
        Notification::create([
            'user_id' => $author->id,
            'type' => 'article_status',
            'message' => "Your article '{$article->title}' has been rejected.",
            'data' => ['article_id' => $article->id, 'reason' => $data['reason']],
            'is_read' => false,
        ]);

        $this->sendEmail(
            $author->email,
            'Your Article has been Rejected',
            view('emails.article_rejected', ['article' => $article, 'reason' => $data['reason']])->render()
        );

        return response()->json(['message' => 'Article rejected successfully.'], 200);
    }

    private function sendEmail($recipient, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($recipient);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
        } catch (\Exception $e) {
            logger("PHPMailer Error: " . $mail->ErrorInfo);
        }
    }
}
