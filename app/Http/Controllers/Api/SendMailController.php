<?php

namespace App\Http\Controllers\Api;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class SendMailController extends BaseController
{
    public function SendMail()
    {        
        //    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://message.heroleads.co.th/SendDailyEmail/public/index.php/api/sendEmail_test");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);

        $data = array(
            'email_from' => 'admin.th@heroleads.com',
            'email[0]' => 'nut@heroleads.com',
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
    }
}
