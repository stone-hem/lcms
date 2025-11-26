<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\Country;
use App\Models\SMSProvider;
use Exception;
use GuzzleHttp\Client as GuzzleClient;

use App\Models\SMTPSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

use Illuminate\Support\Facades\Config;


class Helper
{

    public static function set_env($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . env($value),
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));
    }

    public static function countries()
    {
        $countries = Country::select('country_code', 'name', 'calling_code', 'flag')->get();
        foreach ($countries as $country) {
            $flag = $country->flag;
            $flagPath = url('') . '/flags/' . $flag;
            $country->flag = $flagPath;
        }
        return $countries;
    }



    //kenya is supported for now
    //create admin dashboard to enable addition of other countries
    public static function supported_countries()
    {
        return [
            ["code" => '404', 'phone_length_minus_code' => 9],
        ];
    }

    /**
     * Recipients are the phone numbers [0791507732,0795567893]
     * Message - the message being sent 
     */
    public static function send_sms($recipients, $message)
    {
        if (!is_array($recipients)) {
            throw new Exception('Invalid recipients, recipients must be in array format');
        }
        if (!is_string($message) || $message == '') {
            throw new Exception('Invalid message, message must be a string of one or more characters');
        }
        $recipients_cleaned = [];
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient != '' && trim($recipient) != '') {
                array_push($recipients_cleaned, trim($recipient));
            }
        }

        $sms_provider = SMSProvider::where('active', 1)->first();
        if (!$sms_provider) {
            throw new Exception('No active sms provider');
        }
        if (!$sms_provider->identifier || !$sms_provider->auth_token) {
            throw new Exception('Provided sms provider does not have correct credentials');
        }

        if ($sms_provider->provider == 'Africa\'s Talking') {
            return Helper::send_sms_africas_talking($sms_provider, $recipients_cleaned, $message);
        } else if ($sms_provider->provider == 'Sematime') {
            return Helper::send_sms_sematime($sms_provider, $recipients_cleaned, $message);
        } else if ($sms_provider->provider == 'Twilio') {
            return Helper::send_sms_twilio($sms_provider, $recipients_cleaned, $message);
        }
    }



    static function send_sms_africas_talking($sms_provider, $recipients, $message)
    {

        $endpoint = "https://api.africastalking.com/version1/messaging";
        $headers = array();
        $auth_token = $sms_provider->auth_token;
        $headers = [
            'apikey' => $auth_token,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'accept' => 'application/json'
        ];
        $client = new GuzzleClient([
            'headers' => $headers
        ]);
        $username = $sms_provider->identifier;
        $response = $client->request('POST', $endpoint, [
            'multipart' => [
                [
                    'name'     => 'username',
                    'contents' => $username
                ],
                [
                    'name'     => 'to',
                    'contents' => implode(',', $recipients)
                ],
                [
                    'name'     => 'message',
                    'contents' => $message
                ]
            ]
        ]);
    }

    static function send_sms_sematime($sms_provider, $recipients, $message) {}

    static function send_sms_twilio($sms_provider, $recipients, $message) {}




    /**
     * $config_type 1) General 2) sales
     */
    public static function send_email_with_custom_config(
        $subject,
        $recipients,
        $cc,
        $bcc,
        $reply_to,
        $template = null,
        $mail_data = null
    ) {
        // $smtp_setting = SMTPSettings::where('type_id', $config_type)->first(); //2 is for sales
        // if (!$smtp_setting) {
        //     throw new Exception("No SMTP setting found", 1);
        // }
        //$backup = Config::get('mail.mailers.smtp');

        // Config::set('mail.mailers.smtp.host', $smtp_setting->smtp_host);
        // Config::set('mail.mailers.smtp.port', $smtp_setting->smtp_port);
        // Config::set('mail.mailers.smtp.username', $smtp_setting->smtp_username);
        // Config::set('mail.mailers.smtp.password', $smtp_setting->smtp_password);
        // Config::set('mail.mailers.smtp.encryption', $smtp_setting->smtp_encryption);
        // Config::set('mail.mailers.smtp.transport', 'smtp');

        //$mail_from = $smtp_setting->smtp_username;

        View::make($template);

        if (!$template) {
            // Mail::html($mail_content, function ($mail) use ($subject, $recipients, $cc, $bcc, $reply_to, $mail_from) {
            //     $mail->from($mail_from)->to($recipients)->subject($subject);
            //     if (count($cc) > 0) {
            //         $mail->cc($cc);
            //     }
            //     if (count($bcc) > 0) {
            //         $mail->bcc($bcc);
            //     }
            //     if ($reply_to) {
            //         $mail->replyTo($reply_to, '');
            //     }
            // });
        } else {
            //echo 'here is amm ---------- ';
            //dd($mail_data);
            Mail::send($template, $mail_data, function ($message) use ($mail_data, $subject) {
                $message->to($mail_data['email'])->subject($subject);
            });
        }
        // //set back to the previous config set in .env
        // Config::set('mail.mailers.smtp', $backup);
    }




    public static function get_where_clause_with_match_mode(string $matchMode, string $search, $column)
    {
        if ($matchMode == 'startsWith') {
            return  [$column, 'like', "{$search}%"];
        } else if ($matchMode == 'contains') {
            return [$column, 'like', "%{$search}%"];
        } else if ($matchMode == 'endsWith') {
            return [$column, 'like', "%{$search}"];
        } else if ($matchMode == 'equals') {
            return [$column, '=', $search];
        } else if ($matchMode == 'notEquals') {
            return [$column, '!=', $search];
        }
        return  [$column, 'like', "%{$search}%"]; //return  contains by default
    }

    /**
     * ie inactive ==0
     *    active ==1
     */
    public static function get_where_clause_for_column_alias($searchFields, string $matchMode, string $search)
    {
        $filtered_values = [];
        foreach ($searchFields as $key => $value) {
            if ($matchMode == 'contains' && str_contains($key, strtolower($search))) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == 'notEquals' && $key != $search) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == 'equals' && $key == $search) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == 'endsWith' && str_ends_with(strtolower($key), strtolower($search))) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == 'startsWith' && str_starts_with(strtolower($key), strtolower($search))) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == 'startsWith' && str_contains(strtolower($key), strtolower($search))) {
                array_push($filtered_values, [$key => $value]);
            }
        }

        // echo 'XXX';
        //echo json_encode($filtered_values);
        //echo 'YYY';


        $where_array = [];
        foreach ($searchFields as $key => $value) {
            foreach ($filtered_values as $filtered_value) {
                if (key_exists($key, $filtered_value)) {
                    $value = $filtered_value[$key];
                    array_push($where_array, [$value[0], $value[1], $value[2]]);
                }
            }
        }

        if (count($where_array) == 0) {
            $search_field_value = "";
            foreach ($searchFields as $key => $value) {
                $search_field_value = $value[0];
                break;
            }
            $where_array = [[$search_field_value, '=', '-100212120010212']];
        }

        //echo json_encode($where_array);

        return $where_array;
    }


    public static function get_role_permissions($role_id)
    {
        $fetch = DB::select('select permissions.id,permissions.name,permissions.module from role_has_permissions join permissions on role_has_permissions.permission_id=permissions.id where role_id = ?', [$role_id]);
        return $fetch;
    }

    /**
     * ie {id: 1, name: 'create', module: 'Product'} -> [Product-create]
     */
    public static function flatten_permissions($permissions)
    {
        $results = [];
        foreach ($permissions as $value) {
            $permission = $value->module . '-' . $value->name;
            array_push($results, $permission);
        }
        return $results;
    }

    public static function get_permissions_ids_for_role($role_id)
    {
        $permissions = Helper::get_role_permissions($role_id);
        $permission_ids = [];
        foreach ($permissions as $value) {
            array_push($permission_ids, $value->id);
        }
        return $permission_ids;
    }

    /**
     * actions are combined with module ie 'Product-edit'
     * 
     */
    public static function user_can_permform_action($action, $permissions)
    {
        foreach ($permissions as $value) {
            $permission = $value->module . '-' . $value->name;

            if ($action == $permission) {
                return true;
            }
        }
        return false;
    }

    //checks if the user can perform any of the actions in the actions list
    public static function user_can_perform_actions($actions, $permissions)
    {
        $results = false;
        foreach ($actions as $action) {
            if (Helper::user_can_permform_action($action, $permissions)) {
                $results = true;
            }
        }
        return $results;
    }
}
