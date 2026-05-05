<?php

namespace Database\Seeders;

use App\Models\Firm;
use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Database\Seeder;

class PipelineSeeder extends Seeder
{
    public function run(): void
    {
        $firm = Firm::first();
        if (!$firm) return;

        $pipelines = [
            [
                'name'   => 'Sales Pipeline',
                'stages' => [
                    ['name' => 'New', 'color' => '#3b82f6'],
                    ['name' => 'Contacted', 'color' => '#06b6d4'],
                    ['name' => 'Qualified', 'color' => '#8b5cf6'],
                    ['name' => 'Proposal Sent', 'color' => '#f59e0b'],
                    ['name' => 'Converted', 'color' => '#10b981'],
                    ['name' => 'Lost', 'color' => '#ef4444'],
                ],
            ],
            [
                'name'   => 'Support Pipeline',
                'stages' => [
                    ['name' => 'Open', 'color' => '#3b82f6'],
                    ['name' => 'In Progress', 'color' => '#f59e0b'],
                    ['name' => 'Resolved', 'color' => '#10b981'],
                    ['name' => 'Closed', 'color' => '#6b7280'],
                ],
            ],
        ];

        foreach ($pipelines as $pipelineData) {
            $pipeline = Pipeline::create([
                'name'    => $pipelineData['name'],
                'firm_id' => $firm->id,
            ]);

            foreach ($pipelineData['stages'] as $stage) {
                if (is_array($stage)) {
                    Stage::create([
                        'name'        => $stage['name'],
                        'pipeline_id' => $pipeline->id,
                        'color'       => $stage['color'] ?? '#10b981',
                    ]);
                } else {
                    Stage::create([
                        'name'        => $stage,
                        'pipeline_id' => $pipeline->id,
                        'color'       => '#10b981',
                    ]);
                }
            }
        }
    }
}
