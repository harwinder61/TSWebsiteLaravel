<?php
namespace App\Mail;

use App\Mail\DynamicEmail;
use App\Models\BaseProfile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Admin\app\Models\EmailTemplates;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;    
use Modules\Admin\app\Models\EmailLog;  
use Modules\Admin\app\Models\whatsappTemplates;
use Modules\Admin\app\Models\WhatsappLogs;

class WhatsappHelper
{
    public static function getWhatsappTemplateByType($type)
    {
        $template = whatsappTemplates::where('type', $type)->first();
        
        if (!$template) {
            Log::error('Whatsapp template not found for type: ' . $type);
            return null; // Return null if the template is not found
        }
        
        if (empty($template->content)) {
            Log::error('Whatsapp template content is empty for type: ' . $type);
            return null; // Return null if the content is empty
        }
    
        return $template; // Return the template if found and content is not empty
    }


    // public static function dynamicsendWhatsapp($templateType, $dynamicData, $recipientSms)
    // {
    //     // Fetch the SMS template by type
    //     $smsTemplate = whatsappTemplates::where('type', $templateType)->first();
    
    //     if (!$smsTemplate) {
    //         return 'SMS template not found';
    //     }
    
    //     if ($smsTemplate->status == 0) {
    //         return "SMS is disabled";
    //     }
    
    //     // Replace dynamic data in the subject and body
    //     $subject = str_replace(array_keys($dynamicData), array_values($dynamicData), $smsTemplate->subject);
    //     $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $smsTemplate->content);
        
    //     // Assuming you want to send a WhatsApp message, you should use Twilio or another service
    //     // Send the WhatsApp message using Twilio or your preferred method
    //     try {
    //         $sid = env('TWILIO_SID');
    //         $token = env('TWILIO_TOKEN');
    //         $twilioNumber = env('TWILIO_PHONE_NUMBER');
    
    //         $client = new \Twilio\Rest\Client($sid, $token);
    //         $client->messages->create(
    //             $recipientSms, // This should be the phone number
    //             [
    //                 'from' => $twilioNumber,
    //                 'body' => $body
    //             ]
    //         );
    
    //         WhatsappLogs::create([
    //             'subject' => $subject,
    //             'message' => $body,
    //             'to' => $recipientSms
    //         ]);
    
    //         return "WhatsApp message sent successfully!";
    //     } catch (\Exception $e) {
    //         Log::error('Error sending WhatsApp message: ' . $e->getMessage());
    //         return "Failed to send WhatsApp message: " . $e->getMessage();
    //     }
    // }
  
}


 