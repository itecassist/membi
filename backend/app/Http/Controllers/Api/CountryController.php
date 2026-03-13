<?php

namespace App\Http\Controllers\Api;

use App\Domain\Organisation\Models\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ZoneResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CountryResource::collection(Country::orderBy('name')->get());
    }

    public function show(Country $country): JsonResponse
    {
        return response()->json(['data' => new CountryResource($country)]);
    }

    public function zones(Country $country): AnonymousResourceCollection
    {
        return ZoneResource::collection($country->zones()->orderBy('name')->get());
    }
}
