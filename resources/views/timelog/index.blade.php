<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Time Logs</h2>
  </x-slot>

  <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="bg-white rounded-xl border p-4">
      <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-xs text-gray-500">From</label>
          <input type="date" name="from" value="{{ request('from') }}" class="px-2 py-1 text-sm rounded border border-gray-300 bg-white" />
        </div>
        <div>
          <label class="block text-xs text-gray-500">To</label>
          <input type="date" name="to" value="{{ request('to') }}" class="px-2 py-1 text-sm rounded border border-gray-300 bg-white" />
        </div>
        <div>
          <button type="submit" class="px-3 py-1.5 text-xs rounded border border-gray-300 bg-white hover:bg-gray-50">Filter</button>
        </div>
        <div class="text-xs text-gray-500 ms-auto">Default: last 7 days</div>
      </form>
    </div>

    <div class="bg-white rounded-xl border p-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500">
            <th class="py-2 pr-4">Started</th>
            <th class="py-2 pr-4">Ended</th>
            <th class="py-2 pr-4">Duration</th>
            <th class="py-2 pr-4">Genre</th>
            <th class="py-2 pr-4">Title</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($logs as $log)
            <tr>
              <td class="py-2 pr-4">{{ optional($log->started_at)->setTimezone('Asia/Tokyo')->format('Y-m-d H:i') }}</td>
              <td class="py-2 pr-4">{{ optional($log->ended_at)->setTimezone('Asia/Tokyo')->format('Y-m-d H:i') ?? '—' }}</td>
              <td class="py-2 pr-4">{{ $log->duration_hm ?? '00:00' }}</td>
              <td class="py-2 pr-4">{{ $log->genre ?? '—' }}</td>
              <td class="py-2 pr-4">{{ $log->title ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td class="py-4 text-gray-500" colspan="5">No logs</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <div class="mt-4">{{ $logs->links() }}</div>
    </div>
  </div>
</x-app-layout>
