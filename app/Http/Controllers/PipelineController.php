<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $pipelines = Pipeline::where('firm_id', $request->user()->firm_id)
            ->with('stages')
            ->get();

        return response()->json(['pipelines' => $pipelines]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:150',
            'stages' => 'nullable|array',
            'stages.*' => 'string|max:100',
        ]);

        $pipeline = Pipeline::create([
            'name'    => $data['name'],
            'firm_id' => $request->user()->firm_id,
        ]);

        if (!empty($data['stages'])) {
            foreach ($data['stages'] as $stageName) {
                Stage::create(['name' => $stageName, 'pipeline_id' => $pipeline->id]);
            }
        }

        return response()->json(['message' => 'Pipeline created.', 'pipeline' => $pipeline->load('stages')], 201);
    }

    public function show(Request $request, Pipeline $pipeline)
    {
        $this->authorizePipeline($request, $pipeline);

        return response()->json(['pipeline' => $pipeline->load('stages')]);
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $this->authorizePipeline($request, $pipeline);

        $data = $request->validate(['name' => 'required|string|max:150']);
        $pipeline->update($data);

        return response()->json(['message' => 'Pipeline updated.', 'pipeline' => $pipeline]);
    }

    public function destroy(Request $request, Pipeline $pipeline)
    {
        $this->authorizePipeline($request, $pipeline);
        $pipeline->delete();

        return response()->json(['message' => 'Pipeline deleted.']);
    }

    // ── Stage sub-resource ──────────────────────────────────────────────────

    public function storeStage(Request $request, Pipeline $pipeline)
    {
        $this->authorizePipeline($request, $pipeline);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);
        $stage = Stage::create([
            'name' => $data['name'],
            'pipeline_id' => $pipeline->id,
            'color' => $data['color'] ?? null,
        ]);

        return response()->json(['message' => 'Stage created.', 'stage' => $stage], 201);
    }

    public function updateStage(Request $request, Pipeline $pipeline, Stage $stage)
    {
        $this->authorizePipeline($request, $pipeline);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);
        $stage->update($data);

        return response()->json(['message' => 'Stage updated.', 'stage' => $stage]);
    }

    public function destroyStage(Request $request, Pipeline $pipeline, Stage $stage)
    {
        $this->authorizePipeline($request, $pipeline);
        $stage->delete();

        return response()->json(['message' => 'Stage deleted.']);
    }

    private function authorizePipeline(Request $request, Pipeline $pipeline): void
    {
        if ($pipeline->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
