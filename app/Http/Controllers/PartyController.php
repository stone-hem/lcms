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
    // Individual Party Methods
    public function createIndividual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:individual_parties,email|regex:/(.+)@(.+)\.(.+)/i',
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'phone' => 'required|min:9|max:13|unique:individual_parties,phone',
            'date_of_birth' => 'required',
            'location' => 'required',
            'gender' => 'required',
            'party_type_id' => 'required|exists:party_types,id',
            'legal_case_id' => 'required|exists:legal_cases,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::transaction(function () use ($request) {
                $client = new IndividualParty();
                $client->party_type_id = $request->input('party_type_id');
                $client->first_name = $request->input('first_name');
                $client->middle_name = $request->input('middle_name');
                $client->last_name = $request->input('last_name');
                $client->calling_code = '254';
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
            });

            return response()->json([
                'status' => 200,
                'message' => 'Individual party created successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function updateIndividual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:individual_parties,id',
            'email' => 'required|email|unique:individual_parties,email,' . $request->id . '|regex:/(.+)@(.+)\.(.+)/i',
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'phone' => 'required|min:9|max:13|unique:individual_parties,phone,' . $request->id,
            'date_of_birth' => 'required',
            'location' => 'required',
            'gender' => 'required',
            'party_type_id' => 'required|exists:party_types,id',
            'legal_case_id' => 'required|exists:legal_cases,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::transaction(function () use ($request) {
                $client = IndividualParty::findOrFail($request->id);
                $client->party_type_id = $request->input('party_type_id');
                $client->first_name = $request->input('first_name');
                $client->middle_name = $request->input('middle_name');
                $client->last_name = $request->input('last_name');
                $client->calling_code = '254';
                $client->phone = $request->input('phone');
                $client->email = $request->input('email');
                $client->gender = $request->input('gender');
                $client->birth_date = $request->input('date_of_birth');
                $client->location = json_encode($request->input('location'));
                $client->legal_case_id = $request->input('legal_case_id');
                $client->save();
            });

            return response()->json([
                'status' => 200,
                'message' => 'Individual party updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroyIndividual(Request $request)
    {
        try {
            $client = IndividualParty::findOrFail($request->id);
            $client->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Individual party deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    // Firm Party Methods
    public function createFirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:firm_parties,email|regex:/(.+)@(.+)\.(.+)/i',
            'name' => 'required|min:2',
            'phone' => 'required|min:9|max:13|unique:firm_parties,phone',
            'location' => 'required',
            'party_type_id' => 'required|exists:party_types,id',
            'legal_case_id' => 'required|exists:legal_cases,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::transaction(function () use ($request) {
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
            });

            return response()->json([
                'status' => 200,
                'message' => 'Firm party created successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function updateFirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:firm_parties,id',
            'email' => 'required|email|unique:firm_parties,email,' . $request->id . '|regex:/(.+)@(.+)\.(.+)/i',
            'name' => 'required|min:2',
            'phone' => 'required|min:9|max:13|unique:firm_parties,phone,' . $request->id,
            'location' => 'required',
            'party_type_id' => 'required|exists:party_types,id',
            'legal_case_id' => 'required|exists:legal_cases,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::transaction(function () use ($request) {
                $client = FirmParty::findOrFail($request->id);
                $client->party_type_id = $request->input('party_type_id');
                $client->name = $request->input('name');
                $client->calling_code = '254';
                $client->phone = $request->input('phone');
                $client->email = $request->input('email');
                $client->location = json_encode($request->input('location'));
                $client->legal_case_id = $request->input('legal_case_id');
                $client->save();
            });

            return response()->json([
                'status' => 200,
                'message' => 'Firm party updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroyFirm(Request $request)
    {
        try {
            $client = FirmParty::findOrFail($request->id);
            $client->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Firm party deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    // Helper Methods
    private function validationErrorResponse($validator)
    {
        return response()->json([
            'error' => true,
            'status' => 403,
            'code' => 'ACCESS_DENIED',
            'message' => $validator->errors()->first(),
            'recommendation' => 'Fill all required fields correctly while observing rules',
            'payload' => $validator->errors()->toArray()
        ], 403);
    }

    private function errorResponse($exception)
    {
        return response()->json([
            'error' => true,
            'message' => 'An error occurred during the operation',
            'details' => $exception->getMessage()
        ], 500);
    }
}