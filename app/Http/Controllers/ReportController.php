<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reportable_type' => 'required|in:post',
            'reportable_id' => 'required|integer|min:1',
            'reason' => 'required|in:spam,scam,hate,harassment,illegal,other',
            'details' => 'nullable|string|max:2000',
        ]);

        $typeMap = [
            'post' => \App\Models\Post::class,
        ];

        Report::create([
            'reporter_user_id' => $request->user()->id,
            'reportable_type' => $typeMap[$data['reportable_type']],
            'reportable_id' => $data['reportable_id'],
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('status', 'Report submitted. Thanks.');
    }
}
