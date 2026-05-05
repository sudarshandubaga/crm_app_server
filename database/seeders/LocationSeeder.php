<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $india = Country::updateOrCreate(['name' => 'India'], ['code' => 'IN']);
        
        $states = [
            'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur'],
            'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur'],
            'Karnataka' => ['Bangalore', 'Mysore', 'Hubli'],
            'Delhi' => ['New Delhi'],
            'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara'],
        ];

        foreach ($states as $stateName => $cities) {
            $state = State::updateOrCreate([
                'country_id' => $india->id,
                'name' => $stateName,
            ]);

            foreach ($cities as $cityName) {
                City::updateOrCreate([
                    'state_id' => $state->id,
                    'name' => $cityName,
                ]);
            }
        }
    }
}
