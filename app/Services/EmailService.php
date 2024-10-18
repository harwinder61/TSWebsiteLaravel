<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;


class EmailService{

    public function __construct(){
        $this->to=null;
        $this->subject=null;
        $this->message=null;
    }

    public function to($to){
        $this->to=$to;
    }
    public function subject($subject){
        $this->subject=$subject;
    }
    public function message($message){
        $this->message=$message;
    }
    public function send(){

    }
}
?>