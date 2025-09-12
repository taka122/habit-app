<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Today's Plan</h2>
    </x-slot>

    <div class="py-6 max-w-md mx-auto">
        <div class="bg-white rounded-xl border p-4">
            <form method="POST" action="{{ route('plans.store') }}" x-data @submit="$el.querySelector('button[type=submit]').disabled=true">
                @csrf

                <label class="block text-sm font-medium">Title</label>
                <input name="title" value="{{ old('title') }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror

                <label class="block text-sm font-medium mt-4">Genre (optional)</label>
                <input name="genre" value="{{ old('genre') }}" maxlength="50" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g., Exercise / Study / Chores">

                <label class="block text-sm font-medium mt-4">Start time (optional)</label>
                <input type="datetime-local" name="start_at" value="{{ old('start_at') }}" class="mt-1 w-full border rounded px-3 py-2">

                <label class="block text-sm font-medium mt-4">End time (optional)</label>
                <input type="datetime-local" name="end_at" value="{{ old('end_at') }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('start_at')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                @error('end_at')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror

                <div class="mt-4 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white">Create</button>
                    <a href="{{ route('dashboard') }}" class="text-sm underline">Back</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
