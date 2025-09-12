<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class TimeLogController extends Controller
{
    // GET /timelogs (list)
    public function index(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) abort(401);

        // If table not ready, return empty paginator to satisfy the view
        if (!Schema::hasTable('time_logs')) {
            $empty = collect();
            $logs = new LengthAwarePaginator($empty, 0, 50, 1, [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]);
            return view('timelog.index', compact('logs'));
        }

        $query = TimeLog::where('user_id', $userId);

        $from = $request->query('from');
        $to   = $request->query('to');
        try {
            if ($from) {
                $fromDt = Carbon::parse($from, 'Asia/Tokyo')->startOfDay();
                $query->where('started_at', '>=', $fromDt);
            }
            if ($to) {
                $toDt = Carbon::parse($to, 'Asia/Tokyo')->endOfDay();
                $query->where('started_at', '<=', $toDt);
            }
        } catch (\Exception $e) {
            // ignore invalid inputs
        }

        if (!$from && !$to) {
            // default to last 7 days
            $query->where('started_at', '>=', Carbon::now('Asia/Tokyo')->subDays(7));
        }

        $logs = $query->orderByDesc('started_at')->paginate(50)->withQueryString();
        return view('timelog.index', compact('logs'));
    }

    // POST /timelog/start
    public function start(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) abort(401);

        // Prevent multiple running logs
        $running = TimeLog::where('user_id', $userId)->whereNull('ended_at')->first();
        if ($running) {
            return response()->json([
                'ok' => false,
                'message' => 'A time log is already running',
                'log' => $this->serialize($running)
            ], 422);
        }

        $now = Carbon::now('Asia/Tokyo');
        $log = TimeLog::create([
            'user_id'      => $userId,
            'checkin_id'   => $request->integer('checkin_id') ?: null,
            'genre'        => $request->input('genre'),
            'title'        => $request->input('title'),
            'started_at'   => $now,
            'ended_at'     => null,
            'duration_hm'  => '00:00',
        ]);

        return response()->json(['ok' => true, 'log' => $this->serialize($log)]);
    }

    // POST /timelog/stop
    public function stop(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) abort(401);

        $running = TimeLog::where('user_id', $userId)->whereNull('ended_at')->first();
        if (!$running) {
            return response()->json(['ok' => false, 'message' => 'No running time log'], 422);
        }

        $now = Carbon::now('Asia/Tokyo');
        // Prefer HH:MM string from client when provided, otherwise accept seconds, else server diff
        $sec = null;
        $hm = $request->input('hm');
        if (is_string($hm) && preg_match('/^(\d{1,2}):(\d{2})$/', $hm, $m)) {
            $h = (int) $m[1]; $mm = (int) $m[2];
            $sec = max(0, $h * 3600 + $mm * 60);
        }
        if ($sec === null) {
            $reqSec = $request->integer('seconds');
            if (is_int($reqSec) && $reqSec > 0) {
                $sec = $reqSec;
            } else {
                $sec = max(0, $now->diffInSeconds($running->started_at));
            }
        }

        $endedAt = (clone $running->started_at)->addSeconds($sec);
        $hmStr = sprintf('%02d:%02d', intdiv($sec, 3600), intdiv(($sec % 3600), 60));
        $update = [ 'ended_at' => $endedAt, 'duration_hm' => $hmStr ];
        $titleIn = $request->input('title');
        if (is_string($titleIn) && strlen($titleIn)) { $update['title'] = $titleIn; }
        $genreIn = $request->input('genre');
        if (is_string($genreIn) && strlen($genreIn)) { $update['genre'] = $genreIn; }
        $running->update($update);

        return response()->json(['ok' => true, 'log' => $this->serialize($running->fresh())]);
    }

    // GET /timelog/events  (optionally filters by start/end; or running=1 for current running only)
    public function events(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) abort(401);

        $query = TimeLog::where('user_id', $userId);

        if ($request->boolean('running')) {
            $query->whereNull('ended_at');
        } else {
            $start = $request->query('start');
            $end   = $request->query('end');
            if ($start && $end) {
                try {
                    $startDt = Carbon::parse($start, 'Asia/Tokyo');
                    $endDt   = Carbon::parse($end, 'Asia/Tokyo');
                    // Overlap condition: started before end and (ended after start or running)
                    $query->where(function($q) use ($startDt, $endDt) {
                        $q->where('started_at', '<', $endDt)
                          ->where(function($qq) use ($startDt) {
                              $qq->where('ended_at', '>=', $startDt)
                                 ->orWhereNull('ended_at');
                          });
                    });
                } catch (\Exception $e) {
                    // ignore invalid filter
                }
            }
        }

        $logs = $query->orderBy('started_at')->get();

        $events = $logs->map(function (TimeLog $t) {
            // Show genre instead of title in the calendar label
            $title = $t->title ? $t->title : ($t->genre ? $t->genre : 'Time Log');
            if ($t->duration_hm) { $title .= ' ' . $t->duration_hm; }
            $ev = [
                'id'     => $t->id,
                'title'  => $title,
                'start'  => $t->started_at?->toIso8601String(),
                'end'    => $t->ended_at?->toIso8601String(),
                'allDay' => false,
                'color'  => '#0ea5e9', // sky-500
                'extendedProps' => [
                    'duration_hm' => $t->duration_hm ?? null,
                    'genre'       => $t->genre,
                    'title'       => $t->title,
                    'checkin_id'   => $t->checkin_id,
                ],
            ];
            return $ev;
        });

        return response()->json($events);
    }

    private function serialize(TimeLog $t): array
    {
        $hm = $t->duration_hm ?? null;
        if (!$hm) {
            $m = (int) ($t->duration_sec ?? 0);
            $hm = sprintf('%02d:%02d', intdiv($m, 3600), intdiv($m % 3600, 60));
        }
        return [
            'id'           => $t->id,
            'checkin_id'   => $t->checkin_id,
            'genre'        => $t->genre,
            'title'        => $t->title,
            'started_at'   => optional($t->started_at)->toIso8601String(),
            'ended_at'     => optional($t->ended_at)->toIso8601String(),
            'duration_hm'  => $hm,
        ];
    }
}
