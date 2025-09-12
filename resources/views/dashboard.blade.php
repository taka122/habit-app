<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tasks</h2>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded bg-emerald-50 text-emerald-700 text-sm">{{ session('status') }}</div>
        @endif

        <!-- Time Log (moved from /calendar) -->
        <div class="bg-white rounded-xl border p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Time Log</h3>
            </div>
            <div x-data="dashTimelogCtl()" x-init="init()" class="mt-3 flex items-center gap-3 flex-wrap">
                <div class="text-2xl tabular-nums w-28 text-right" x-text="timeStr()">00:00:00</div>
                <select x-model="currentTitle" class="px-2 py-1 text-xs rounded border border-gray-300 bg-white">
                    <option value=""> title </option>
                    <template x-for="t in titleOptions" :key="t">
                        <option :value="t" x-text="t"></option>
                    </template>
                </select>
                <select x-model="genre" class="px-2 py-1 text-xs rounded border border-gray-300 bg-white">
                    <option value=""> genre </option>
                    <template x-for="g in options" :key="g">
                        <option :value="g" x-text="g"></option>
                    </template>
                </select>
                
                <button type="button" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50" @click="start" :disabled="running">Start</button>
                <button type="button" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50" @click="stop" :disabled="!running">Stop</button>
                <span class="text-xs text-gray-500" x-text="message"></span>

                <a href="{{ route('timelog.index') }}"
   class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50">
  ALl logs
</a>

                
            </div>
        </div>

    

<div class="bg-white rounded-xl border p-4">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold">List</h3>
        <a href="{{ route('plans.create') }}" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50">Add</a>
    </div>

    <ul class="divide-y mt-3">
        @forelse ($planned as $p)
            <li class="py-2 flex items-center justify-between">
                <div>
                    <div class="font-medium">
                        {{ $p->title }}
                        @if($p->genre)
                          <span class="ml-2 align-middle text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 ring-1 ring-gray-200">{{ $p->genre }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        @if ($p->start_at && $p->end_at)
                            {{ $p->start_at->format('H:i') }} - {{ $p->end_at->format('H:i') }}
                        @elseif ($p->start_at)
                            {{ $p->start_at->format('H:i') }}
                        @elseif ($p->end_at)
                            {{ $p->end_at->format('H:i') }}
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Timer (client-side, per item) -->
                    
                       
                    

                    <!-- Actions menu -->
                    <div x-data="{ open:false }" class="relative">
                        <button type="button" @click="open=!open" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50" aria-label="Actions">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-6 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition
                             class="absolute right-0 mt-2 w-50 bg-white border rounded shadow-md z-10">
                            <a href="{{ route('checkins.edit', $p) }}" class="block px-3 py-2 text-xs hover:bg-gray-50">edit</a>
                            <form method="POST" action="{{ route('checkins.done', $p) }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50">Done</button>
                            </form>
                            <a href="{{ route('checkins.skip.form', $p) }}" class="block px-3 py-2 text-xs text-red-600 hover:bg-gray-50">Skip</a>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="py-2 text-sm text-gray-500">No plans for today</li>
        @endforelse
    </ul>
</div>
        <div class="bg-white rounded-xl border p-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Today's Results</h3>
            </div>
            <ul class="divide-y mt-3">
                @forelse ($done as $d)
                    <li class="py-2 flex items-center justify-between">
                        <div>
                            <div class="font-medium">
                                {{ $d->title }}
                                @if($d->genre)
                                  <span class="ml-2 align-middle text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 ring-1 ring-gray-200">{{ $d->genre }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">Done</span>
                        </div>
                    </li>
                @empty @endforelse

                @forelse ($skipped as $s)
                    <li class="py-2 flex items-center justify-between">
                        <div>
                            <div class="font-medium">
                                {{ $s->title }}
                                @if($s->genre)
                                  <span class="ml-2 align-middle text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 ring-1 ring-gray-200">{{ $s->genre }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Reason: {{ $s->reason ?? '—' }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded-full bg-rose-100 text-red-600 text-rose-700 ring-1 ring-rose-200">Skipped</span>
                        </div>
                    </li>
                @empty
                    @if ($done->isEmpty())
                        <li class="py-2 text-sm text-gray-500">No results yet</li>
                    @endif
                @endforelse
            </ul>
        </div>

        <!-- カレンダー（FullCalendar 試験導入：UI） -->
        <div class="bg-white rounded-xl border p-4">
            <div class="mt-1" style="height: 850px;">
                <div id="fc-calendar" class="h-full"></div>
            </div>
        </div>


</x-app-layout>

<!-- FullCalendar via CDN（必要に応じてVite統合へ） -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
  // Dashboard Time Log control (EasyTimer + TimeLog API)
  function dashTimelogCtl(){
    return {
      running: false,
      seconds: 0,
      timer: null,
      currentTitle: '',
      titleOptions: [],
      genre: '',
      options: [],
      message: '',
      init(){
        this.timer = (window.easytimer && new easytimer.Timer()) || { start:()=>{}, stop:()=>{}, reset:()=>{}, addEventListener:()=>{}, getTotalTimeValues:()=>({seconds:this.seconds}) };
        this.timer.addEventListener('secondsUpdated', () => {
          this.seconds = this.timer.getTotalTimeValues().seconds;
        });
        // Build genre options from today's data
        try{
          this.options = @json(collect([$planned->pluck('genre'), $done->pluck('genre'), $skipped->pluck('genre')])->flatten()->filter()->unique()->values());
        }catch(e){ this.options = []; }
        // Build title options from today's data
        try{
          this.titleOptions = @json(collect([$planned->pluck('title'), $done->pluck('title'), $skipped->pluck('title')])->flatten()->filter()->unique()->values());
        }catch(e){ this.titleOptions = []; }
        // Resume if a server-side running log exists
        fetch(@json(route('timelog.events')) + '?running=1')
          .then(r => r.ok ? r.json() : [])
          .then(arr => {
            if (Array.isArray(arr) && arr.length > 0) {
              const ev = arr[0];
              const started = ev.start ? new Date(ev.start) : null;
              if (started) {
                const now = new Date();
                this.seconds = Math.max(0, Math.floor((now - started)/1000));
                this.timer.start({ startValues: { seconds: this.seconds } });
                this.running = true;
                this.message = 'Resumed running log';
              }
            }
          }).catch(()=>{});

        // Listen to item-level timer events to control this Time Log
        window.addEventListener('timelog:start', async (e) => {
          try{
            const { checkin_id, title, genre } = e.detail || {};
            this.currentTitle = title || '';
            if (genre) this.genre = genre;
            const g = genre || this.genre || null;
            // Start server log
            const res = await fetch(@json(route('timelog.start')), {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ checkin_id, title, genre: g })
            });
            // Start local timer regardless of server response to maintain UX
            this.seconds = 0; this.timer.start({ startValues: { seconds: 0 } }); this.running = true; this.message = 'Started';
          }catch(err){ this.message = 'Failed to start'; }
        });

        window.addEventListener('timelog:stop', async (e) => {
          try{
            const { seconds, hm, title, genre } = e.detail || {};
            // Stop server log with hm, title, and genre
            await fetch(@json(route('timelog.stop')), {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ seconds, hm, title, genre })
            });
            this.timer.stop(); this.running = false; this.message = 'Saved';
          }catch(err){ this.message = 'Failed to stop'; }
        });
      },
      timeStr(){ const t=this.seconds||0; const h=String(Math.floor(t/3600)).padStart(2,'0'); const m=String(Math.floor((t%3600)/60)).padStart(2,'0'); const s=String(t%60).padStart(2,'0'); return h+':'+m+':'+s; },
      timeStrHM(){ const t=this.seconds||0; const h=String(Math.floor(t/3600)).padStart(2,'0'); const m=String(Math.floor((t%3600)/60)).padStart(2,'0'); return h+':'+m; },
      async start(){
        if (this.running) return;
        this.message = '';
        try{
          const res = await fetch(@json(route('timelog.start')), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ genre: this.genre || null, title: this.currentTitle || null })
          });
          if (!res.ok) {
            const err = await res.json().catch(()=>({message:'Failed'}));
            this.message = err.message || 'Failed to start';
            return;
          }
          this.seconds = 0; this.timer.start({ startValues: { seconds: 0 } }); this.running = true; this.message = 'Started';
        }catch(e){ this.message = 'Failed to start'; }
      },
      async stop(){
        if (!this.running) return;
        this.message = '';
        try{
          const res = await fetch(@json(route('timelog.stop')), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ seconds: this.seconds, hm: this.timeStrHM(), title: this.currentTitle || null, genre: this.genre || null })
          });
          if (!res.ok) {
            const err = await res.json().catch(()=>({message:'Failed'}));
            this.message = err.message || 'Failed to stop';
            return;
          }
          this.timer.stop(); this.running = false; this.message = 'Saved';
        }catch(e){ this.message = 'Failed to stop'; }
      }
    }
  }
</script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('fc-calendar');
    if (!el || typeof FullCalendar === 'undefined') return;

    // Show a 3-day range with "today" as the rightmost column
    function startForRightAligned(date){
      const d = new Date(date);
      d.setHours(0,0,0,0);
      d.setDate(d.getDate() - 2);
      return d;
    }

    const calendar = new FullCalendar.Calendar(el, {
      initialView: 'timeGridThreeDay',
      initialDate: startForRightAligned(new Date()),
      firstDay: 1,
      locale: 'en',
      height: 850, // show roughly 06:00–24:00 without feeling cramped
      scrollTime: '08:00:00',
      slotMinTime: '00:00:00',
      slotMaxTime: '24:00:00',
      nowIndicator: true,
      navLinks: true,
      headerToolbar: {
        left: 'prev3,prev todayR next,next3',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridThreeDay,timeGridDay'
      },
      // 時間軸のラベルを 00:00 形式に
      slotLabelFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      },
      views: {
        timeGridThreeDay: {
          type: 'timeGrid',
          duration: { days: 3 },
          buttonText: '3 days'
        }
      },
      customButtons: {
        prev3: { text: '-3d', click: () => calendar.incrementDate({ days: -3 }) },
        next3: { text: '+3d', click: () => calendar.incrementDate({ days: 3 }) },
        todayR: { text: 'today', click: () => calendar.gotoDate(startForRightAligned(new Date())) },
      },

      // 全データをサーバーから取得（期間指定にも対応）
     eventSources: [
        { url: @json(route('checkins.events')), id: 'checkins', textColor: '#000000' },
        { url: @json(route('timelog.events')),  id: 'timelogs', color: '#0ea5e9' },
      ]
    });
    calendar.render();
    window.__fcInstance = calendar;
  });
</script>
