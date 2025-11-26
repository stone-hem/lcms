<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GooglePlacesController extends Controller
{
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->input("query");
        $url = "https://maps.googleapis.com/maps/api/place/autocomplete/json";

        $response = Http::get($url, [
            "input" => $query,
            "components" => "country:KE", // Restrict to Kenya
            "key" => env("GOOGLE_PLACES_API_KEY"),
        ]);

        return response()->json($response->json());
    }

    public function placeDetails(Request $request): JsonResponse
    {
        $placeId = $request->input("place_id");
        $url = "https://maps.googleapis.com/maps/api/place/details/json";

        $response = Http::get($url, [
            "place_id" => $placeId,
            "key" => env("GOOGLE_PLACES_API_KEY"),
        ]);

        $data = $response->json();

        if (isset($data["result"]["photos"])) {
            unset($data["result"]["photos"]);
        }

        if (isset($data["result"]["icon"])) {
            unset($data["result"]["icon"]);
        }
        if (isset($data["result"]["icon_background_color"])) {
            unset($data["result"]["icon_background_color"]);
        }
        if (isset($data["result"]["icon_mask_base_uri"])) {
            unset($data["result"]["icon_mask_base_uri"]);
        }
        
        if (isset($data["result"]["current_opening_hours"])) {
            unset($data["result"]["current_opening_hours"]);
        }
        
        if (isset($data["result"]["opening_hours"])) {
            unset($data["result"]["opening_hours"]);
        }
        
        if (isset($data["result"]["reviews"])) {
            unset($data["result"]["reviews"]);
        }

        return response()->json($data);
    }
}
