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

class EmailHelper
{
    // This function sends an email with dynamic content
    // public static function sendDynamicEmail($templateType, $dynamicData, $recipientEmail)
    // {
    //     // Fetch the email template by type
    //     $emailTemplate = EmailTemplates::where('type', $templateType)->first();
        
    //     if (!$emailTemplate) {
    //         // If the template doesn't exist, return an error or handle it
    //         return 'Email template not found';
    //     }

    //     // Replace dynamic data in the subject and body
    //     $subject = str_replace(array_keys($dynamicData), array_values($dynamicData), $emailTemplate->subject);
    //     $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $emailTemplate->content);

    //     // Send the email to the recipient
    //     Mail::to($recipientEmail)->send(new DynamicEmail($subject, $body));

    //     return "Email sent successfully!";
    // }

    public static function sendDynamicEmail($templateType, $dynamicData, $recipientEmail)
    {
        // Fetch the email template by type
        
        $emailTemplate = EmailTemplates::where('type', $templateType)->first();
        
        if (!$emailTemplate) {
            // If the template doesn't exist, return an error or handle it
            return 'Email template not found';
        }

        if($emailTemplate->status==0){
            return "Email is disabled";
        } 
        // Replace dynamic data in the subject and body
        $subject = str_replace(array_keys($dynamicData), array_values($dynamicData), $emailTemplate->subject);
        $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $emailTemplate->content);
        
        $user = User::where('email', $recipientEmail)->first();
        
        if($emailTemplate->status==1){
            
            Mail::to($recipientEmail)->send(new DynamicEmail($subject, $body));
        }

        EmailLog::create([
            'subject' => $subject,
            'message' => $body,
            'to' => $recipientEmail
        ]);
       
    
        return "Email sent successfully!";
    }

    public static function updateLastActiveAt($user_id)
    {
        $user = User::find($user_id);
        $user->last_active_at = Carbon::now();
        $user->save();
    }




    
}


 