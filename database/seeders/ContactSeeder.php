<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Firm;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $firm = Firm::first();

        $contacts = [
            [
                'firm_id'     => $firm->id,
                'first_name'  => 'Rahul',
                'middle_name' => '',
                'last_name'   => 'Sharma',
                'email'       => 'rahul.sharma@example.com',
                'mobile'      => '9811111111',
                'gender'      => 'Male',
                'city'        => 'Mumbai',
                'state'       => 'Maharashtra',
                'country'     => 'India',
            ],
            [
                'firm_id'     => $firm->id,
                'first_name'  => 'Priya',
                'middle_name' => '',
                'last_name'   => 'Verma',
                'email'       => 'priya.verma@example.com',
                'mobile'      => '9822222222',
                'gender'      => 'Female',
                'city'        => 'Delhi',
                'state'       => 'Delhi',
                'country'     => 'India',
            ],
            [
                'firm_id'     => $firm->id,
                'first_name'  => 'Amit',
                'middle_name' => 'R',
                'last_name'   => 'Patel',
                'email'       => 'amit.patel@example.com',
                'mobile'      => '9833333333',
                'gender'      => 'Male',
                'city'        => 'Ahmedabad',
                'state'       => 'Gujarat',
                'country'     => 'India',
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::create($contact);
        }
    }
}
