<?php

namespace App\Enums;
use BenSampo\Enum\Enum;



final class InqueryFormSubject extends Enum
{
    const LEAVE_FEEDBACK = 'Leave Feedback';
    const ADVERTISEMENT = 'Advertisement';    
    const PAYMENT = 'Payment';
    const TECHNICAL_ISSUE = 'Technical Issue';
    const HOW_TO_USE_THE_WEBSITE = 'How to use the website';
    const ONLINE_SAFETY = 'Online Safety';
    const REPORT_A_PROFILE = 'Report a Profile';
    const REPORT_TRAFFICKING = 'Report Trafficking';
    const OTHER = 'Other';

}
