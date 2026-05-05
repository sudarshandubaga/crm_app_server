<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\Activity;
use App\Models\Note;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::where('firm_id', $request->user()->firm_id)
            ->with(['contact', 'pipeline', 'stage', 'assignedTo', 'customFieldValues.customField']);

        // Optional filters
        if ($request->filled('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }
        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id'         => 'required|exists:contacts,id',
            'pipeline_id'        => 'required|exists:pipelines,id',
            'stage_id'           => 'required|exists:stages,id',
            'assigned_to'        => 'nullable|exists:users,id',
            'source'             => 'sometimes|string|max:50',
            'note'               => 'nullable|string',
            'follow_up_type'     => 'nullable|string|max:50',
            'follow_up_notes'    => 'nullable|string',
            'follow_up_due_at'   => 'nullable|date',
            'custom_fields'      => 'nullable|array',
        ]);

        $lead = Lead::create([
            'firm_id'     => $request->user()->firm_id,
            'contact_id'  => $data['contact_id'],
            'pipeline_id' => $data['pipeline_id'],
            'stage_id'    => $data['stage_id'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'source'      => $data['source'] ?? 'manual',
        ]);

        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $cf) {
                CustomFieldValue::updateOrCreate(
                    [
                        'custom_field_id' => $cf['field_id'],
                        'model_type'      => Lead::class,
                        'model_id'        => $lead->id,
                    ],
                    ['value' => $cf['value']]
                );
            }
        }

        if (!empty($data['note'])) {
            Note::create([
                'lead_id' => $lead->id,
                'user_id' => $request->user()->id,
                'note'    => $data['note'],
            ]);
        }

        if (!empty($data['follow_up_type'])) {
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => $data['assigned_to'] ?? $request->user()->id,
                'type'    => $data['follow_up_type'],
                'notes'   => $data['follow_up_notes'] ?? '',
                'due_at'  => $data['follow_up_due_at'] ?? now()->addDay(),
            ]);
        }

        ActivityLog::create([
            'firm_id'      => $request->user()->firm_id,
            'user_id'      => $request->user()->id,
            'subject_id'   => $lead->id,
            'subject_type' => Lead::class,
            'action'       => 'created',
            'properties'   => ['stage_id' => $lead->stage_id],
        ]);

        return response()->json(['message' => 'Lead created.', 'lead' => $lead->load('contact', 'pipeline', 'stage')], 201);
    }

    public function show(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);

        return response()->json([
            'lead' => $lead->load([
                'contact', 'pipeline', 'stage', 'assignedTo',
                'activities.user', 'notes.user', 'customFieldValues.customField',
            ]),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);

        $data = $request->validate([
            'pipeline_id' => 'sometimes|exists:pipelines,id',
            'stage_id'    => 'sometimes|exists:stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'source'      => 'sometimes|string|max:50',
            'custom_fields' => 'nullable|array',
        ]);

        $old = $lead->only(['stage_id', 'pipeline_id', 'assigned_to']);
        $lead->update($data);

        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $cf) {
                CustomFieldValue::updateOrCreate(
                    [
                        'custom_field_id' => $cf['field_id'],
                        'model_type'      => Lead::class,
                        'model_id'        => $lead->id,
                    ],
                    ['value' => $cf['value']]
                );
            }
        }

        // Log stage change
        if (isset($data['stage_id']) && $old['stage_id'] !== $data['stage_id']) {
            ActivityLog::create([
                'firm_id'      => $request->user()->firm_id,
                'user_id'      => $request->user()->id,
                'subject_id'   => $lead->id,
                'subject_type' => Lead::class,
                'action'       => 'stage_changed',
                'properties'   => ['old' => $old, 'new' => $lead->only(['stage_id', 'pipeline_id', 'assigned_to'])],
            ]);
        }

        return response()->json(['message' => 'Lead updated.', 'lead' => $lead->fresh('contact', 'pipeline', 'stage')]);
    }

    public function destroy(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);
        $lead->delete();

        ActivityLog::create([
            'firm_id'      => $request->user()->firm_id,
            'user_id'      => $request->user()->id,
            'subject_id'   => $lead->id,
            'subject_type' => Lead::class,
            'action'       => 'deleted',
        ]);

        return response()->json(['message' => 'Lead deleted.']);
    }

    private function authorizeLead(Request $request, Lead $lead): void
    {
        if ($lead->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
