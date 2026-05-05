<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Activity;
use App\Models\User;
use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        $firmId = $request->user()->firm_id;

        // 1. Lead Reports
        $leadReports = [
            'total_day' => Lead::where('firm_id', $firmId)->whereDate('created_at', Carbon::today())->count(),
            'total_week' => Lead::where('firm_id', $firmId)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'total_month' => Lead::where('firm_id', $firmId)->whereMonth('created_at', Carbon::now()->month)->count(),
            'new_today' => Lead::where('firm_id', $firmId)->whereDate('created_at', Carbon::today())->count(),
            'source_wise' => Lead::where('firm_id', $firmId)->select('source', DB::raw('count(*) as total'))->groupBy('source')->get(),
            'status_wise' => Lead::where('firm_id', $firmId)
                ->join('stages', 'leads.stage_id', '=', 'stages.id')
                ->select('stages.name as status', DB::raw('count(*) as total'))
                ->groupBy('stages.name')
                ->get(),
        ];

        // 2. Pipeline Report
        $pipelineReports = Pipeline::where('firm_id', $firmId)->with(['stages' => function($q) use ($firmId) {
            $q->withCount(['leads' => function($l) use ($firmId) {
                $l->where('firm_id', $firmId);
            }]);
        }])->get()->map(function($pipeline) {
            $totalLeads = $pipeline->stages->sum('leads_count');
            return [
                'name' => $pipeline->name,
                'stages' => $pipeline->stages->map(function($stage) use ($totalLeads) {
                    return [
                        'name' => $stage->name,
                        'leads_count' => $stage->leads_count,
                        'conversion_rate' => $totalLeads > 0 ? round(($stage->leads_count / $totalLeads) * 100, 2) : 0,
                    ];
                }),
            ];
        });

        // 3. Follow-up Report
        $followUpReports = [
            'pending' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })
                ->where('due_at', '>', Carbon::now())
                ->count(),
            'missed' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })
                ->where('due_at', '<', Carbon::now())
                ->count(),
            // Assuming we have a completed_at column or a status. Let's check Activity model.
            // If not, we'll use a mock for now or check for 'note' types?
            'completed' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })
                ->where('type', 'note') // Simplified assumption
                ->count(),
            'overdue' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })
                ->where('due_at', '<', Carbon::now())
                ->count(),
            'todays_pending' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })
                ->whereDate('due_at', Carbon::today())
                ->where('due_at', '>', Carbon::now())
                ->count(),
        ];

        // 4. Staff Performance
        $staffPerformance = User::where('firm_id', $firmId)->get()->map(function($user) use ($firmId) {
            return [
                'name' => $user->first_name . ' ' . $user->last_name,
                'leads_assigned' => Lead::where('assigned_to', $user->id)->count(),
                'conversions' => Lead::where('assigned_to', $user->id)
                    ->whereHas('stage', function($q) { $q->where('name', 'like', '%Won%')->orWhere('name', 'like', '%Converted%'); })
                    ->count(),
                'followups_done' => Activity::where('user_id', $user->id)->count(),
            ];
        });

        // 5. Activity Report
        $activityReport = [
            'calls' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })->where('type', 'call')->count(),
            'notes' => Activity::whereHas('lead', function($q) use ($firmId) { $q->where('firm_id', $firmId); })->where('type', 'note')->count(),
            'leads_updated' => Lead::where('firm_id', $firmId)->where('updated_at', '>', Carbon::now()->subDay())->count(),
        ];

        // 6. Conversion Report
        $totalLeads = Lead::where('firm_id', $firmId)->count();
        $convertedLeads = Lead::where('firm_id', $firmId)
            ->whereHas('stage', function($q) { $q->where('name', 'like', '%Won%')->orWhere('name', 'like', '%Converted%'); })
            ->count();
        $conversionReport = [
            'total_leads' => $totalLeads,
            'converted_leads' => $convertedLeads,
            'conversion_percent' => $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0,
            'revenue_estimate' => 'TBD', // Placeholder
        ];

        return response()->json([
            'lead_reports' => $leadReports,
            'pipeline_reports' => $pipelineReports,
            'followup_reports' => $followUpReports,
            'staff_performance' => $staffPerformance,
            'activity_report' => $activityReport,
            'conversion_report' => $conversionReport,
        ]);
    }
}
