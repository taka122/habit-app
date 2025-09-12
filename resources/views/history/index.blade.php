<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">History</h2>
  </x-slot>

  <div class="py-8 w-full px-4 sm:px-6 lg:px-8 space-y-8">
    <div class="bg-white rounded-xl border p-6">
      <h3 class="font-semibold mb-4">Summary (non-skip rate)</h3>
      <div class="flex flex-wrap gap-6 text-sm leading-relaxed">
        @php
          $cls7 = $score7 === null ? 'text-gray-400' : ($score7 < 50 ? 'text-rose-600 font-semibold' : ($score7 < 80 ? 'text-amber-600 font-medium' : 'text-emerald-700 font-semibold'));
          $cls30 = $score30 === null ? 'text-gray-400' : ($score30 < 50 ? 'text-rose-600 font-semibold' : ($score30 < 80 ? 'text-amber-600 font-medium' : 'text-emerald-700 font-semibold'));
        @endphp
        <div>Last 7 days avg: <span class="{{ $cls7 }}">{{ $score7 !== null ? $score7.'%' : '—' }}</span></div>
        <div>Last 30 days avg: <span class="{{ $cls30 }}">{{ $score30 !== null ? $score30.'%' : '—' }}</span></div>
      </div>
    </div>

    <!-- Trend (Chart.js) -->
    <div class="bg-white rounded-xl border p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 id="trendTitle" class="font-semibold">Trend (last 7 days)</h3>
        <div class="flex items-center gap-2">
          <button id="btn7" type="button" class="px-2 py-1 text-xs rounded border border-gray-300 bg-gray-100">7d</button>
          <button id="btn30" type="button" class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50">30d</button>
        </div>
      </div>
      <div class="relative" style="height: 200px;">
        <canvas id="scoreChart"></canvas>
      </div>
    </div>

    <div class="bg-white rounded-xl border p-6">
      <h3 class="font-semibold mb-4">Last 7 days</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500">
              <th class="py-3 pr-6">Date</th>
              <th class="py-3 pr-6">planned</th>
              <th class="py-3 pr-6">done</th>
              <th class="py-3 pr-6">skipped</th>
              <th class="py-3 pr-10">Non-skip rate</th>
              <th class="py-3">Progress</th>
            </tr>
          </thead>
          <tbody class="divide-y">
          @forelse ($history->take(7) as $r)
            <tr class="align-middle">
              <td class="py-3 pr-6 leading-relaxed">{{ \Carbon\Carbon::parse($r->date)->format('Y-m-d') }}</td>
              <td class="py-3 pr-6 leading-relaxed">{{ $r->planned }}</td>
              <td class="py-3 pr-6 leading-relaxed">{{ $r->done }}</td>
              <td class="py-3 pr-6 leading-relaxed">{{ $r->skipped }}</td>
              <td class="py-3 pr-10 leading-relaxed">
                @php
                  $scoreVal = $r->score;
                  $rateClass = $scoreVal === null
                    ? 'text-gray-400'
                    : ($scoreVal < 50
                        ? 'text-rose-600 font-semibold'
                        : ($scoreVal < 80
                            ? 'text-amber-600 font-medium'
                            : 'text-emerald-700 font-semibold'));
                @endphp
                <span class="{{ $rateClass }}">{{ $scoreVal !== null ? $scoreVal.'%' : '—' }}</span>
              </td>
              <td class="py-3 leading-relaxed">
                @php
                  $scoreP = $r->score ?? 0; // 0..100 or null treated as 0
                  $barColor = $scoreP >= 80 ? '#10b981' : ($scoreP >= 50 ? '#f59e0b' : '#ef4444');
                @endphp
                <div class="h-2.5 w-60 bg-gray-100 rounded-full ring-1 ring-gray-200 overflow-hidden">
                  <div class="h-2.5 rounded-full" style="width: {{ (int) $scoreP }}%; min-width: 4px; background: {{ $barColor }}"></div>
                </div>
              </td>
            </tr>
          @empty
            <tr><td class="py-4 text-gray-500" colspan="6">No data yet</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</x-app-layout>

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  (function(){
    const ctx = document.getElementById('scoreChart');
    if (!ctx) return;

    // Build arrays for up to last 30 days (ascending) and derived 7 days
    const allLabels = @json(
      collect($history)->reverse()->values()->map(function ($r) {
        return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
      })
    );
    const allData = @json(
      collect($history)->reverse()->values()->map(function ($r) {
        return $r->score !== null ? (int) $r->score : null;
      })
    );
    const last7Labels = allLabels.slice(-7);
    const last7Data   = allData.slice(-7);

    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: last7Labels,
        datasets: [{
          label: 'Non-skip rate (%)',
          data: last7Data,
          tension: 0.25,
          borderColor: '#6366f1', // indigo-500
          backgroundColor: 'rgba(99, 102, 241, 0.15)',
          fill: true,
          pointRadius: 2.5,
          pointHoverRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            suggestedMin: 0,
            suggestedMax: 100,
            ticks: { stepSize: 20 }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: (ctx) => ` ${ctx.parsed.y}%` } }
        }
      }
    });

    // Toggle buttons
    const btn7 = document.getElementById('btn7');
    const btn30 = document.getElementById('btn30');
    const title = document.getElementById('trendTitle');
    function setActive(btnA, btnB) {
      btnA.classList.add('bg-gray-100');
      btnB.classList.remove('bg-gray-100');
    }
    btn7?.addEventListener('click', () => {
      chart.data.labels = last7Labels;
      chart.data.datasets[0].data = last7Data;
      chart.update();
      title.textContent = 'Trend (last 7 days)';
      setActive(btn7, btn30);
    });
    btn30?.addEventListener('click', () => {
      chart.data.labels = allLabels;
      chart.data.datasets[0].data = allData;
      chart.update();
      title.textContent = 'Trend (last 30 days)';
      setActive(btn30, btn7);
    });
  
  })();
</script>
