<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioSmsService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        Log::info('Twilio SID initialized', ['TWILIO_SID' => env('TWILIO_SID')]); // Log the SID
    }

    public function sendSms($to, $message)
    {
    try {
        $response = $this->client->messages->create($to, [
            'from' => env('TWILIO_FROM'),
            'body' => $message,
        ]);
        Log::info('SMS sent successfully', ['response' => $response]);
        return $response;
    } catch (\Exception $e) {
        Log::error('Failed to send SMS: ' . $e->getMessage());
        throw $e; // rethrow to handle it in the calling function
    }
}
}