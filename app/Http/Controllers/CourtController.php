<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourtRequest;
use App\Http\Requests\UpdateCourtRequest;
use App\Models\Court\Court;

class CourtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourtRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Court $court)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourtRequest $request, Court $court)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Court $court)
    {
        //
    }
}
