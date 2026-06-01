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
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

class SmsHelper
{

    public static function createSmsLog($message,$to,$status,$user_id,$from,$message_sent){
       try{

        SmsLogs::create([
            'message' => $message,
            'to' => $to,
            'status' => $status, // Success
            'user_id' => $user_id,  // Now properly saving the user_id
            'from' => $from,
            'message_sent' => $message_sent
        ]);
       }catch(Exception $e){
        Log::error('Error creating SMS log: ' . $e->getMessage());
        return "Error creating SMS log";
       }
    }
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

  if (!env('TWILIO_PHONE_NUMBER')) {
        Log::info('TWILIO_PHONE_NUMBER missing. SMS sending skipped.');
        return false;
    }
    // Check if the user object is valid
    if (!is_object($user) || $user === null) {
        Log::error('The user object is invalid or not passed correctly.');
        // return 'The user object is invalid or not passed correctly.';
        return false;
    }

    $smsTemplate = SmsTemplates::where('type', $templateType)->first();
    if (!$smsTemplate) {
        Log::error('SMS template not found for type: ' . $templateType);
        // return 'SMS template not found';
        return false;
    }

    // Replace the placeholders with actual dynamic values
    $body = str_replace(array_keys($dynamicData), array_values($dynamicData), $smsTemplate->content);

    // Check for profile and phone number
    if (!$user->profile || !$user->profile->phone_number) {
        Log::error('User profile or phone number not found for user: ' . $user->email);
        // return 'User profile or phone number not found';
        return false;
    }

    $twilioNumber = env('TWILIO_PHONE_NUMBER');
    //phone number validation and formating
    $user_phone_num=$user->profile->phone_number;

    if($user->profile->phone_number){
        $num=$user->profile->phone_number;

        //num is properly formatted with +country code
        //first case
        if(Str::startsWith($num, '+')){
            $formattedNum=new PhoneNumber($num);
            $countryCode=$formattedNum->getCountry();
            
            $data=[
                'phone'=>$num
            ];

            $validator=Validator::make($data, [
                'phone' => 'required|phone:' . $countryCode
            ]);
            if($validator->fails()){
                // echo "\n Invalid phone number for case 1 ";
                self::createSmsLog(
                    "Unable to send SMS because phone number is invalid",$formattedNum,0, $user->id,$twilioNumber,false
                );
                // return 'Invalid phone number';
                return false;
            }

            //if everthing is fine for case 1
            $user_phone_num=$formattedNum;
        }else{
            

            //num is not properly formatted with +country code
            //second case
            $appendedNum="+".$num;
            $formattedNum=new PhoneNumber($appendedNum);

            //if no country code is found then append uk country code
            //third case
            if($formattedNum->getCountry() == null){
                

                //append uk country code
                $newNumber="+44".$num;
                $formattedNum=new PhoneNumber($newNumber);

                $validator=Validator::make(['phone'=>$formattedNum],[
                    'phone' => 'required|phone:'.$formattedNum->getCountry()
                ]);

                if($validator->fails()){
                    // echo "\n Invalid phone number for appended UK country code";

                    self::createSmsLog(
                        "Unable to send SMS because phone number is invalid",$formattedNum,0, $user->id,$twilioNumber,false
                    );
                    // return 'Invalid phone number';
                    return false;
                }

                //if everthing is fine for case 3
                $user_phone_num=$formattedNum;

            }


            $validator=Validator::make(['phone'=>$formattedNum],[
                'phone' => 'required|phone:' . $formattedNum->getCountry()
            ]);

            //after properly formatting if num is invalid
            if($validator->fails()){
                // echo "\n Invalid phone number for case 2";

                self::createSmsLog(
                    "Unable to send SMS because phone number is invalid",$formattedNum,0, $user->id,$twilioNumber,false
                );

                // return 'Invalid phone number';
                return false;
            }

            //if everthing is fine for case 2
            $user_phone_num=$formattedNum;

        }
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

        //$receiptSms is old phone number we passed to twillio
        //now use formatted $user_phone_num instead

        //     $recipientSms,

        if(env('TWILIO_ENABLED')==true){
        Log::info("Twillio enabled sending sms");

        $client = new \Twilio\Rest\Client($sid, $token);
        $client->messages->create(
        
            $user_phone_num,
            [
                'from' => $twilioNumber,
                'body' => $body
            ]
        );
    }else{
        Log::info("Twillio disabled sending sms");
    }

        
        SmsLogs::create([
            'message' => $body,
            'to' => $user_phone_num,
            'status' => 1, // Success
            'user_id' => $user->id,  // Now properly saving the user_id
            'from' => $twilioNumber,
            'message_sent' => true
        ]);
        // return "SMS sent successfully!";
        return true;
    } catch (\Exception $e) {
        Log::error('Error sending SMS: ' . $e->getMessage());

        // Save failed SMS details with user_id
        SmsLogs::create([
            'message' => $body,
            'to' => $user_phone_num,
            'status' => 0, // Failed
            'user_id' => $user->id,  // Now properly saving the user_id
            'from' => env('TWILIO_PHONE_NUMBER'),
            'message_sent' => false
        ]);
        // return "Failed to send SMS: " . $e->getMessage();
        return false;
    }
}






}