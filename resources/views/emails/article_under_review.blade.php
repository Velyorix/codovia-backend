<!DOCTYPE html>
<html>
<head>
    <title>Article Under Review</title>
</head>
<body>
<h2>Your Article is Under Review</h2>
<p>
    Dear {{ $article->user->name }},
</p>
<p>
    Your article titled "<strong>{{ $article->title }}</strong>" has been submitted for review.
    We will notify you once a decision has been made.
</p>
<p>Thank you for your contribution!</p>
<p>Best Regards,</p>
<p>The {{ env('APP_NAME') }} Team</p>
</body>
</html>
