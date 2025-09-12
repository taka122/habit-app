<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinCarryOverTest extends TestCase
{
    use RefreshDatabase;

    public function test_carries_over_titles_from_yesterday_when_today_empty(): void
    {
        $user = User::factory()->create();

        // 時刻を固定（Asia/Tokyo で 2025-09-05 として扱う）
        Carbon::setTestNow(Carbon::parse('2025-09-05 09:00:00', 'Asia/Tokyo'));

        $yesterday = Carbon::yesterday('Asia/Tokyo')->toDateString(); // 2025-09-04
        $today     = Carbon::today('Asia/Tokyo')->toDateString();     // 2025-09-05

        // 昨日のデータ（タイトル以外の項目はコピーされない想定）
        Checkin::create(['user_id' => $user->id, 'date' => $yesterday, 'state' => 'done',    'title' => '朝ラン', 'start_at' => now(), 'reason' => '眠い', 'next_action' => '早寝']);
        Checkin::create(['user_id' => $user->id, 'date' => $yesterday, 'state' => 'skipped', 'title' => '英単語', 'start_at' => null,  'reason' => null,  'next_action' => null]);

        // 今日のデータは空
        $this->assertDatabaseCount('checkins', 2);

        $this->actingAs($user)->get('/dashboard')->assertOk();

        // 今日に2件が planned で生成され、付随情報は null
        $am = Checkin::where('user_id', $user->id)->whereDate('date', $today)->where('title', '朝ラン')->first();
        $en = Checkin::where('user_id', $user->id)->whereDate('date', $today)->where('title', '英単語')->first();
        $this->assertNotNull($am);
        $this->assertNotNull($en);
        $this->assertSame('planned', $am->state);
        $this->assertSame('planned', $en->state);
        $this->assertNull($am->start_at);
        $this->assertNull($en->start_at);
        $this->assertNull($am->reason);
        $this->assertNull($en->reason);
        $this->assertNull($am->next_action);
        $this->assertNull($en->next_action);
    }

    public function test_does_not_carry_over_when_today_already_has_any_record(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::parse('2025-09-05 09:00:00', 'Asia/Tokyo'));

        $yesterday = Carbon::yesterday('Asia/Tokyo')->toDateString();
        $today     = Carbon::today('Asia/Tokyo')->toDateString();

        Checkin::create(['user_id' => $user->id, 'date' => $yesterday, 'state' => 'done', 'title' => '朝ラン']);

        // 今日に既に1件でもある場合は、クローン処理はスキップされる仕様
        Checkin::create(['user_id' => $user->id, 'date' => $today, 'state' => 'planned', 'title' => '既存']);

        $this->actingAs($user)->get('/dashboard')->assertOk();

        // 昨日の「朝ラン」は今日に複製されない
        $exists = Checkin::where('user_id', $user->id)->whereDate('date', $today)->where('title', '朝ラン')->exists();
        $this->assertFalse($exists);
    }

    public function test_idempotent_when_accessing_dashboard_multiple_times(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::parse('2025-09-05 09:00:00', 'Asia/Tokyo'));

        $yesterday = Carbon::yesterday('Asia/Tokyo')->toDateString();
        $today     = Carbon::today('Asia/Tokyo')->toDateString();

        Checkin::create(['user_id' => $user->id, 'date' => $yesterday, 'state' => 'done', 'title' => '朝ラン']);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $exists = Checkin::where('user_id', $user->id)->whereDate('date', $today)
            ->where('title', '朝ラン')->where('state', 'planned')->exists();
        $this->assertTrue($exists);

        // 2回目アクセスでも増えない
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->assertSame(2, Checkin::count()); // 昨日1件 + 今日1件 のまま
    }

    public function test_testNow_query_param_overrides_date_in_local_env(): void
    {
        $user = User::factory()->create();

        // テストでは APP_ENV=testing だが、ローカル扱いに切替
        config(['app.env' => 'local']);

        $fakeToday     = '2025-09-05';
        $fakeYesterday = '2025-09-04';

        Checkin::create(['user_id' => $user->id, 'date' => $fakeYesterday, 'state' => 'done', 'title' => '朝ラン']);

        $this->actingAs($user)->get('/dashboard?testNow=' . $fakeToday)->assertOk();

        $exists = Checkin::where('user_id', $user->id)->whereDate('date', $fakeToday)
            ->where('title', '朝ラン')->where('state', 'planned')->exists();
        $this->assertTrue($exists);
    }
}
