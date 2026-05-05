<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Firm;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $firm     = Firm::first();
        $staff    = User::where('role', 'staff')->where('firm_id', $firm->id)->first();
        $pipeline = Pipeline::where('name', 'Sales Pipeline')->where('firm_id', $firm->id)->first();
        $contacts = Contact::where('firm_id', $firm->id)->get();

        $stages   = Stage::where('pipeline_id', $pipeline->id)->get()->keyBy('name');
        $sources  = ['manual', 'website', 'facebook', 'mobile_sync'];

        foreach ($contacts as $i => $contact) {
            $stageName = array_values(['New', 'Contacted', 'Qualified'])[$i % 3];
            Lead::create([
                'firm_id'     => $firm->id,
                'assigned_to' => $staff->id,
                'contact_id'  => $contact->id,
                'pipeline_id' => $pipeline->id,
                'stage_id'    => $stages[$stageName]->id,
                'source'      => $sources[$i % count($sources)],
            ]);
        }
    }
}
