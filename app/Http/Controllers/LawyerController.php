<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLawyerRequest;
use App\Http\Requests\UpdateLawyerRequest;
use App\Models\Lawyer\Lawyer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Shared\Address;
use App\Actions\Utils\RespondWithError;
use App\Actions\Utils\RespondWithSuccess;
use App\Helpers\EmailsHelper;
use App\Helpers\LogActivity;
use App\Helpers\SmsesHelper;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendSmsNotification;
use Illuminate\Support\Facades\Validator;

class LawyerController extends Controller
{
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email|unique:lawyers,email|regex:/(.+)@(.+)\.(.+)/i',
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'phone' => 'required|min:9|max:13|unique:users,phone',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'lawyer_type_id' => 'required|exists:lawyer_types,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'status' => 422,
                'code' => 'ACCESS_DENIED',
                'message' => $validator->errors()->first(),
                'recommendation' => 'Fill all required fields correctly while observing rules',
                'payload' => $validator->errors()->toArray()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $address = new Address();
                $address->type = 'lawyer';
                $address->address_line = $request->input('address_line');
                $address->town = $request->input('town');
                $address->postal_code = $request->input('postal_code');
                $address->postal_address = $request->input('postal_address');
                $address->county_code = $request->input('county_code');
                $address->sub_code = $request->input('sub_code');
                $address->save();

                $user = new User();
                $user->role_id = 2;
                $user->first_name = $request->input('first_name');
                $user->last_name = $request->input('last_name');
                $user->phone = $request->input('phone');
                $user->password = Hash::make('Lawyer#24');
                $user->email = $request->input('email');
                $user->calling_code = '254';
                $user->is_active = 1;
                $user->save();

                $lawyer = new Lawyer();
                $lawyer->first_name = $request->input('first_name');
                $lawyer->middle_name = $request->input('middle_name');
                $lawyer->last_name = $request->input('last_name');
                $lawyer->gender = $request->input('gender');
                $lawyer->birth_date = $request->input('date_of_birth');
                $lawyer->email = $request->input('email');
                $lawyer->lawyer_type_id = $request->input('lawyer_type_id');
                $lawyer->user_id = $user->id;
                $lawyer->address_id = $address->id;
                $lawyer->calling_code = '254';
                $lawyer->is_internal = true;
                $lawyer->phone = $request->input('phone');

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $path = $file->store('lawyer', 'public');
                    $lawyer->photo_url = $path;
                }
                $lawyer->save();

                $lawyer_first_name = $request->input('first_name');
                $app_name = env('APP_NAME');
                $app_url = env('APP_URL');
                $app_owner = env('APP_OWNER');

                $phones = $request->input('phone');

                if (strpos($phones, '0') === 0) {
                    $phones = '254' . substr($phones, 1);
                } elseif (strpos($phones, '+254') === 0) {
                    $phones = substr($phones, 1);
                } elseif (strpos($phones, '254') !== 0) {
                    $phones = '254' . $phones;
                }


                $message =  "Hello $lawyer_first_name,\n You - $app_name - account has successfully been created.\n\nCheck Mail for more details.\n\n\nSent By $app_owner";

                SendSmsNotification::dispatch($phones,$message);
                // SmsesHelper::twoauth($phones,$subject);
                SendEmailNotification::dispatch($request->email, $app_url, $app_owner, $app_name, $lawyer_first_name);
                LogActivity::addToLog("Added Lawyer ".$lawyer_first_name.'-'.$request->email);

            });

            return response()->json([
                'status' => 200,
                'message' => 'Lawyer created successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred during creation',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function index(){
        LogActivity::addToLog("Viewed Lawyers Page");
        try {
            $data = Lawyer::with(['lawyerType'])->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'success','lawyers' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 500);
        }
    }

    public function show($id){
        try {
            $data = Lawyer::where('id',$id)->with(['lawyerType', 'address'])->first();
            if($data){
            LogActivity::addToLog("Viewed Lawyer $data->email Page");
            return response()->json(['status' => 'success','data' => $data], 200);
            }else{
                LogActivity::addToLog("Viewed Lawyer with id: $id Page");
                return response()->json(['status' => 'error','data' => 'Lawyer Does not exixt'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 500);
        }
    }

}
