<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('email_templates')->insert([
            [
                'type' => 'ts_reset_email_confirmations',
                'subject' => 'Email Verification',
                'content' => '<p>Hello [USER_LOGIN],</p>
<p> </p>
<p>Welcome to Transbunnies.com!</p>
<p>Please <a href="[VERIFIED_EMAIL_LINK]" target="_blank">click here</a> to verify your email address and start placing your adverts.</p>
<p>This link is valid for the next 24 hours only.</p>
<p> </p>
<p>Thank you.</p>
>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'type' => 'ts_new_password_notification',
                'subject' => 'Email Verification',
                'content' => '<p>Hello [USER_LOGIN],</p>
<p>As requested, your password has been changed on Transbunnies.com.</p>
<p>If you did not change it, please <a href="[NOTIFY_URL]">click here to notify our team.</a></p>
',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'type' => 'new_order',
                'subject' => 'New customer order',
                'content' => '<p>You’ve received the following order from [CUSTOMER_NAME]:</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],



            [
                'type' => 'flash_email_notification',
                'subject' => 'Welcome to Ts',
                'content' => '<p>Hello [USER_LOGIN],</p>
<p>Welcome to Transbunnies, please click <a href="[VERIFY_EMAIL_LINK]" target="_blank">here</a> to verify your email.</p>
<p>Then take full advantage with your TS classfields, TS Competitions, and much more.</p>
<p> </p>
<p>Regards,</p>
<p>Transbunnies Admin Team</p>
',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'type' => 'subscription_expired',
                'subject' => 'Subcription Expired',
                'content' => '<p>Your subscription has expired. Please <a href="[SUBSCRIPTION_URL]">click here</a> to renew your subscription.</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'type' => '24_hours_before_ad_expiry_notification',
                'subject' => 'Oh no! Just 24hr before your Ad expires',
                'content' => '<p><p>Hello, [USER_LOGIN]!</p>
<p>Your [ADVERT_NAME] Ad will expire in 24hrs, <a href="[RENEW_URL]">renew it now</a>, or purchase another <a href="[LOGIN_URL]">type of Ad</a>.</p>

<p> </p>
<p>Regards,</p>
<p>Team Transbunnies</p>
<p>THE PLACE TO BE IN!</p>
</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],



            [
                'type' => '4_weeks_of_profile_inactivity_notification',
                'subject' => '4 weeks of profile inactivity',
                'content' => '<p>Hello, <?=$user_login?>!</p>
<p> </p>
<p>We have not heard from you for a while, <a href="<?php echo $login_link;?>">click here</a> to update your profile and purchase an Ad. If you have not signed into your account for 12 weeks, your profile will be deleted automatically, and you will have to re-register.</p>
<p>Hope to see you soon.</p>
<p> </p>
<p>Regards,</p>
<p>Team Transbunnies</p>
<p>THE PLACE TO BE IN!</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],




            [
                'type' => 'Great_news!_you_are_step_away_to_place_your_free_featured_ad',
                'subject' => 'Great news! You are step away to place your free featured ad',
                'content' => '<p>Hello, [USER_LOGIN]!</p>
<p>You are close to placing your FREE Advert on Transbunnies! <a href="[UNCORAGING_URL]">Click here</a> to take advantage.</p>
<p> </p>
<p>Regards,</p>
<p>Team Transbunnies</p>
<p>THE PLACE TO BE IN!</p>
',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],



            [
                'type' => 'Verify_your_new_email_address',
                'subject' => 'Verify your new email address',
                'content' => '<p>Hello, [USER_LOGIN],</p>
<p> </p>
<p>As requested, this email address will now be used to log in on Transbunnies.com once it is verified.</p>
<p>Please <a href="[VERIFY_EMAIL_URL]">click here</a> to verify this new email address to continue taking advantage of your ads.</p>
<p> </p>
<p>Thank you.</p>
<p> </p>
<p>Regards,</p>
<p>Team Transbunnies</p>
<p>THE PLACE TO BE IN!</p>
',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'type' => 'account_deleted',
                'subject' => 'Your Profile is Deleted',
                'content' => '<p>Hello, [USER_LOGIN],</p>
<p> </p>
<p>As requested, your profile is now deleted on transbunnies.com.</p>
<p>You still have 7 days from today to log in and keep your profile; otherwise, you will have to register again.</p>
<p>If you did not request this change, please <a href="https://transbunnies.com/?page_id=330">click here</a> to contact support.</p>

<p> </p>
<p>Thank you.</p>
<p> </p>
<p>Regards,</p>
<p>Team Transbunnies</p>
<p>THE PLACE TO BE IN!</p>
',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],






        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->truncate();
    }
};
