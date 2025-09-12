<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index()
    {
        $uid   = Auth::id();
        $today = Carbon::today('Asia/Tokyo')->toDateString();
        $from  = Carbon::today('Asia/Tokyo')->subDays(29)->toDateString();

        $rows = DB::table('checkins')
            ->selectRaw("
                `date`,
                COUNT(*) AS total,
                SUM(CASE WHEN state='planned' THEN 1 ELSE 0 END) AS planned,
                SUM(CASE WHEN state='done'    THEN 1 ELSE 0 END) AS done,
                SUM(CASE WHEN state='skipped' THEN 1 ELSE 0 END) AS skipped
            ")
            ->where('user_id', $uid)
            ->whereBetween('date', [$from, $today])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $history = $rows->map(function ($r) {
            $den = ($r->done + $r->skipped);
            $r->score = $den > 0 ? round($r->done * 100 / $den) : null; // done / (done+skipped) * 100
            return $r;
        });

        // 直近7/30日の平均スコア
        $avg = function ($take) use ($history) {
            $slice = $history->take($take);
            if ($slice->isEmpty()) return null;
            $vals = $slice->pluck('score')->filter(fn($v)=>$v!==null);
            return $vals->isNotEmpty() ? round($vals->avg()) : null;
        };

        $score7  = $avg(7);
        $score30 = $avg(30);

        return view('history.index', compact('history','score7','score30'));
    }
}
