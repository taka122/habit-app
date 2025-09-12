<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daily Reports</h2>
  </x-slot>

  <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
    @if (session('status'))
      <div class="p-3 rounded bg-emerald-50 text-emerald-700 text-sm">{{ session('status') }}</div>
    @endif

    {{-- Today's report form or view --}}
    <div class="bg-white rounded-xl border p-4">
      <h3 class="font-semibold">Today's Report</h3>

      @if(!$myToday)
        <form method="POST" action="{{ route('reports.store') }}" class="mt-3">
          @csrf
          <label class="block text-sm font-medium">Content(What you did today / Insights / Next step)</label>
          <textarea name="content" rows="4" class="mt-1 w-full border rounded px-3 py-2"
                    placeholder="here">{{ old('content') }}</textarea>
          @error('content')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror

          <div class="grid grid-cols-2 gap-3 mt-3">
            <label class="block text-sm">Mood (1-5)
              <input type="number" name="mood" min="1" max="5" class="mt-1 w-full border rounded px-3 py-2" value="{{ old('mood') }}">
            </label>
            <label class="block text-sm">Effort (1-5)
              <input type="number" name="effort" min="1" max="5" class="mt-1 w-full border rounded px-3 py-2" value="{{ old('effort') }}">
            </label>
          </div>

          <div class="mt-3">
            <button class="px-3 py-1 rounded bg-indigo-600 text-white text-sm">Save Report</button>
          </div>
        </form>
      @else
        <div class="mt-3">
          <div class="text-sm text-gray-600">
            Mood: {{ $myToday->mood ?? '—' }} / Effort: {{ $myToday->effort ?? '—' }}
            <a href="{{ route('reports.edit', $myToday) }}" class="text-sm underline ms-2">Edit</a>
          </div>
          <p class="mt-2 whitespace-pre-wrap text-sm">{{ $myToday->content }}</p>
        </div>
      @endif
    </div>

    {{-- Recent (7 days) --}}
    <div class="bg-white rounded-xl border p-4">
      <div class="flex items-center justify-between">
      <h3 class="font-semibold">Recent Reports (7 days)</h3>
<a href="{{ route('reports.show') }}"
   class="px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50">
  All Reports
</a>
      </div>
      <ul class="divide-y mt-3">
        @forelse ($recent as $r)
          <li class="py-2">
            <div class="text-xs text-gray-500">{{ $r->date->format('Y-m-d') }}</div>
            <div class="text-sm line-clamp-2">{{ \Illuminate\Support\Str::limit($r->content, 100,'...') }}</div>
          </li>
        @empty
          <li class="py-2 text-sm text-gray-500">No reports yet</li>
        @endforelse
      </ul>
    </div>
  </div>
</x-app-layout>
