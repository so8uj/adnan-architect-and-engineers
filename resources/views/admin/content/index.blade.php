@extends('layouts.admin')

@section('title', $def['title'])

@section('content')
<div x-data="sortRows(@js(route('admin.content.reorder', $def['slug'])))">
  @if (! empty($def['config']))
    @php $cfgStyle = ($config[$def['style_key']] ?? 'v2') === 'v1' ? 'v1' : 'v2'; @endphp
    <form method="POST" action="{{ route('admin.content.config', $def['slug']) }}" x-data="{ style: @js($cfgStyle) }"
      @change="if ($event.target.name === @js($def['style_key'])) style = $event.target.value"
      class="mb-6 rounded-xl bg-white px-5 pb-4 shadow-sm ring-1 ring-gray-100">
      @csrf @method('PUT')
      <h2 class="-mx-5 border-b border-gray-100 bg-gray-50 px-5 py-2 text-xs font-bold uppercase tracking-wider text-gray-500">Slider settings</h2>
      <div class="grid gap-x-8 xl:grid-cols-12">
        @foreach ($def['config'] as $f)
          <div class="xl:col-span-4" @if (! empty($f['styles'])) x-show="{{ json_encode($f['styles']) }}.includes(style)" @endif>
            @include('admin.partials.field', ['f' => $f, 'value' => $config[$f['key']] ?? null, 'prefix' => null])
          </div>
        @endforeach
      </div>
      <button class="mt-3 rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save slider settings</button>
    </form>
  @endif

  <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
      <p class="text-sm text-gray-500">{{ $items->count() }} {{ \Illuminate\Support\Str::plural($def['single'], $items->count()) }}</p>
      @if ($items->count() > 1)
        <p class="text-xs text-gray-400">Drag the <span class="font-semibold">⠿</span> handle to change the order. It saves automatically.</p>
      @endif
    </div>
    @if (! empty($def['searchable']))
      <input type="search" x-model="q" @input="filter()" placeholder="Search {{ strtolower($def['title']) }} …" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm sm:w-72 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20">
    @endif
    <a href="{{ route('admin.content.create', $def['slug']) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ Add {{ $def['single'] }}</a>
  </div>

  <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
        <tr>
          <th class="w-10 px-3 py-3"></th>
          @foreach ($def['columns'] as $col => $kind)
            <th class="px-4 py-3">{{ $col === 'sort' ? 'order' : str_replace('_', ' ', $col) }}</th>
          @endforeach
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100" x-ref="body">
        @forelse ($items as $item)
          <tr class="bg-white hover:bg-gray-50" data-id="{{ $item->id }}">
            <td class="drag-handle w-10 cursor-grab select-none px-3 py-3 text-center text-lg leading-none text-gray-400 hover:text-brand-600 active:cursor-grabbing" title="Drag to reorder">⠿</td>
            @foreach ($def['columns'] as $col => $kind)
              <td class="px-4 py-3 align-middle" @if ($col === 'sort') data-order @endif>
                @if ($kind === 'bi')
                  <p class="font-semibold">{{ $item->tr($col, 'bn') }}</p>
                  @if (is_array($item->$col) && ($item->$col['en'] ?? ''))<p class="text-xs text-gray-500">{{ $item->$col['en'] }}</p>@endif
                @elseif ($kind === 'image')
                  @if ($item->$col)<img src="/{{ ltrim($item->$col, '/') }}" alt="" class="h-12 w-16 rounded border border-gray-200 object-cover">@endif
                @elseif ($kind === 'bool')
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->$col ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $item->$col ? 'Visible' : 'Hidden' }}</span>
                @else
                  {{ $item->$col instanceof \Carbon\Carbon ? $item->$col->toDateString() : $item->$col }}
                @endif
              </td>
            @endforeach
            <td class="whitespace-nowrap px-4 py-3 text-right">
              @if (! empty($def['view_url']))
                <a href="{{ str_replace('{slug}', $item->slug, $def['view_url']) }}" target="_blank" rel="noopener" class="mr-3 text-gray-500 hover:text-brand-600">View ↗</a>
              @endif
              <a href="{{ route('admin.content.edit', [$def['slug'], $item->id]) }}" class="mr-3 font-semibold text-brand-600 hover:underline">Edit</a>
              <form method="POST" action="{{ route('admin.content.destroy', [$def['slug'], $item->id]) }}" class="inline" onsubmit="return confirm('Delete this {{ $def['single'] }}?')">@csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ count($def['columns']) + 2 }}" class="px-4 py-10 text-center text-gray-500">Nothing here yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- small toast --}}
  <div x-show="state" x-cloak x-transition.opacity class="fixed bottom-5 right-5 z-50 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-lg"
    :class="state === 'saved' ? 'bg-green-600' : (state === 'error' ? 'bg-red-600' : 'bg-gray-800')" x-text="state === 'saved' ? 'Order saved ✓' : (state === 'error' ? 'Could not save the order' : 'Saving…')"></div>
</div>
@endsection

@push('scripts')
<script>
  function sortRows(url) {
    return {
      state: '', q: '',
      filter() {
        const q = this.q.trim().toLowerCase();
        this.$refs.body.querySelectorAll('tr[data-id]').forEach((r) => r.classList.toggle('hidden', q !== '' && !r.textContent.toLowerCase().includes(q)));
      },
      init() {
        if (!window.Sortable || !this.$refs.body.querySelector('tr[data-id]')) return;
        Sortable.create(this.$refs.body, {
          handle: '.drag-handle', draggable: 'tr[data-id]', animation: 160, forceFallback: true,
          ghostClass: 'opacity-40', onEnd: () => this.save(),
        });
      },
      async save() {
        const rows = [...this.$refs.body.querySelectorAll('tr[data-id]')];
        rows.forEach((r, i) => { const c = r.querySelector('[data-order]'); if (c) c.textContent = i + 1; });
        this.state = 'saving';
        try {
          const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ ids: rows.map((r) => Number(r.dataset.id)) }),
          });
          this.state = res.ok ? 'saved' : 'error';
        } catch (e) { this.state = 'error'; }
        setTimeout(() => this.state = '', 2000);
      },
    };
  }
</script>
@endpush
