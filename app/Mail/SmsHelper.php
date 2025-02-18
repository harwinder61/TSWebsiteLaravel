<?php
namespace App\Mail;

use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Admin\app\Models\EmailTemplates;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;    
use Modules\Admin\app\Models\EmailLog;  
use Modules\Admin\app\Models\SmsTemplates;
use Modules\Admin\app\Models\SmsLogs;   
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SmsHelper
{
    public static function getSmsTemplateByType($type)
    {
        $template = SmsTemplates::where('type', $type)->first();
        
        if (!$template) {
            Log::error('SMS template not found for type: ' . $type);
            return null; // Return null if the template is not found
        }
        
        if (empty($template->content)) {
            Log::error('SMS template content is empty for type: ' . $type);
            return null; // Return null if the content is empty
        }
    
        return $template; // Return the template if found and content is not empty
    }

    public static function dynamicsendSms($templateType, $dynamicData, $recipientSms, $user = null,$isWhatsapp = false)
{
    // Check if the user object is valid
    if (!is_object($user) || $user === null) {
        Log::error('The user object is invalid or not passed correctly.');
        return 'The user object is invalid or not passed correctly.';
    }


    $smsTemplate = SmsTemplates::where('type', $templateType)->first();
    if (!$smsTemplate) {
        Log::error('SMS template not found for type: ' . $templateType);
        return 'SMS template not found';
    }

    // Replace the placeholders with actual dynamic values
    $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $smsTemplate->content);

    // Check for profile and phone number
    if (!$user->profile || !$user->profile->phone_number) {
        Log::error('User profile or phone number not found for user: ' . $user->email);
        return 'User profile or phone number not found';
    }

    // Replace encrypted password if necessary
    if ($user->user_type == 2) {
        $decryptedPassword = Crypt::decryptString($user->secret);
        $body = str_replace('[USER_PASSWORD]', $decryptedPassword, $body);
    } else {
        $body = str_replace('[USER_PASSWORD]', $dynamicData['[USER_PASSWORD]'], $body);
    }

    // if($isWhatsapp){
    //     //runs whatsapp functions
    // }else{
    //     //run simple sms functions
    // }

    try {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $twilioNumber = env('TWILIO_PHONE_NUMBER');

        $client = new \Twilio\Rest\Client($sid, $token);
        $client->messages->create(
            $recipientSms,
            [
                'from' => $twilioNumber,
                'body' => $body
            ]
        );
        SmsLogs::create([
            'message' => $body,
            'to' => $recipientSms,
            'status' => 1, // Success
            'user_id' => $user->id,  // Now properly saving the user_id
            'from' => $twilioNumber,
        ]);
        return "SMS sent successfully!";
    } catch (\Exception $e) {
        Log::error('Error sending SMS: ' . $e->getMessage());

        // Save failed SMS details with user_id
        SmsLogs::create([
            'message' => $body,
            'to' => $recipientSms,
            'status' => 0, // Failed
            'user_id' => $user->id,  // Now properly saving the user_id
            'from' => env('TWILIO_PHONE_NUMBER'),
        ]);
        return "Failed to send SMS: " . $e->getMessage();
    }
}






}