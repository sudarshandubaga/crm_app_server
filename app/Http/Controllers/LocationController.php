<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getCountries()
    {
        return response()->json(Country::orderBy('name')->get());
    }

    public function getStates(Request $request)
    {
        $query = State::query();
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        return response()->json($query->orderBy('name')->get());
    }

    public function getCities(Request $request)
    {
        $query = City::query();
        if ($request->has('state_id')) {
            $query->where('state_id', $request->state_id);
        }
        return response()->json($query->orderBy('name')->get());
    }

    public function addCountry(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:countries,name',
            'code' => 'nullable|string'
        ]);
        $country = Country::create($data);
        return response()->json($country, 201);
    }

    public function addState(Request $request)
    {
        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string'
        ]);
        $state = State::create($data);
        return response()->json($state, 201);
    }

    public function addCity(Request $request)
    {
        $data = $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string'
        ]);
        $city = City::create($data);
        return response()->json($city, 201);
    }
}
