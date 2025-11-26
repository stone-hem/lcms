<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartyRequest;
use App\Http\Requests\UpdatePartyRequest;
use App\Models\FirmParty;
use App\Models\IndividualParty;
use App\Models\Party\Party;
use App\Models\Party\PartyType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Shared\Address;


class PartyController extends Controller
{

    public function create(Request $request)
    {
        if($request->identifier == 1){
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:individual_parties,email|regex:/(.+)@(.+)\.(.+)/i',
                'first_name' => 'required|min:2',
                'last_name' => 'required|min:2',
                'phone' => 'required|min:9|max:13|unique:individual_parties,phone',
                'date_of_birth' => 'required',
                // 'address_line' => 'required',
                // 'postal_code' => 'required',
                // 'postal_address' => 'required',
                // 'town' => 'required',
                'location' => 'required',
                'gender' => 'required',
                'party_type_id' => 'required|exists:party_types,id',
                'legal_case_id' => 'required|exists:legal_cases,id'

            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:firm_parties,email|regex:/(.+)@(.+)\.(.+)/i',
                'name' => 'required|min:2',
                'phone' => 'required|min:9|max:13|unique:firm_parties,phone',
                // 'address_line' => 'required',
                // 'postal_code' => 'required',
                // 'postal_address' => 'required',
                // 'town' => 'required',
                'location' => 'required',
                'party_type_id' => 'required|exists:party_types,id',
                'legal_case_id' => 'required|exists:legal_cases,id'

            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'status' => 403,
                'code' => 'ACCESS_DENIED',
                'message' => $validator->errors()->first(),
                'recommendation' => 'Fill all required fields correctly while observing rules',
                'payload' => $validator->errors()->toArray()
            ], 403);
        }

        try {
            DB::transaction(function () use ($request) {
                if ($request->identifier == 1) {
                        $client = new IndividualParty();
                        $client->party_type_id = $request->input('party_type_id');
                        $client->first_name = $request->input('first_name');
                        $client->middle_name = $request->input('middle_name');
                        $client->last_name = $request->input('last_name');
                        $client->calling_code ='254';
                        $client->phone = $request->input('phone');
                        $client->email = $request->input('email');
                        $client->gender = $request->input('gender');
                        $client->birth_date = $request->input('date_of_birth');
                        $client->location = json_encode($request->input('location'));
                        $client->legal_case_id = $request->input('legal_case_id');
                        $client->save();

                        $party = new Party();
                        $party->table_name = 'individual_parties';
                        $party->table_id = $client->id;
                        $party->legal_case_id = $request->input('legal_case_id');
                        $party->save();
                }else{
                        $client = new FirmParty();
                        $client->party_type_id = $request->input('party_type_id');
                        $client->name = $request->input('name');
                        $client->calling_code = '254';
                        $client->phone = $request->input('phone');
                        $client->email = $request->input('email');
                        $client->location = json_encode($request->input('location'));
                        $client->legal_case_id = $request->input('legal_case_id');
                        $client->save();

                        $party = new Party();
                        $party->table_name = 'firm_parties';
                        $party->table_id = $client->id;
                        $party->legal_case_id = $request->input('legal_case_id');
                        $party->save();
                }

            });

            return response()->json([
                'status' => 200,
                'message' => 'Party created successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred during creation',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request){
        if($request->identifier == 1){
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:individual_parties,id',
                'email' => 'required|email|unique:individual_parties,email|regex:/(.+)@(.+)\.(.+)/i',
                'first_name' => 'required|min:2',
                'last_name' => 'required|min:2',
                'phone' => 'required|min:9|max:13|unique:individual_parties,phone',
                'date_of_birth' => 'required',
                // 'address_line' => 'required',
                // 'postal_code' => 'required',
                // 'postal_address' => 'required',
                // 'town' => 'required',
                'location' => 'required',
                'gender' => 'required',
                'party_type_id' => 'required|exists:party_types,id',
                'legal_case_id' => 'required|exists:legal_cases,id'

            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:firm_parties,id',
                'email' => 'required|email|unique:firm_parties,email|regex:/(.+)@(.+)\.(.+)/i',
                'name' => 'required|min:2',
                'phone' => 'required|min:9|max:13|unique:firm_parties,phone',
                // 'address_line' => 'required',
                // 'postal_code' => 'required',
                // 'postal_address' => 'required',
                // 'town' => 'required',
                'location' => 'required',
                'party_type_id' => 'required|exists:party_types,id',
                'legal_case_id' => 'required|exists:legal_cases,id'

            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'status' => 403,
                'code' => 'ACCESS_DENIED',
                'message' => $validator->errors()->first(),
                'recommendation' => 'Fill all required fields correctly while observing rules',
                'payload' => $validator->errors()->toArray()
            ], 403);
        }

        try {
            DB::transaction(function () use ($request) {
                if ($request->identifier == 1) {
                        $client = IndividualParty::where('id',$request->id)->first();
                        $client->party_type_id = $request->input('party_type_id');
                        $client->first_name = $request->input('first_name');
                        $client->middle_name = $request->input('middle_name');
                        $client->last_name = $request->input('last_name');
                        $client->calling_code ='254';
                        $client->phone = $request->input('phone');
                        $client->email = $request->input('email');
                        $client->gender = $request->input('gender');
                        $client->birth_date = $request->input('date_of_birth');
                        $client->location = json_encode($request->input('location'));
                        $client->legal_case_id = $request->input('legal_case_id');
                        $client->update();
                }else{
                        $client = FirmParty::where('id',$request->id)->first();
                        $client->party_type_id = $request->input('party_type_id');
                        $client->name = $request->input('name');
                        $client->calling_code = '254';
                        $client->phone = $request->input('phone');
                        $client->email = $request->input('email');
                        $client->location = json_encode($request->input('location'));
                        $client->legal_case_id = $request->input('legal_case_id');
                        $client->update();

                        // $party = new Party();
                        // $party->table_name = 'firm_parties';
                        // $party->table_id = $client->id;
                        // $party->legal_case_id = $request->input('legal_case_id');
                        // $party->save();
                }

            });

            return response()->json([
                'status' => 200,
                'message' => 'Party updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred during creation',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
