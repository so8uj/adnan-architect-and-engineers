{{--
  Lays out a list of fields so the whole screen width is used.
    $fields  field definitions
    $get     fn(key) => current value
    $prefix  null or "seo"
    $mode    'grid'  : fields flow in a 12 column grid (short fields side by side, long ones full width)
             'split' : long fields on the left, short fields (images, toggles, order, links ...) in a side card on the right
--}}
@php
    $compact = ['image', 'select', 'toggle', 'number', 'color', 'date'];
    $isSide = fn ($f) => in_array($f['type'], array_merge($compact, ['text', 'url']));
    $span = fn ($f) => $f['type'] === 'color' ? 'xl:col-span-3' : (in_array($f['type'], $compact) ? 'xl:col-span-4' : (in_array($f['type'], ['text', 'url']) ? 'xl:col-span-6' : 'xl:col-span-12'));
    $card = 'rounded-xl bg-white px-5 shadow-sm ring-1 ring-gray-100 self-start';
@endphp

@if (($mode ?? 'grid') === 'split')
  @php
      $main = array_values(array_filter($fields, fn ($f) => ! $isSide($f)));
      $side = array_values(array_filter($fields, $isSide));
  @endphp
  <div class="grid gap-6 xl:grid-cols-12">
    <div class="{{ $card }} {{ $side ? 'xl:col-span-8' : 'xl:col-span-12' }}">
      @foreach ($main as $f)
        @include('admin.partials.field', ['f' => $f, 'value' => $get($f['key']), 'prefix' => $prefix ?? null])
      @endforeach
    </div>
    @if ($side)
      <div class="{{ $card }} xl:col-span-4">
        <h2 class="-mx-5 border-b border-gray-100 bg-gray-50 px-5 py-2 text-xs font-bold uppercase tracking-wider text-gray-500">Image, link &amp; options</h2>
        @foreach ($side as $f)
          @include('admin.partials.field', ['f' => $f, 'value' => $get($f['key']), 'prefix' => $prefix ?? null])
        @endforeach
      </div>
    @endif
  </div>
@else
  @php $lastGroup = null; @endphp
  <div class="{{ $card }} w-full">
    <div class="grid gap-x-8 xl:grid-cols-12">
      @foreach ($fields as $f)
        @if (! empty($f['group']) && $f['group'] !== $lastGroup)
          <h2 class="-mx-5 border-b border-gray-100 bg-gray-50 px-5 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 xl:col-span-12">{{ $f['group'] }}</h2>
          @php $lastGroup = $f['group']; @endphp
        @endif
        <div class="{{ $span($f) }}">
          @include('admin.partials.field', ['f' => $f, 'value' => $get($f['key']), 'prefix' => $prefix ?? null])
        </div>
      @endforeach
    </div>
  </div>
@endif
