<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegalCaseLawyerRequest;
use App\Http\Requests\UpdateLegalCaseLawyerRequest;
use App\Models\LegalCase\LegalCaseLawyer;

class LegalCaseLawyerController extends Controller
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
    public function store(StoreLegalCaseLawyerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LegalCaseLawyer $legalCaseLawyer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLegalCaseLawyerRequest $request, LegalCaseLawyer $legalCaseLawyer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LegalCaseLawyer $legalCaseLawyer)
    {
        //
    }
}
