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
        DB::table('email_templates')->where('type', 'new_order')->update([
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
                                    <td style="border: 1px solid #ddd;">[PRICE]</td>
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
                                    <td style="text-align: right;"><strong>[PRICE]</strong></td>
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
                                    <td style="border: 1px solid #ddd;">[END_DATE]</td>
                                    <td style="border: 1px solid #ddd;">[PRICE] every 2 weeks</td>
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
                          </table>'
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
