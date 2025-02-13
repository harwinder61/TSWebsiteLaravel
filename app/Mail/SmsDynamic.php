<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mime;

class SmsDynamic extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            //view: 'emailTemplates.dynamic_email',
            view: 'layouts.app',
            with: [
                //  'subject' => $this->subject,
                'title' => $this->subject,
                'body' => $this->body,
                'mime' => 'text/html', 
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function build()
    {
        return $this->subject($this->subject)
            //                    ->view('emailTemplates.dynamic_email') // This uses the Blade view we made
            ->view('layouts.app') // This uses the Blade view we made  
            ->with([
                //'subject' => $this->subject,
                'title' => $this->subject,
                'body' => $this->body,
                'mime' => 'text/html',
                'plan_code' => session('plan_code'),
                'start_date' => session('start_date'),
                'end_date' => session('end_date'),
                'price' => session('price'),
                'total' => session('total'),
            ]);
            
            
    }
}
