<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Firm-wide list — all activities across all leads in the firm.
     * Used by the Follow-ups tab in the mobile app.
     */
    public function firmIndex(Request $request)
    {
        $firmId = $request->user()->firm_id;
        $status = $request->query('status'); // pending, upcoming, missed, done
        $date = $request->query('date');     // YYYY-MM-DD

        $query = Activity::whereHas('lead', function ($q) use ($firmId) {
                $q->where('firm_id', $firmId);
            })
            ->with([
                'lead.contact',
                'user',
            ]);

        if ($status === 'pending') {
            if ($date) {
                $query->whereDate('due_at', $date);
            }
            $query->where('status', 'pending')->where('due_at', '>=', now());
        } elseif ($status === 'upcoming') {
            $query->where('status', 'pending')->where('due_at', '>', now());
            if ($date) {
                $query->whereDate('due_at', $date);
            }
        } elseif ($status === 'missed') {
            $query->where('status', 'pending')->where('due_at', '<', now());
            if ($date) {
                $query->whereDate('due_at', $date);
            }
        } elseif ($status === 'done') {
            $query->where('status', 'completed');
            if ($date) {
                $query->whereDate('due_at', $date);
            }
        }

        $activities = $query->orderBy('due_at', 'asc')->get();

        return response()->json(['data' => $activities]);
    }

    public function index(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);

        return response()->json([
            'activities' => $lead->activities()->with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);

        $data = $request->validate([
            'type'   => 'required|in:call,email,meeting,note,task',
            'notes'  => 'nullable|string',
            'due_at' => 'nullable|date',
            'status' => 'nullable|string|in:pending,completed',
            'result_notes' => 'nullable|string',
        ]);

        $activity = Activity::create(array_merge($data, [
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
        ]));

        return response()->json(['message' => 'Activity logged.', 'activity' => $activity->load('user')], 201);
    }

    public function update(Request $request, Lead $lead, Activity $activity)
    {
        $this->authorizeLead($request, $lead);

        $data = $request->validate([
            'type'   => 'sometimes|in:call,email,meeting,note,task',
            'notes'  => 'nullable|string',
            'due_at' => 'nullable|date',
            'status' => 'nullable|string|in:pending,completed',
            'result_notes' => 'nullable|string',
        ]);

        $activity->update($data);

        return response()->json(['message' => 'Activity updated.', 'activity' => $activity]);
    }

    public function destroy(Request $request, Lead $lead, Activity $activity)
    {
        $this->authorizeLead($request, $lead);
        $activity->delete();

        return response()->json(['message' => 'Activity deleted.']);
    }

    private function authorizeLead(Request $request, Lead $lead): void
    {
        if ($lead->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
