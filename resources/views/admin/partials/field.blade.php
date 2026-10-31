{{--
  One form control, drawn from a field definition in config/admin.php.
  Needs: $f (definition), $value (current value), $prefix (null or e.g. "seo")
--}}
@php
    $key = $f['key'];
    $name = $prefix ? "{$prefix}[{$key}]" : $key;
    $dot = $prefix ? "{$prefix}.{$key}" : $key;
    $type = $f['type'];
    $inputClass = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20';
    $mono = 'font-mono text-xs leading-relaxed';
    $locked = ! empty($f['developer_only']) && ! auth()->user()?->is_developer;
    $bi = fn ($lang) => old("{$dot}.{$lang}", is_array($value) ? ($value[$lang] ?? '') : '');
@endphp

<div class="py-4 border-b border-gray-100 last:border-0">
  <label class="block text-sm font-semibold text-gray-800">
    {{ $f['label'] }} @if (! empty($f['required']))<span class="text-red-600">*</span>@endif
  </label>
  @if (! empty($f['help']))<p class="text-xs text-gray-500">{{ $f['help'] }}</p>@endif

  @if ($locked)
    <div class="mt-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
      @if (is_array($value))
        <p><span class="text-[11px] font-semibold uppercase text-gray-400">বাংলা</span> {{ $value['bn'] ?? '' }}</p>
        <p><span class="text-[11px] font-semibold uppercase text-gray-400">English</span> {{ $value['en'] ?? '' }}</p>
      @else
        {{ $value }}
      @endif
    </div>
  @else
  @switch($type)
    @case('text')
    @case('url')
    @case('number')
    @case('date')
      <input type="{{ $type === 'text' ? 'text' : $type }}" name="{{ $name }}" @if($type === 'number') step="any" min="0" @endif
        value="{{ old($dot, $type === 'date' && $value ? \Illuminate\Support\Carbon::parse($value)->toDateString() : $value) }}" class="{{ $inputClass }}">
      @break

    @case('textarea')
      <textarea name="{{ $name }}" rows="{{ $f['rows'] ?? 4 }}" class="{{ $inputClass }}">{{ old($dot, $value) }}</textarea>
      @break

    @case('code')
      <textarea name="{{ $name }}" rows="{{ $f['rows'] ?? 6 }}" spellcheck="false" class="{{ $inputClass }} {{ $mono }}">{{ old($dot, $value) }}</textarea>
      @break

    @case('select')
      <select name="{{ $name }}" class="{{ $inputClass }}">
        @foreach ($f['options'] as $optValue => $optLabel)
          <option value="{{ $optValue }}" @selected(old($dot, $value ?? array_key_first($f['options'])) == $optValue)>{{ $optLabel }}</option>
        @endforeach
      </select>
      @break

    @case('toggle')
      <input type="hidden" name="{{ $name }}" value="0">
      <label class="mt-2 inline-flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="{{ $name }}" value="1" class="h-5 w-5 rounded border-gray-300 text-brand-600 focus:ring-brand-600" @checked((bool) old($dot, $value ?? ($key === 'active' || str_starts_with($key, 'robots_'))))>
        <span class="text-sm text-gray-600">On</span>
      </label>
      @break

    @case('color')
      <div class="mt-1 flex items-center gap-3" x-data="{ c: @js(old($dot, $value ?: '#000000')) }">
        <input type="color" x-model="c" class="h-10 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1">
        <input type="text" name="{{ $name }}" x-model="c" maxlength="7" class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono">
      </div>
      @break

    @case('image')
      <div class="mt-2 flex items-start gap-4">
        @if ($value)
          <img src="/{{ ltrim($value, '/') }}" alt="" class="h-20 w-28 rounded-lg border border-gray-200 bg-gray-50 object-contain">
        @endif
        <div class="text-sm">
          <input type="file" name="{{ $name }}" accept="image/png,image/jpeg,image/webp,image/gif" class="block text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
          <input type="hidden" name="{{ $prefix ? "{$prefix}[{$key}__current]" : "{$key}__current" }}" value="{{ $value }}">
          @if ($value)
            <label class="mt-2 flex items-center gap-2 text-xs text-gray-500"><input type="checkbox" name="{{ $prefix ? "{$prefix}[{$key}__remove]" : "{$key}__remove" }}" value="1" class="rounded border-gray-300"> Remove image</label>
          @endif
        </div>
      </div>
      @break

    @case('bi_text')
      <div class="mt-1 grid gap-3 md:grid-cols-2">
        <div><span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">বাংলা</span>
          <input type="text" name="{{ $name }}[bn]" value="{{ $bi('bn') }}" class="{{ $inputClass }}"></div>
        <div><span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">English</span>
          <input type="text" name="{{ $name }}[en]" value="{{ $bi('en') }}" class="{{ $inputClass }}"></div>
      </div>
      @break

    @case('bi_textarea')
    @case('bi_html')
      <div class="mt-1 grid gap-3 {{ $type === 'bi_html' ? '' : 'md:grid-cols-2' }}">
        @foreach (['bn' => 'বাংলা', 'en' => 'English'] as $lang => $langLabel)
          <div><span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ $langLabel }}</span>
            <textarea name="{{ $name }}[{{ $lang }}]" rows="{{ $f['rows'] ?? 4 }}" class="{{ $inputClass }} {{ $type === 'bi_html' ? $mono : '' }}">{{ $bi($lang) }}</textarea></div>
        @endforeach
      </div>
      @break

    @case('bi_lines')
    @case('bi_pairs')
      <div class="mt-1 grid gap-3 md:grid-cols-2">
        @foreach (['bn' => 'বাংলা', 'en' => 'English'] as $lang => $langLabel)
          @php
              $raw = is_array($value) ? ($value[$lang] ?? []) : [];
              $text = $type === 'bi_pairs' ? \App\Support\FieldProcessor::pairsText($raw) : \App\Support\FieldProcessor::linesText($raw);
          @endphp
          <div><span class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ $langLabel }}</span>
            <textarea name="{{ $name }}[{{ $lang }}]" rows="{{ $f['rows'] ?? 5 }}" class="{{ $inputClass }}">{{ old("{$dot}.{$lang}", $text) }}</textarea></div>
        @endforeach
      </div>
      @break

    @case('repeater')
      @php
          $rows = array_values((array) old($dot, is_array($value) ? $value : []));
          $rows = array_map(fn ($r, $i) => (array) $r + ['_id' => $i + 1], $rows, array_keys($rows));
      @endphp
      <div class="mt-2 space-y-3" x-data="{ rows: @js($rows), uid: {{ count($rows) + 1 }} }" x-init="window.Sortable && Sortable.create($el, { handle: '.row-handle', draggable: '.sort-row', animation: 150, forceFallback: true, onEnd(e) { rows.splice(e.newDraggableIndex, 0, rows.splice(e.oldDraggableIndex, 1)[0]); } })">
        <template x-for="(row, i) in rows" :key="row._id">
          <div class="sort-row rounded-xl border border-gray-200 bg-gray-50 p-3">
            <span class="row-handle mb-2 inline-block cursor-grab select-none text-lg leading-none text-gray-400 hover:text-brand-600" title="Drag to reorder">⠿</span>
            <div class="grid gap-3 md:grid-cols-4">
              @foreach ($f['sub'] as $sub)
                @if ($sub['type'] === 'bi_text')
                  <div class="md:col-span-2 grid grid-cols-2 gap-2">
                    <div><span class="text-[11px] text-gray-400">{{ $sub['label'] }} (বাংলা)</span>
                      <input type="text" :name="'{{ $name }}[' + row._id + '][{{ $sub['key'] }}][bn]'" x-model="row.{{ $sub['key'] }}.bn" class="{{ $inputClass }}"></div>
                    <div><span class="text-[11px] text-gray-400">{{ $sub['label'] }} (English)</span>
                      <input type="text" :name="'{{ $name }}[' + row._id + '][{{ $sub['key'] }}][en]'" x-model="row.{{ $sub['key'] }}.en" class="{{ $inputClass }}"></div>
                  </div>
                @else
                  <div><span class="text-[11px] text-gray-400">{{ $sub['label'] }}</span>
                    <input type="{{ $sub['type'] === 'number' ? 'number' : 'text' }}" :name="'{{ $name }}[' + row._id + '][{{ $sub['key'] }}]'" x-model="row.{{ $sub['key'] }}" class="{{ $inputClass }}"></div>
                @endif
              @endforeach
            </div>
            <button type="button" @click="rows.splice(i, 1)" class="mt-2 text-xs font-semibold text-red-600 hover:underline">Remove row</button>
          </div>
        </template>
        <button type="button" @click="rows.push({ _id: ++uid, @foreach ($f['sub'] as $sub) {{ $sub['key'] }}: {{ $sub['type'] === 'bi_text' ? "{ bn: '', en: '' }" : "''" }}, @endforeach })"
          class="rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:border-brand-600 hover:text-brand-600">+ Add row</button>
      </div>
      @break

    @case('gallery')
      @php
          $rows = array_values(is_array($value) ? $value : []);
          $rows = array_map(fn ($r, $i) => (array) $r + ['_id' => $i + 1], $rows, array_keys($rows));
      @endphp
      <div class="mt-2 space-y-4" x-data="{ rows: @js($rows), uid: {{ count($rows) + 1 }} }" x-init="window.Sortable && Sortable.create($el, { handle: '.row-handle', draggable: '.sort-row', animation: 150, forceFallback: true, onEnd(e) { rows.splice(e.newDraggableIndex, 0, rows.splice(e.oldDraggableIndex, 1)[0]); } })">
        <template x-for="(row, i) in rows" :key="row._id">
          <div class="sort-row rounded-xl border border-gray-200 bg-gray-50 p-4">
            <span class="row-handle mb-2 inline-block cursor-grab select-none text-lg leading-none text-gray-400 hover:text-brand-600" title="Drag to reorder">⠿</span>
            <div class="flex flex-col gap-4 md:flex-row">
              <div class="md:w-44 shrink-0">
                <template x-if="row.src"><img :src="'/' + row.src" alt="" class="h-28 w-full rounded-lg border border-gray-200 bg-white object-contain"></template>
                <input type="hidden" :name="'{{ $name }}[' + row._id + '][src]'" :value="row.src ?? ''">
                <input type="file" :name="'{{ $name }}[' + row._id + '][file]'" accept="image/png,image/jpeg,image/webp,image/gif" class="mt-2 block w-full text-xs text-gray-600 file:mr-2 file:rounded file:border-0 file:bg-brand-600 file:px-2 file:py-1 file:text-xs file:text-white">
              </div>
              <div class="grid flex-1 gap-3 md:grid-cols-2">
                @foreach (['title' => 'Title', 'size' => 'Size', 'note' => 'Note'] as $sk => $sl)
                  <div class="{{ $sk === 'note' ? 'md:col-span-2' : '' }} grid grid-cols-2 gap-2">
                    <div><span class="text-[11px] text-gray-400">{{ $sl }} (বাংলা)</span>
                      <input type="text" :name="'{{ $name }}[' + row._id + '][{{ $sk }}][bn]'" :value="row.{{ $sk }}?.bn ?? ''" class="{{ $inputClass }}"></div>
                    <div><span class="text-[11px] text-gray-400">{{ $sl }} (English)</span>
                      <input type="text" :name="'{{ $name }}[' + row._id + '][{{ $sk }}][en]'" :value="row.{{ $sk }}?.en ?? ''" class="{{ $inputClass }}"></div>
                  </div>
                @endforeach
              </div>
            </div>
            <button type="button" @click="rows.splice(i, 1)" class="mt-3 text-xs font-semibold text-red-600 hover:underline">Remove this image</button>
          </div>
        </template>
        <button type="button" @click="rows.push({ _id: ++uid, src: '', title: { bn: '', en: '' }, size: { bn: '', en: '' }, note: { bn: '', en: '' } })"
          class="rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:border-brand-600 hover:text-brand-600">+ Add image</button>
      </div>
      @break
  @endswitch
  @endif

  @error($dot) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
  @error($dot.'.bn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
