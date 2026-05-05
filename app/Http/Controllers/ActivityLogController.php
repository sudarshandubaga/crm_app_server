<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Get activity log for a specific lead.
     */
    public function forLead(Request $request, Lead $lead)
    {
        if ($lead->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }

        $logs = ActivityLog::where('subject_type', Lead::class)
            ->where('subject_id', $lead->id)
            ->with('user')
            ->latest()
            ->get();

        return response()->json(['logs' => $logs]);
    }

    /**
     * Get all activity logs for the firm (admin only).
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Admins only.');
        }

        $logs = ActivityLog::where('firm_id', $request->user()->firm_id)
            ->with(['user', 'subject'])
            ->latest()
            ->paginate(30);

        return response()->json($logs);
    }
}
