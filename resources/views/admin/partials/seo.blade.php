{{-- SEO + custom code block. Needs $seo (SeoMeta, may be new). Inputs are named seo[...] --}}
@php
    $all = config('admin.seo_fields');
    $groups = [
        'Search & social' => array_slice($all, 0, 12),
        'Structured data' => array_slice($all, 12, 1),
        'Custom code for this page only' => array_slice($all, 13),
    ];
    $get = fn ($key) => $seo->exists ? $seo->getAttribute($key) : (in_array($key, ['robots_index', 'robots_follow']) ? true : null);
@endphp

<div class="mt-3 rounded-xl bg-white shadow-sm ring-1 ring-gray-100" x-data="{ tab: 0 }">
  <div class="flex flex-wrap gap-1 border-b border-gray-100 px-3 pt-3">
    @foreach (array_keys($groups) as $i => $label)
      <button type="button" @click="tab = {{ $i }}" class="rounded-t-lg px-4 py-2 text-sm font-semibold" :class="tab === {{ $i }} ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-100'">{{ $label }}</button>
    @endforeach
  </div>
  @foreach (array_values($groups) as $i => $fields)
    <div class="px-5" x-show="tab === {{ $i }}" @if ($i) x-cloak @endif>
      <div class="grid gap-x-8 xl:grid-cols-12">
        @foreach ($fields as $f)
          @php
              $t = $f['type'];
              $span = in_array($t, ['toggle', 'select', 'image', 'number', 'color', 'date']) ? 'xl:col-span-4' : (in_array($t, ['text', 'url']) ? 'xl:col-span-6' : (in_array($t, ['code']) ? 'xl:col-span-6' : 'xl:col-span-12'));
          @endphp
          <div class="{{ $span }}">
            @include('admin.partials.field', ['f' => $f, 'value' => $get($f['key']), 'prefix' => 'seo'])
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
