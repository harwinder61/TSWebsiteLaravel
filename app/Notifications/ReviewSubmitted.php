<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Escort\app\Models\Reviews;
use Illuminate\Support\Facades\Log;

class ReviewSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reviews;

    public function __construct($reviews)
    {
        $this->reviews = $reviews;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        Log::info("Sending email to admin: " . $notifiable->email); // Add this log
        return (new MailMessage)
            ->subject('New Review Submitted')
            ->view('emailTemplates.review-notification', ['review' => $this->reviews]);
    }
}