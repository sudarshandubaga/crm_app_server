<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $firm = $request->user()->firm;
        $query = Plan::where('is_active', true);

        if ($firm->has_used_first_time_offer) {
            $query->where('is_first_time_only', false);
        }

        return response()->json($query->get());
    }
}
