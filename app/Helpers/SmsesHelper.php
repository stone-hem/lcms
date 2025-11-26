<?php


namespace App\Helpers;
use  Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
// use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;



class SmsesHelper
{
    public static function twoauth($phones,$message)
    {
        $endpoint = env('BULK_SMS_ENDPOINT');
        $usrname = env('BULK_SMS_CLIENTID');
        $auth_token = env('BULK_SMS_APIKEY');
        $sender_id = env('BULK_SMS_SENDERID');
        $validity = env('BULK_SMS_VALIDITY');
        $headers = array();
        $headers=[
          'accept'=>'application/json'
        ];
        $client = new GuzzleClient([
          'headers'=>$headers
        ]);
        $response = $client->request('POST', $endpoint, [
            'multipart' => [
                [
                    'name'     => 'apikey',
                    'contents' => $auth_token
                ],
                [
                    'name'     => 'partnerID',
                    'contents' => $usrname
                ],
                [
                    'name'     => 'shortcode',
                    'contents' => $sender_id
                ],
                [
                    'name'     => 'mobile',
                    'contents' => $phones
                ],
                [
                    'name'     => 'message',
                    'contents' => $message
                ]
          ]]);

    }

    public static function sms($phone,$subject)
    {
        $endpoint = env('SMS_ENDPOINT');
        $usrname = env('SMS_CLIENTID');
        $auth_token = env("SMS_APIKEY");
        $headers = array();
        $headers=[
          'apikey'=>$auth_token,
          'Content-Type'=>'application/x-www-form-urlencoded',
          'accept'=>'application/json'
        ];
        $client = new GuzzleClient([
          'headers'=>$headers
        ]);
        $response = $client->request('POST', $endpoint, [
            'multipart' => [
                [
                    'name'     => 'username',
                    'contents' => $usrname
                ],
                [
                    'name'     => 'to',
                    'contents' => $phone
                ],
                [
                    'name'     => 'message',
                    'contents' => $subject
                ]
                ]]);
    }

    public static function phonesms($phone,$subject){
        $apiKey = env('HTTPS_SMS_KEY');

        $options = array(
        'http' => array(
            'method'  => 'POST',
            'content' => json_encode( [
                'content' => $subject,
                'from'    => env('HTTPS_SMS_FROM'),
                'to'      => $phone
            ]),
            'header'=>  "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n" .
                        "x-api-key: $apiKey\r\n"
            )
        );

        $context  = stream_context_create( $options );
        $result = file_get_contents( "https://api.httpsms.com/v1/messages/send", false, $context );
    }
}
