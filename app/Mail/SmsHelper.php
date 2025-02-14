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

    //    public static function sendDynamicEmail($templateType, $dynamicData, $recipientEmail)
    // {
    //     // Fetch the email template by type
        
    //     $emailTemplate = EmailTemplates::where('type', $templateType)->first();
        
    //     if (!$emailTemplate) {
    //         // If the template doesn't exist, return an error or handle it
    //         return 'Email template not found';
    //     }

    //     if($emailTemplate->status==0){

    //         return "Email is disabled";

    //     }
    
    //     // Replace dynamic data in the subject and body
    //     $subject = str_replace(array_keys($dynamicData), array_values($dynamicData), $emailTemplate->subject);
    //     $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $emailTemplate->content);
        
    //     $user = User::where('email', $recipientEmail)->first();
        
    
    //     // Send the email to the recipient
    //     //Mail::to($recipientEmail)->send(new DynamicEmail($subject, $body));
    //     if($emailTemplate->status==1){
            
    //         Mail::to($recipientEmail)->send(new DynamicEmail($subject, $body));
    //     }

    //     EmailLog::create([
    //         'subject' => $subject,
    //         'message' => $body,
    //         'to' => $recipientEmail
    //     ]);
       
    
    //     return "Email sent successfully!";
    // }



    public static function dynamicsendSms($templateType, $dynamicData, $recipientSms) {
        // Fetch the SMS template by type
        $smsTemplate = SmsTemplates::where('type', $templateType)->first();
    
        if (!$smsTemplate) {
            return 'SMS template not found';
        }
    
        if ($smsTemplate->status == 0) {
            return "SMS is disabled";
        }
    
        // Replace dynamic data in the subject and body
        $subject = str_replace(array_keys($dynamicData), array_values($dynamicData), $smsTemplate->subject);
        $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $smsTemplate->content);
        
        // Send the SMS using Twilio or your preferred method
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $twilioNumber = env('TWILIO_PHONE_NUMBER');
    
            $client = new \Twilio\Rest\Client($sid, $token);
            $client->messages->create(
                $recipientSms, // This should be the phone number
                [
                    'from' => $twilioNumber,
                    'body' => $body
                ]
            );
    
            SmsLogs::create([
                'subject' => $subject,
                'message' => $body,
                'to' => $recipientSms
            ]);
    
            return "SMS sent successfully!";
        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage());
            return "Failed to send SMS: " . $e->getMessage();
        }
    }



  
}


 