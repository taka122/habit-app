<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DailyReportController extends Controller
{
    /** Reports top: today's form + recent list */

    public function show()
    {
        // Show only my posts (newest first), with pagination
        $posts = DailyReport::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->paginate(10);
        return view('reports.show', compact('posts'));
    }
    public function index()
    {
        $today = Carbon::today('Asia/Tokyo')->toDateString();

        $myToday = DailyReport::where('user_id', Auth::id())
                    ->where('date', $today)
                    ->first();

        $recent = DailyReport::where('user_id', Auth::id())
                    ->orderByDesc('date')
                    ->limit(7)
                    ->get();

        return view('reports.index', compact('myToday', 'recent'));
    }

    /** Create today's report (1 per day) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'content' => ['required','string','max:2000'],
            'mood'    => ['nullable','integer','between:1,5'],
            'effort'  => ['nullable','integer','between:1,5'],
        ], [
            'content.required' => 'Please enter the report content',
        ]);

        $today = Carbon::today('Asia/Tokyo')->toDateString();

        try {
            DailyReport::create([
                'user_id' => Auth::id(),
                'date'    => $today,
                'mood'    => $data['mood'] ?? null,
                'effort'  => $data['effort'] ?? null,
                'content' => trim($data['content']),
            ]);
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return back()->withErrors(['content' => 'You have already submitted today\'s report'])->withInput();
            }
            throw $e;
        }

        return back()->with('status', 'Saved today\'s report');
    }

    /** Edit form */
    public function edit(DailyReport $report)
    {
        abort_if($report->user_id !== Auth::id(), 403);
        return view('reports.edit', compact('report'));
    }

    /** Update */
    public function update(Request $request, DailyReport $report)
    {
        abort_if($report->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'content' => ['required','string','max:2000'],
            'mood'    => ['nullable','integer','between:1,5'],
            'effort'  => ['nullable','integer','between:1,5'],
        ]);

        $report->update([
            'content' => trim($data['content']),
            'mood'    => $data['mood'] ?? null,
            'effort'  => $data['effort'] ?? null,
        ]);

        return redirect()->route('reports.index')->with('status', 'Report updated');
    }
}
