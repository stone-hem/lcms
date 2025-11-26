<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLawyerPaymentRequest;
use App\Http\Requests\UpdateLawyerPaymentRequest;
use App\Models\Lawyer\LawyerPayment;

class LawyerPaymentController extends Controller
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
    public function store(StoreLawyerPaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LawyerPayment $lawyerPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLawyerPaymentRequest $request, LawyerPayment $lawyerPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LawyerPayment $lawyerPayment)
    {
        //
    }
}
