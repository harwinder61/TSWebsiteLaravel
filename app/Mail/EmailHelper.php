<?php
namespace App\Mail;

use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\Mail;

class EmailHelper
{
    // This function sends an email with dynamic content
    public static function sendDynamicEmail($dynamicData, $templateSubject, $templateBody, $recipientEmail)
    {
        // Replace placeholders like {{name}} with actual dynamic data
        $subject = str_replace(array_keys($dynamicData), array_values($dynamicData), $templateSubject);
        $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $templateBody);

        // Send the email to the recipient
        Mail::to($recipientEmail)->send(new DynamicEmail($subject, $body));

        return "Email sent!";
    }
}
 