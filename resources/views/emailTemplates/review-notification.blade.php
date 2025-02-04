
<!DOCTYPE html>
<html>
<head>
    <title>New Review Submitted</title>
</head>
<body>
    <h1>New Review Notification</h1>
    <p>A new review has been submitted by a fan.</p>
    <!-- <p><strong>Comment:</strong> {{ $review->comment }}</p> -->
    <p><strong>Submitted by User ID:</strong> {{ $review->user_id }}</p>
    <p>send review notification to </p>
    <p><strong>Escort ID:</strong> {{ $review->escort_id }}</p>
    <!-- <p><a href="{{ url('/reviews/' . $review->id) }}">View Review</a></p> -->
    <p>Thank you!</p>
</body>
</html>