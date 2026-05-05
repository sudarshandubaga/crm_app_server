<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use Illuminate\Http\Request;

class FirmController extends Controller
{
    public function show(Request $request)
    {
        $firm = $request->user()->firm;
        if ($firm->logo) {
            $firm->logo = asset('storage/' . $firm->logo);
        }
        return response()->json([
            'firm' => $firm
        ]);
    }

    public function update(Request $request)
    {
        $firm = $request->user()->firm;

        $data = $request->validate([
            'name'     => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:100',
            'settings' => 'nullable|array',
            'logo'     => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $firm->update($data);

        return response()->json([
            'message' => 'Firm details updated.',
            'firm'    => $firm
        ]);
    }
}
