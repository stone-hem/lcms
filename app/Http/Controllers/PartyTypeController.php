<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartyTypeRequest;
use App\Http\Requests\UpdatePartyTypeRequest;
use App\Models\Party\PartyType;

class PartyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partyTypes = PartyType::all();
        return response()->json($partyTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePartyTypeRequest $request)
    {
        $partyType = new PartyType();
        $partyType->name = $request->input('name');
        $partyType->description = $request->input('description');
        $partyType->save();

        return response()->json($partyType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PartyType $partyType)
    {
        return response()->json($partyType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePartyTypeRequest $request, PartyType $partyType)
    {
        $partyType->name = $request->input('name');
        $partyType->description = $request->input('description');
        $partyType->save();

        return response()->json($partyType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartyType $partyType)
    {
        $partyType->delete();
        return response()->json(null, 204);
    }
}
