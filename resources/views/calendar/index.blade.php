<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendar</h2>
  </x-slot>

  <div class="py-6 w-full px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl border p-4">
      <div class="flex items-center justify-between mb-3">
        <h3 class="font-semibold">Calendar</h3>
        <div x-data="timelogCtl()" x-init="init()" class="flex items-center gap-3">
          <div class="text-lg tabular-nums w-28 text-right" x-text="timeStr()">00:00:00</div>
          <button type="button" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50" @click="start" :disabled="running">Start</button>
          <button type="button" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50" @click="stop" :disabled="!running">Stop</button>
          <span class="text-xs text-gray-500" x-text="message"></span>
        </div>
      </div>
      <div id="fc-calendar"></div>
    </div>
  </div>
</x-app-layout>

<!-- FullCalendar via CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
  function timelogCtl(){
    return {
      running: false,
      seconds: 0,
      timer: null,
      message: '',
      init(){
        this.timer = (window.easytimer && new easytimer.Timer()) || { start:()=>{}, stop:()=>{}, reset:()=>{}, addEventListener:()=>{}, getTotalTimeValues:()=>({seconds:this.seconds}) };
        this.timer.addEventListener('secondsUpdated', () => {
          this.seconds = this.timer.getTotalTimeValues().seconds;
        });
        // Resume if a server-side running log exists
        fetch(@json(route('timelog.events')) + '?running=1').then(r => r.ok ? r.json() : []).then(arr => {
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
            body: JSON.stringify({})
          });
          if (!res.ok) {
            const err = await res.json().catch(()=>({message:'Failed'}));
            this.message = err.message || 'Failed to start';
            return;
          }
          this.seconds = 0; this.timer.start({ startValues: { seconds: 0 } }); this.running = true; this.message = 'Started';
          window.__fcInstance && window.__fcInstance.refetchEvents();
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
            body: JSON.stringify({ seconds: this.seconds, hm: this.timeStrHM() })
          });
          if (!res.ok) {
            const err = await res.json().catch(()=>({message:'Failed'}));
            this.message = err.message || 'Failed to stop';
            return;
          }
          this.timer.stop(); this.running = false; this.message = 'Saved';
          window.__fcInstance && window.__fcInstance.refetchEvents();
        }catch(e){ this.message = 'Failed to stop'; }
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('fc-calendar');
    if (!el || typeof FullCalendar === 'undefined') return;

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
      height: 'auto',
      nowIndicator: true,
      navLinks: true,
      headerToolbar: {
        left: 'prev3,prev todayR next,next3',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridThreeDay,timeGridDay'
      },
      slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
      views: {
        timeGridThreeDay: {
          type: 'timeGrid',
          duration: { days: 3 },
          buttonText: '3 days'
        }
      },
      customButtons: {
        prev3: { text: '-3d', click: function(){ calendar.incrementDate({ days: -3 }); } },
        next3: { text: '+3d', click: function(){ calendar.incrementDate({ days: 3 }); } },
        todayR: { text: 'today', click: function(){ calendar.gotoDate(startForRightAligned(new Date())); } },
      },
      eventSources: [
        { url: @json(route('checkins.events')), id: 'checkins' },
        { url: @json(route('timelog.events')),  id: 'timelogs', color: '#0ea5e9' },
      ]
    });
    calendar.render();
    window.__fcInstance = calendar;
  });
</script>
