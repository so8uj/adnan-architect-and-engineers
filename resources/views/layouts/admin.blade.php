@php
    use App\Support\AdminMenu;
    use App\Support\Assets;
    use App\Support\Theme;
    $unread = \App\Models\ContactMessage::where('is_read', false)->count();
    $groups = AdminMenu::groups();
    $icon = fn ($name) => '<svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">'.AdminMenu::icon($name).'</svg>';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex,nofollow">
  <title>@yield('title', 'Admin') · {{ \App\Models\Setting::tr('site_name', 'en', 'Admin') }}</title>
  <link rel="icon" href="/{{ ltrim(\App\Models\Setting::get('favicon', 'assets/img/logo.png'), '/') }}">
  <link rel="stylesheet" href="{{ Assets::v('assets/fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ Assets::v('assets/css/tailwind.css') }}">
  <style>{!! Theme::css() !!} body { font-family: 'Hind Siliguri', system-ui, sans-serif; background: #f4f5f7 } [x-cloak] { display: none !important }</style>
  <script src="{{ Assets::v('assets/vendor/Sortable.min.js') }}"></script>
  <script defer src="{{ Assets::v('assets/vendor/alpine.min.js') }}"></script>
</head>

<body class="text-gray-800 antialiased" x-data="{ menu: false }">
  <div class="flex min-h-screen">
    {{-- sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto bg-ink-950 text-gray-300 transition lg:translate-x-0" :class="menu && 'translate-x-0'">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-5">
        <span class="rounded-lg bg-white p-1.5"><img src="/{{ ltrim(\App\Models\Setting::get('logo', 'assets/img/logo.png'), '/') }}" alt="" class="h-8 w-auto"></span>
        <span class="text-sm font-semibold leading-tight text-white">Admin panel</span>
      </a>

      <nav class="px-3 pb-8 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white' : 'hover:bg-white/10' }}">{!! $icon('dash') !!} Dashboard</a>
        <a href="{{ route('admin.messages') }}" class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.messages') ? 'bg-brand-600 text-white' : 'hover:bg-white/10' }}">
          {!! $icon('mail') !!} <span class="flex-1">Messages</span>
          @if ($unread)<span class="rounded-full bg-brand-500 px-2 text-xs text-white">{{ $unread }}</span>@endif
        </a>

        @foreach ($groups as $group)
          <div class="mb-1" x-data="{ open: {{ $group['open'] ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
              class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left font-semibold {{ $group['open'] ? 'text-white' : '' }} hover:bg-white/10">
              {!! $icon($group['icon']) !!}
              <span class="flex-1">{{ $group['label'] }}</span>
              <svg class="h-4 w-4 transition-transform duration-200" :class="open && 'rotate-90'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6" /></svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="ml-5 mt-1 space-y-0.5 border-l border-white/10 pl-3">
              @foreach ($group['children'] as $child)
                <a href="{{ $child['url'] }}" class="block rounded-lg px-3 py-2 {{ $child['active'] ? 'bg-brand-600 font-semibold text-white' : 'text-gray-400 hover:bg-white/10 hover:text-white' }}">{{ $child['label'] }}</a>
              @endforeach
            </div>
          </div>
        @endforeach
      </nav>

      <div class="border-t border-white/10 px-5 py-4 text-xs text-gray-400">
        <p class="text-gray-300">{{ auth()->user()->name }}</p>
        <p class="truncate">{{ auth()->user()->email }}</p>
        <a href="{{ route('admin.profile') }}" class="mt-1 inline-block font-semibold text-gray-200 hover:text-white hover:underline">My profile</a>
      </div>
    </aside>
    <div x-show="menu" x-cloak @click="menu = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

    {{-- main --}}
    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
      <header class="sticky top-0 z-20 flex flex-wrap items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 sm:px-6">
        <button class="rounded-lg border border-gray-300 p-2 lg:hidden" @click="menu = true" aria-label="Menu">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
        <h1 class="mr-auto text-lg font-bold">@yield('title', 'Admin')</h1>
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold hover:border-brand-600 hover:text-brand-600">View website ↗</a>
        <form method="POST" action="{{ route('admin.cache.clear') }}">@csrf
          <button class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold hover:border-brand-600 hover:text-brand-600" title="Clears the content cache so changes show immediately">Clear cache</button>
        </form>
        <form method="POST" action="{{ route('admin.logout') }}">@csrf
          <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black">Log out</button>
        </form>
      </header>

      <main class="flex-1 p-4 sm:p-6">
        @if (session('status'))
          <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please fix the following:</p>
            <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif
        @yield('content')
      </main>
    </div>
  </div>
  @stack('scripts')
</body>

</html>
