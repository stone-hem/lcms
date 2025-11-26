<?php


namespace App\Helpers;
use  Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
// use Illuminate\Http\Request;


class EmailsHelper
{

    public static function twoauth($email,$randomId)
    {
         View::make('twoauthemail');

         $mail = Mail::send('twoauthemail', ['code'=>$randomId], function($message) use($email){
             $message->to($email)->subject('Login Verification Code');
          });
    }

    public static function newaccount($email,$appurl,$appowner,$appname,$lawyerfirstname)
    {
         View::make('newaccount');

         $mail = Mail::send('newaccount', ['email'=>$email,'url'=>$appurl,'appowner'=>$appowner,'password'=>'Lawyer#24','appname'=>$appname,'lawyerfirstname'=>$lawyerfirstname], function($message) use($email){
             $message->to($email)->subject('Account Created Successfully');
          });
    }

    public static function siteemail($first_name,$link,$date,$start_time,$end_time,$email)
    {
        $to = $email;
        View::make('votelinkmail');

        $mail = Mail::send('votelinkmail', ['first_name'=>$first_name,'link'=>$link,'date'=>$date,'start_time'=>$start_time,'end_time'=>$end_time,'email'=>$email], function($message) use($to){
            $message->to($to)->subject(env('APP_NAME').' Voting Link');
         });
    }

    public static function newcase($email,$appurl,$appowner)
    {
         View::make('newcase');

         $mail = Mail::send('newcase', ['email'=>$email,'casetitle'=>$appurl,'casenumber'=>$appowner], function($message) use($email){
             $message->to($email)->subject('New Case File');
          });
    }


}
