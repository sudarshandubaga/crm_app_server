<?php

namespace Database\Seeders;

use App\Models\Firm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FirmSeeder extends Seeder
{
    public function run(): void
    {
        $firm = Firm::create([
            'name'     => 'Acme CRM',
            'category' => 'Technology',
            'timezone' => 'Asia/Kolkata',
            'settings' => ['currency' => 'INR', 'date_format' => 'd/m/Y'],
        ]);

        // Admin user
        User::create([
            'first_name'  => 'Admin',
            'middle_name' => '',
            'last_name'   => 'User',
            'email'       => 'admin@acmecrm.com',
            'mobile'      => '9000000001',
            'password'    => Hash::make('password'),
            'role'        => 'admin',
            'firm_id'     => $firm->id,
            'is_active'   => true,
        ]);

        // Staff user
        User::create([
            'first_name'  => 'John',
            'middle_name' => 'K',
            'last_name'   => 'Smith',
            'email'       => 'staff@acmecrm.com',
            'mobile'      => '9000000002',
            'password'    => Hash::make('password'),
            'role'        => 'staff',
            'firm_id'     => $firm->id,
            'is_active'   => true,
        ]);
    }
}
