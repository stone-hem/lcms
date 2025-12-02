<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartyRequest;
use App\Http\Requests\UpdatePartyRequest;
use App\Models\FirmParty;
use App\Models\IndividualParty;
use App\Models\LegalCase\LegalCaseParty;
use App\Models\Party\Party;
use App\Models\Party\PartyType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Shared\Address;
use Inertia\Inertia;

class PartyController extends Controller
{
    public function storeParty(Request $request)
    {
        $rules = [
            'party_kind' => 'required|in:individual,firm',
        
            'email' => 'required|email|unique:parties,email',
            'phone' => 'required|min:9|max:13|unique:parties,phone',
        
            'legal_case_id' => 'required|exists:legal_cases,id',
            'party_type_id' => 'nullable|exists:party_types,id',
        ];
        
        // individual validations
        if ($request->party_kind === 'individual') {
            $rules = array_merge($rules, [
                'first_name' => 'required|min:2',
                'last_name'  => 'required|min:2',
                'gender'     => 'required',
                'birth_date' => 'required',
            ]);
        }
        
        // firm validations
        if ($request->party_kind === 'firm') {
            $rules = array_merge($rules, [
                'firm_name' => 'required|min:2',
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();   // keeps form values
        }

    
        try {
            DB::transaction(function () use ($request) {
                $party = new Party();
    
                $party->party_kind = $request->party_kind;
                $party->calling_code = $request->calling_code ?? '254';
                $party->phone = $request->phone;
                $party->email = $request->email;
                $party->photo_url = $request->photo_url ?? null;
                $party->physical_address = $request->physical_address;
                $party->postal_address = $request->postal_address;
    
                if ($request->party_kind === 'individual') {
                    $party->first_name = $request->first_name;
                    $party->middle_name = $request->middle_name;
                    $party->last_name = $request->last_name;
                    $party->gender = $request->gender;
                    $party->birth_date = $request->birth_date;
                }
    
                if ($request->party_kind === 'firm') {
                    $party->firm_name = $request->firm_name;
                }
    
                $party->save();
    
                // link to case
                DB::table('legal_case_parties')->insert([
                    'party_id' => $party->id,
                    'party_type_id' => $request->party_type_id,
                    'legal_case_id' => $request->legal_case_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });
    
            return redirect()->back()->with('success', 'Party updated successfully');
    
        } catch (\Exception $e) {
            return back()->withErrors('errors', 'Failed to add');
        }
    }

    public function updateParty(Request $request)
    {
        $party = Party::findOrFail($request->id);
    
        $rules = [
            'email' => 'required|email|unique:parties,email,' . $party->id,
            'phone' => 'required|min:9|max:13|unique:parties,phone,' . $party->id,
        ];
    
        if ($party->party_kind === 'individual') {
            $rules = array_merge($rules, [
                'first_name' => 'required|min:2',
                'last_name' => 'required|min:2',
                'gender' => 'required',
                'birth_date' => 'required',
            ]);
        }
    
        if ($party->party_kind === 'firm') {
            $rules['firm_name'] = 'required|min:2';
        }
    
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
    
        try {
            DB::transaction(function () use ($request, $party) {
                $party->update($request->all());
            });
    
            return redirect()->back()->with('success', 'Party updated successfully');

    
        } catch (\Exception $e) {
            return back()->withErrors('errors', 'failed to update');
        }
    }
    public function destroyParty(Request $request)
    {
        try {
            Party::findOrFail($request->id)->delete();
    
            return response()->json([
                'message' => 'Party deleted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }
    
    public function external_firms(Request $request)
    {
        $items_per_page = $request->ipp ?? 10;
        $search_query   = $request->s   ?? "";
        $page           = $request->p   ?? 0;
        $sort_by        = $request->sort_by ?? "legal_case_parties.created_at";
        $order_by       = $request->order_by ?? "asc";
        $start_date     = $request->sd ?? null;
        $end_date       = $request->ed ?? null;
    
        // Base query: join parties so we can read firm details
        $items = DB::table('legal_case_parties')
            ->leftJoin('parties', 'parties.id', '=', 'legal_case_parties.party_id')
            ->where('parties.party_kind', 'firm') // only firms
            ->select(
                'legal_case_parties.*',
                'parties.name',
                'parties.email',
                'parties.phone',
                'parties.calling_code',
                'parties.photo_url',
                'parties.physical_address',
                'parties.postal_address',
                'parties.meta'
            );
    
        // Search filter
        if (!empty($search_query)) {
            $items->where(function ($q) use ($search_query) {
                $q->where('parties.name', 'ILIKE', "%{$search_query}%")
                  ->orWhere('parties.email', 'ILIKE', "%{$search_query}%")
                  ->orWhere('parties.phone', 'ILIKE', "%{$search_query}%");
            });
        }
    
        // Date filter
        if ($start_date && $end_date) {
            $items->whereBetween('legal_case_parties.created_at', [$start_date, $end_date]);
        }
    
        // Count BEFORE pagination
        $item_count = $items->count();
    
        // Sorting
        if ($sort_by) {
            $items->orderBy($sort_by, $order_by);
        }
    
        // Pagination (skip → offset)
        $items = $items
            ->skip($page)
            ->take($items_per_page)
            ->get();
    
        return Inertia::render('users/ExternalCounselIndex', [
            'items'       => $items,
            'total_count' => $item_count,
            'filters'     => [
                's' => $search_query,
            ],
        ]);
    }


}