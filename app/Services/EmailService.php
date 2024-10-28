<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Services\Mailer;


class EmailService{

    public function __construct(){
        $this->to=null;
        $this->subject=null;
        $this->body=null;
    }

    public function to($to){
        $this->to=$to;    
    }
    public function subject($subject){
        $this->subject=$subject;
    }
    public function body($body){
        $this->body=$body;
    }
    
    public function send(){

        Mail::html($this->body, function ($mail) {
            $mail->to($this->to)
                 ->subject($this->subject);
        });

    }

    public function setBodyByTemplate(string $template,array $data){

        $htmlContent = View::make('emailTemplates/'.$template, $data)->render();
        $body = $htmlContent;
        $this->body($body);
        return $this;

    }

    public function setBodyRecoveryEmail(string $template,array $data){
        $htmlContent = View::make('emailTemplates/'.$template, $data)->render();
        $body = $htmlContent;
        $this->body($body);
        return $this;
    }


    public function setBodyPurchasingEmail(string $template,array $data){
        $htmlContent = View::make('emailTemplates/'.$template, $data)->render();
        $body = $htmlContent;
        $this->body($body);
        return $this;
    }


    private  $header = "<div style='text-align: center;'>
            <img src='https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png' 
            alt='Company Logo' style='max-width: 100px; margin-bottom: 20px;' />
        </div>
        ";


     private $footer = "<div style='text-align: center'>
        </div>";  
}
?>