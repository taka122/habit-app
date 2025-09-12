<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Edit Report ({{ $report->date->format('Y-m-d') }})
    </h2>
  </x-slot>

  <div class="py-6 max-w-xl mx-auto">
    <div class="bg-white rounded-xl border p-4">
      <form method="POST" action="{{ route('reports.update', $report) }}">
        @csrf
        @method('PATCH')

        <label class="block text-sm font-medium">Content</label>
        <textarea name="content" rows="6" class="mt-1 w-full border rounded px-3 py-2">{{ old('content', $report->content) }}</textarea>
        @error('content')
          <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
        @enderror

        <div class="grid grid-cols-2 gap-3 mt-3">
          <label class="block text-sm">Mood (1-5)
            <input type="number" name="mood" min="1" max="5"
                   class="mt-1 w-full border rounded px-3 py-2"
                   value="{{ old('mood', $report->mood) }}">
          </label>
          <label class="block text-sm">Effort (1-5)
            <input type="number" name="effort" min="1" max="5"
                   class="mt-1 w-full border rounded px-3 py-2"
                   value="{{ old('effort', $report->effort) }}">
          </label>
        </div>

        <div class="mt-4 flex items-center gap-3">
          <button class="px-3 py-1 rounded bg-indigo-600 text-white text-sm">Update</button>
          <a href="{{ route('reports.index') }}" class="text-sm underline">Back</a>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
