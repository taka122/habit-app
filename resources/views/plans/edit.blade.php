<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit</h2>
    </x-slot>

    <div class="py-6 max-w-md mx-auto">
        <div class="bg-white rounded-xl border p-4">
            <form id="update-form" method="POST" action="{{ route('checkins.update', $checkin) }}">
                @csrf
                @method('PATCH')

                <label class="block text-sm font-medium">Title</label>
                <input name="title" value="{{ old('title', $checkin->title) }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror

                <label class="block text-sm font-medium mt-4">Genre (optional)</label>
                <input name="genre" value="{{ old('genre', $checkin->genre) }}" maxlength="50" class="mt-1 w-full border rounded px-3 py-2" placeholder="e.g., Exercise / Study / Chores">

                <label class="block text-sm font-medium mt-4">Start time (optional)</label>
                <input type="datetime-local" name="start_at" value="{{ old('start_at', optional($checkin->start_at)->format('Y-m-d\TH:i')) }}" class="mt-1 w-full border rounded px-3 py-2">
                <label class="block text-sm font-medium mt-4">End time (optional)</label>
                <input type="datetime-local" name="end_at" value="{{ old('end_at', optional($checkin->end_at)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('start_at')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                @error('end_at')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror

            </form>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit" form="update-form" class="px-4 py-2 rounded bg-indigo-600 text-white">Update</button>
 
                <form method="POST" action="{{ route('checkins.destroy', $checkin) }}" onsubmit="return confirm('Are you sure you want to delete this?')" class="ml-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm rounded border border-rose-200 text-rose-600 hover:bg-rose-50">Delete</button>
                </form>
                               <a href="{{ route('dashboard') }}" class="text-sm underline">Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
