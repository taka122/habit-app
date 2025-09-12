<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Skip Reason</h2>
    </x-slot>

    <div class="py-6 max-w-md mx-auto">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-600 mb-3">Target: <span class="font-medium">{{ $checkin->title }}</span></p>

            <form method="POST" action="{{ route('checkins.skip', $checkin) }}">
                @csrf
                <label class="block text-sm font-medium">Reason (optional)</label>
                <input name="reason" class="mt-1 w-full border rounded px-3 py-2" maxlength="200">

                <label class="block text-sm font-medium mt-4">Next step (optional)</label>
                <input name="next_action" class="mt-1 w-full border rounded px-3 py-2" maxlength="200">

                <div class="mt-4 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 rounded bg-gray-700 text-white">Skip</button>
                    <a href="{{ route('dashboard') }}" class="text-sm underline">Back</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
