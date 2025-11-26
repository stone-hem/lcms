<?php

namespace App\Http\Controllers;

use App\Models\Document\LawyerType;
use Illuminate\Http\Request;

class LawyerTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lawyerTypes = LawyerType::all();
        return response()->json($lawyerTypes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $lawyerType = new LawyerType();
        $lawyerType->name = $request->input('name');
        $lawyerType->description = $request->input('description');
        $lawyerType->save();

        return response()->json($lawyerType, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LawyerType $lawyerType)
    {
        return response()->json($lawyerType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LawyerType $lawyerType)
    {
        $lawyerType->name = $request->input('name');
        $lawyerType->description = $request->input('description');
        $lawyerType->save();

        return response()->json($lawyerType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LawyerType $lawyerType)
    {
        $lawyerType->delete();
        return response()->json(null, 204);
    }
}
