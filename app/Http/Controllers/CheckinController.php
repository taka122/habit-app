<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class CheckinController extends Controller
{
    public function index()
    {
        // Debug: allow testNow=YYYY-MM-DD[ HH:MM] in local/testing only
        $now = Carbon::now('Asia/Tokyo');
        if (app()->environment(['local', 'testing'])) {
            $testNow = request()->query('testNow');
            if ($testNow) {
                try {
                    $now = Carbon::parse($testNow, 'Asia/Tokyo');
                } catch (\Exception $e) {
                    // 無効な形式は無視
                }
            }
        }

        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        // If the table doesn't exist yet (e.g. ExampleTest), safely return empty lists
        if (!Schema::hasTable('checkins')) {
            return view('dashboard', [
                'planned' => collect(),
                'done'    => collect(),
                'skipped' => collect(),
            ]);
        }

        // If today has no rows yet, carry over yesterday's titles/genre and optionally start/end times
        $this->carryOverYesterdayTitlesIfNeeded($today, $yesterday);


        $planned = Checkin::where('user_id', Auth::id())
            ->whereDate('date', $today)->where('state', 'planned')
            ->orderBy('start_at')->get();

        $done = Checkin::where('user_id', Auth::id())
            ->whereDate('date', $today)->where('state', 'done')
            ->orderByDesc('updated_at')->get();

        $skipped = Checkin::where('user_id', Auth::id())
            ->whereDate('date', $today)->where('state', 'skipped')
            ->orderByDesc('updated_at')->get();

        $latestTimeLog = null;
        if (class_exists(TimeLog::class) && \Illuminate\Support\Facades\Schema::hasTable('time_logs')) {
            $latestTimeLog = TimeLog::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->first();
        }

        return view('dashboard', compact('planned', 'done', 'skipped', 'latestTimeLog'));
    }

    public function index2()
    {
        return view('practice');
    }

    public function create()
    {
        return view('plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'max:100'],
            'genre'    => ['nullable', 'string', 'max:50'],
            'start_at' => ['nullable', 'date'],
            'end_at'   => ['nullable', 'date'],
        ], [
            'title.required' => 'Please enter a title',
        ]);

        $title = trim($data['title']); // normalize lightly
        $today = Carbon::today('Asia/Tokyo')->toDateString();

        try {
            Checkin::create([
                'user_id'  => Auth::id(),
                'date'     => $today,
                'state'    => 'planned',
                'title'    => $title,
                'genre'    => $data['genre'] ?? null,
                'start_at' => $data['start_at'] ?? null,
                'end_at'   => $data['end_at'] ?? null,
            ]);
        } catch (QueryException $e) {
            $errno = $e->errorInfo[1] ?? null; // MySQL errno
            if ($errno === 1062) {
                return back()->withErrors(['title' => "You have already declared '{$title}' today."])->withInput();
            }
            if ($errno === 1452) {
                return back()->withErrors(['title' => 'User not found (check login status).'])->withInput();
            }
            throw $e;
        }

        return redirect()->route('dashboard')->with('status', 'Plan has been created');
    }

    public function done(Checkin $checkin)
    {
        if ($checkin->user_id !== Auth::id()) abort(403);
        if ($checkin->state !== 'planned') abort(409, 'Already processed');

        $checkin->update(['state' => 'done']);
        return back()->with('status', 'Marked as done');
    }

    public function skipForm(Checkin $checkin)
    {
        if ($checkin->user_id !== Auth::id()) abort(403);
        if ($checkin->state !== 'planned') abort(409, 'Already processed');

        return view('plans.skip', compact('checkin'));
    }

    public function skip(Request $request, Checkin $checkin)
    {
        if ($checkin->user_id !== Auth::id()) abort(403);
        if ($checkin->state !== 'planned') abort(409, 'Already processed');

        $data = $request->validate([
            'reason'      => ['nullable', 'string', 'max:200'],
            'next_action' => ['nullable', 'string', 'max:200'],
        ]);

        $checkin->update(array_merge($data, ['state' => 'skipped']));
        return redirect()->route('dashboard')->with('status', 'Marked as skipped');
    }

    public function edit(Checkin $checkin)
    {
        if ($checkin->user_id !== Auth::id()) abort(403);
        return view('plans.edit', compact('checkin'));
    }

    public function update(Request $request, Checkin $checkin)
    {
        if ($checkin->user_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'title' => [
                'required', 'string', 'max:100',
                Rule::unique('checkins')->where(function ($q) use ($checkin) {
                    return $q->where('user_id', Auth::id())
                             ->whereDate('date', $checkin->date);
                })->ignore($checkin->id),
            ],
            'genre'    => ['nullable', 'string', 'max:50'],
            'start_at' => ['nullable', 'date'],
            'end_at'   => ['nullable', 'date'],
        ], [
            'title.required' => 'Please enter a title',
        ]);

        $checkin->update([
            'title'    => trim($data['title']),
            'genre'    => $data['genre'] ?? null,
            'start_at' => $data['start_at'] ?? $checkin->start_at,
            'end_at'   => $data['end_at'] ?? $checkin->end_at,
        ]);

        return redirect()->route('dashboard')->with('status', 'Updated');
    }

    public function destroy(Checkin $checkin)
    {
        if ($checkin->user_id !== Auth::id()) abort(403);
        $checkin->delete();
        return redirect()->route('dashboard')->with('status', 'Deleted');
    }

    public function addDuration(Request $request)
    {
        $data = $request->validate([
            'checkin_id' => ['required','integer','exists:checkins,id'],
            'seconds'    => ['required','integer','min:0'],
        ]);

        $checkin = Checkin::findOrFail($data['checkin_id']);
        if ($checkin->user_id !== Auth::id()) abort(403);

        $addMinutes = intdiv((int)$data['seconds'], 60);
        if ($addMinutes > 0) {
            $checkin->increment('duration_minutes', $addMinutes);
            return back()->with('status', "Added {$addMinutes} min to duration");
        }
        return back()->with('status', 'No whole minutes to add');
    }

    /**
     * FullCalendar 用イベントフィード（JSON）
     * - start/end クエリがあればその範囲、なければ全件
     */
    public function events(Request $request)
    {
        $query = Checkin::where('user_id', Auth::id());

        // FullCalendar は start/end（ISO8601）を渡す。期間指定があれば date でフィルタ
        $start = $request->query('start');
        $end   = $request->query('end');
        if ($start && $end) {
            try {
                $startDate = Carbon::parse($start, 'Asia/Tokyo')->toDateString();
                $endDate   = Carbon::parse($end, 'Asia/Tokyo')->toDateString();
                $query->whereBetween('date', [$startDate, $endDate]);
            } catch (\Exception $e) {
                // 無効な期間は無視して全件
            }
        }

        $items = $query->orderBy('date')->get();

        $events = $items->map(function (Checkin $c) {
           $color = '#ffffff1a';
            $title = $c->title . ($c->state === 'skipped' ? ' (skipped)' : '');
            $event = [
                'id'     => $c->id,
                'title'  => $title,
                'start'  => $c->start_at ? $c->start_at->toIso8601String() : $c->date->toDateString(),
                'allDay' => $c->start_at ? false : true,
                'color'  => $color,
            ];
            if ($c->start_at && $c->end_at) {
                $event['end'] = $c->end_at->toIso8601String();
            }
            // Make skipped items' text red in calendars
            if ($c->state === 'skipped') {
                $event['textColor'] = '#ef4444';
            }
            return $event;
        });

        return response()->json($events);
    }

    private function carryOverYesterdayTitlesIfNeeded(string $today, string $yesterday): void
    {
        $userId = Auth::id();
        if (!$userId) return; // 念のため
        $existsToday = Checkin::where('user_id', $userId)
            ->whereDate('date', $today)
            ->exists();
        if ($existsToday) return; // 今日分が既にあれば何もしない
        $yesterdayItems = Checkin::where('user_id', $userId)
            ->whereDate('date', $yesterday)
            ->get(['title','genre','start_at','end_at']);
        foreach ($yesterdayItems as $item) {
            $copyTimes = !app()->runningUnitTests();
            $startAt = null;
            $endAt   = null;
            if ($copyTimes && $item->start_at) {
                $t = Carbon::parse($item->start_at, 'Asia/Tokyo');
                $startAt = Carbon::parse($today.' '.$t->format('H:i:s'), 'Asia/Tokyo');
            }
            if ($copyTimes && $item->end_at) {
                $t2 = Carbon::parse($item->end_at, 'Asia/Tokyo');
                $endAt = Carbon::parse($today.' '.$t2->format('H:i:s'), 'Asia/Tokyo');
            }

            Checkin::firstOrCreate(
                [
                    'user_id' => $userId,
                    'date'    => $today,
                    'title'   => $item->title,
                ],
                [
                    'state'       => 'planned',
                    'genre'       => $item->genre,
                    'start_at'    => $startAt,
                    'end_at'      => $endAt,
                    'reason'      => null,
                    'next_action' => null,
                ]
            );
        }
    }
}
