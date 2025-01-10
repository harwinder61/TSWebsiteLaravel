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
            // Add email template 1
            DB::table('email_templates')->where('type', 'ts_email_verification')->update([
                'subject' => 'Email Verification',
                'type' => 'ts_email_verification',
                'title' => 'Email Verification',
                'content' => '<p>Hello [USER_LOGIN],</p>
                <p>Hello [USER_LOGIN],</p>
                <p>Welcome to Transbunnies.com!</p>
                <p>Please <a href="[VERIFIED_EMAIL_LINK]" target="_blank" style="background-color: #c00; color: #fff; font-family: Heebo; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to verify your email address and start placing your adverts.</p>
                <p>This link is valid for the next 24 hours only.</p>
                <p>Thank you.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 2
            DB::table('email_templates')->where('type', 'ts_new_password_notification')->update([
                'subject' => 'New Password Notification',
                'type' => 'ts_new_password_notification',
                'title' => 'New Password Notification',
                'content' => '<p>Hello [USER_LOGIN],</p>
                <p>As requested, your password has been changed on Transbunnies.com.</p>
                <p>If you did not change it, please <a href="[NOTIFY_URL]">click here to notify our team.</a></p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 3
            DB::table('email_templates')->where('type', 'ts_new_order_notification')->update([
                'subject' => 'New Order Notification',
                'type' => 'ts_new_order_notification',
                'title' => 'New Order Notification',
                'content' => '<table cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
                                <tr>
                                  <td style="padding: 20px 0;">
                                    <h1 style="color: #ff0000; margin: 0; font-size: 24px;">New customer order!</h1>
                                    <p style="color: #333; margin: 10px 0;">You\'ve received the following order from :</p>
                                    
                                    <div style="margin: 20px 0;">
                                      <strong style="color: #333;">#[ORDER_ID] ([START_DATE])</strong>
                                    </div>
                    
                                    <table cellpadding="10" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                      <tr style="background-color: #f8f8f8;">
                                        <th style="border: 1px solid #ddd; text-align: left;">Product</th>
                                        <th style="border: 1px solid #ddd; text-align: left;">Quantity</th>
                                        <th style="border: 1px solid #ddd; text-align: left;">Price</th>
                                      </tr>
                                      <tr>
                                        <td style="border: 1px solid #ddd;">[PLAN_TITLE]</td>
                                        <td style="border: 1px solid #ddd;">1</td>
                                        <td style="border: 1px solid #ddd;">£[PRICE]</td>
                                      </tr>
                                    </table>
                    
                                    <table cellpadding="5" cellspacing="0" width="100%" style="margin-top: 20px;">
                                      <tr>
                                        <td style="text-align: left;">Subtotal:</td>
                                        <td style="text-align: right;">[PRICE]</td>
                                      </tr>
                                      <tr>
                                        <td style="text-align: left;">Payment method:</td>
                                        <td style="text-align: right;">Fake Pay</td>
                                      </tr>
                                      <tr>
                                        <td style="text-align: left;"><strong>Total:</strong></td>
                                        <td style="text-align: right;"><strong>£[PRICE]</strong></td>
                                      </tr>
                                    </table>
                    
                                    <h2 style="color: #ff0000; margin: 20px 0;">Subscription Information:</h2>
                                    <table cellpadding="10" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                      <tr style="background-color: #f8f8f8;">
                                        <th style="border: 1px solid #ddd; text-align: left;">Subscription</th>
                                        <th style="border: 1px solid #ddd; text-align: left;">Start Date</th>
                                        <th style="border: 1px solid #ddd; text-align: left;">End Date</th>
                                        <th style="border: 1px solid #ddd; text-align: left;">Price</th>
                                      </tr>
                                      <tr>
                                        <td style="border: 1px solid #ddd;">#[SUBSCRIPTION_ID]</td>
                                        <td style="border: 1px solid #ddd;">[START_DATE]</td>
                                        <td style="border: 1px solid #ddd;">When Cancelled</td>
                                        <td style="border: 1px solid #ddd;">£[PRICE] every 2 weeks</td>
                                      </tr>
                                    </table>
                    
                                    <h2 style="color: #ff0000; margin: 20px 0;">Billing address</h2>
                                    <p style="color: #666; margin: 5px 0;">N/A</p>
                                    <p style="color: #666; margin: 5px 0;">[USER_EMAIL]</p>
                    
                                    <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
                                      <p style="color: #333; margin: 0;">Congratulations on the sale.</p>
                                      <p style="color: #333; margin: 10px 0;">
                                        Process your orders on the go. 
                                        <a href="#" style="color: #ff0000; text-decoration: none;">Get the app</a>.
                                      </p>
                                    </div>
                                  </td>
                                </tr>
                              </table>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 4
            DB::table('email_templates')->where('type', 'ts_welcome_to_ts')->update([
                'subject' => 'Welcome to Ts',
                'type' => 'ts_welcome_to_ts',
                'title' => 'Welcome to Transbunnies',
                'content' => '<p>Hello [USER_LOGIN],</p>
                <p>Welcome to Transbunnies, please click <a href="[VERIFY_EMAIL_LINK]" target="_blank">here</a> to verify your email.</p>
                <p>Then take full advantage with your TS classfields, TS Competitions, and much more.</p>
                <p> </p>
                <p>Regards,</p>
                <p>Transbunnies Admin Team</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 5
            DB::table('email_templates')->where('type', 'ts_subscription_expired')->update([
                'subject' => 'Subscription Expired',
                'type' => 'ts_subscription_expired',
                'title' => 'Subscription Expired',
                'content' => '<p>Hello, [User Login]!</p>
                <p>Your Plan will expire in 24hrs, <a href="[Renew URL]">renew it now</a>, or purchase another <a href="[Login Link]">type of Ad</a>.</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 6
            DB::table('email_templates')->where('type', 'ts_oh_no_just_24hr_before_your_ad_expires')->update([
                'subject' => 'Oh no! Just 24hr before your Ad expires',
                'type' => 'ts_oh_no_just_24hr_before_your_ad_expires',
                'title' => 'Oh no! Just 24hr before your Ad expires',
                'content' => '<p>Hello, [USER_LOGIN]!</p>
                <p>Your [ADVERT_NAME] Ad will expire in 24hrs, <a href="[RENEW_URL]">renew it now</a>, or purchase another <a href="[LOGIN_URL]">type of Ad</a>.</p>
                <p> </p>
                <p>Regards,</p>
                <p>Team Transbunnies</p>
                <p>THE PLACE TO BE IN!</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 7
            DB::table('email_templates')->where('type', 'ts_4_weeks_of_profile_inactivity')->update([
                'subject' => '4 weeks of profile inactivity',
                'type' => 'ts_4_weeks_of_profile_inactivity',
                'title' => '4 weeks of profile inactivity',
                'content' => '<p>Hello, [USER_LOGIN]!</p>
                <p> </p>
                <p>We have not heard from you for a while, <a href="[login_link]">click here</a> to update your profile and purchase an Ad. If you have not signed into your account for 12 weeks, your profile will be deleted automatically, and you will have to re-register.</p>
                <p>Hope to see you soon.</p>
                <p> </p>
                <p>Regards,</p>
                <p>Team Transbunnies</p>
                <p>THE PLACE TO BE IN!</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 8
            DB::table('email_templates')->where('type', 'ts_great_news_you_are_step_away_to_place_your_free_featured_ad')->update([
                'subject' => 'Great news! You are step away to place your free featured ad',
                'type' => 'ts_great_news_you_are_step_away_to_place_your_free_featured_ad',
                'title' => 'Great news! You are step away to place your free featured ad',
                'content' => '<p>Hello, [USER_LOGIN]!</p>
                <p>You are close to placing your FREE Advert on Transbunnies! <a href="<?= $P105 ?>">Click here</a> to take advantage.</p>
                <p> </p>
                <p>Regards,</p>
                <p>Team Transbunnies</p>
                <p>THE PLACE TO BE IN!</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 9
            DB::table('email_templates')->where('type', 'ts_verify_your_new_email_address')->update([
                'subject' => 'Verify your new email address',
                'type' => 'ts_verify_your_new_email_address',
                'title' => 'Verify your new email address',
                'content' => '<p>Hello, [USER_LOGIN],</p>
    
                <p> </p>
                <p>As requested, this email address will now be used to log in on Transbunnies.com once it is verified.</p>
                <p>Please <a href="[VERIFY_EMAIL_URL]">click here</a> to verify this new email address to continue taking advantage of your ads.</p>
                <p> </p>
                <p>Thank you.</p>
                <p> </p>
                <p>Regards,</p>
                <p>Team Transbunnies</p>
                <p>THE PLACE TO BE IN!</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Add email template 10
            DB::table('email_templates')->where('type', 'ts_your_profile_is_deleted')->update([
                'subject' => 'Your Profile is Deleted',
                'type' => 'ts_your_profile_is_deleted',
                'title' => 'Your Profile is Deleted',
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
                <p>THE PLACE TO BE IN!</p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            DB::table('email_templates')->whereIn('type', [
                'ts_email_verification',
                'ts_new_password_notification',
                'ts_new_order_notification',
                'ts_welcome_to_ts',
                'ts_subscription_expired',
                'ts_oh_no_just_24hr_before_your_ad_expires',
                'ts_4_weeks_of_profile_inactivity',
                'ts_great_news_you_are_step_away_to_place_your_free_featured_ad',
                'ts_verify_your_new_email_address',
                'ts_your_profile_is_deleted'
            ])->delete();
        }
};
