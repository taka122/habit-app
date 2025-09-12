<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
  </x-slot>


<div class="w-full px-4 sm:px-6 lg:px-8">
  <div class="bg-white rounded-xl border p-6">
    @forelse ($posts as $post)
      <article class="py-4 border-b">
        <h3 class="font-semibold text-lg">{{ $post->title }}</h3>
        @php
          $isEdited = $post->updated_at && $post->created_at && $post->updated_at->ne($post->created_at);
        @endphp
        <p class="text-sm text-gray-500">
          @if($isEdited)
            <span class="inline-flex items-center gap-1">
              <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[10px] font-semibold">Edited</span>
              {{ $post->updated_at->format('Y-m-d H:i') }}
            </span>
          @else
            {{ $post->created_at?->format('Y-m-d H:i') ?? 'Not set' }}
          @endif
          <span class="mx-2">•</span>
          Mood: {{ $post->mood ?? '—' }} / Effort: {{ $post->effort ?? '—' }}
        </p>
        <p class="mt-2 text-sm">{{ \Illuminate\Support\Str::limit($post->content, 999, '…') }}</p>
      </article>
    @empty
      <p class="text-gray-500">No posts.</p>
    @endforelse

    {{-- paginate(10) のときだけ表示されます。all()なら出ません --}}
    @if(method_exists($posts, 'links'))
      <div class="mt-6">{{ $posts->links() }}</div>
    @endif
  </div>
  </div>
</x-app-layout>
