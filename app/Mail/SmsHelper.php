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
    

    
}


 