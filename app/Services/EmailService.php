<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;


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
}
?>