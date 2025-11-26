<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJudgeRequest;
use App\Http\Requests\UpdateJudgeRequest;
use App\Models\Court\Judge;

class JudgeController extends Controller
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
    public function store(StoreJudgeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Judge $judge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJudgeRequest $request, Judge $judge)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Judge $judge)
    {
        //
    }
}
