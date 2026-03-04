<?php

namespace App\Http\Controllers;

use App\Models\ChatReport;
use App\Models\Report;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $status = (string) $request->string('status', 'open');
        $allowedStatuses = ['all', 'open', 'reviewing', 'resolved', 'rejected'];

        if (! in_array($status, $allowedStatuses, true)) {
            $status = 'open';
        }

        $reports = Report::query()
            ->with(['reporter', 'reportable'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $chatReports = ChatReport::query()
            ->with(['user', 'message'])
            ->latest()
            ->take(30)
            ->get();

        return view('moderation.index', compact('reports', 'chatReports', 'status'));
    }

    public function updateStatus(Request $request, Report $report)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'status' => 'required|in:open,reviewing,resolved,rejected',
            'resolution_notes' => 'nullable|string|max:2000',
        ]);

        $report->status = $data['status'];
        $report->resolution_notes = $data['resolution_notes'] ?? null;
        $report->handled_by_user_id = $request->user()->id;
        $report->handled_at = now();
        $report->save();

        return back()->with('status', 'Report status updated.');
    }
}
