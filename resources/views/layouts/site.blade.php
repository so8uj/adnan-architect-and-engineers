@php
    use App\Support\Assets;
    use App\Support\Theme;
    $locale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- ===== SEO ===== --}}
  <title>{{ $seo['title'] }}</title>
  <meta name="description" content="{{ $seo['description'] }}">
  @if ($seo['keywords'])
  <meta name="keywords" content="{{ $seo['keywords'] }}">
  @endif
  <meta name="robots" content="{{ $seo['robots'] }}">
  <link rel="canonical" href="{{ $seo['canonical'] }}">
  <link rel="icon" href="{{ $seo['favicon'] }}">

  <meta property="og:type" content="{{ $seo['og']['type'] }}">
  <meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
  <meta property="og:locale" content="{{ $seo['og']['locale'] }}">
  <meta property="og:title" content="{{ $seo['og']['title'] }}">
  <meta property="og:description" content="{{ $seo['og']['description'] }}">
  <meta property="og:url" content="{{ $seo['canonical'] }}">
  @if ($seo['og']['image'])
  <meta property="og:image" content="{{ $seo['og']['image'] }}">
  @endif
  <meta name="twitter:card" content="{{ $seo['twitter']['card'] }}">
  <meta name="twitter:title" content="{{ $seo['og']['title'] }}">
  <meta name="twitter:description" content="{{ $seo['og']['description'] }}">
  @if ($seo['og']['image'])
  <meta name="twitter:image" content="{{ $seo['og']['image'] }}">
  @endif
  @if ($seo['twitter']['site'])
  <meta name="twitter:site" content="{{ $seo['twitter']['site'] }}">
  @endif
  @if ($seo['verify_google'])
  <meta name="google-site-verification" content="{{ $seo['verify_google'] }}">
  @endif
  @if ($seo['verify_bing'])
  <meta name="msvalidate.01" content="{{ $seo['verify_bing'] }}">
  @endif
  @foreach ($seo['schemas'] as $schema)
  <script type="application/ld+json">{!! $schema !!}</script>
  @endforeach

  {{-- ===== analytics (from Admin -> SEO) ===== --}}
  @if ($seo['gtm'])
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $seo['gtm'] }}');</script>
  @endif
  @if ($seo['ga'])
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seo['ga'] }}"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','{{ $seo['ga'] }}');</script>
  @endif
  @if ($seo['fbq'])
  <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src='https://connect.facebook.net/en_US/fbevents.js';s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script');fbq('init','{{ $seo['fbq'] }}');fbq('track','PageView');</script>
  @endif

  {{-- ===== styles ===== --}}
  <link rel="stylesheet" href="{{ Assets::v('assets/fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ Assets::v('assets/css/tailwind.css') }}">
  <link rel="stylesheet" data-turbo-track="reload" href="{{ Assets::v('assets/css/style.css') }}">
  <style id="theme-vars">{!! Theme::css() !!}</style>
  @if ($seo['css'])
  <style id="custom-css">{!! $seo['css'] !!}</style>
  @endif

  {{-- ===== site data for the page scripts (replaces the old data.js) ===== --}}
  <script>
    Object.assign(window, {!! \Illuminate\Support\Js::from($payload) !!});
    window.PAGE = @json($nav);
    window.PAGE_SLUG = @json($slug ?? null);
    window.DEFAULT_THEME = @json(Theme::defaultTheme());
  </script>
  <script src="{{ Assets::v('assets/js/i18n.js') }}"></script>
  <script src="{{ Assets::v('assets/js/video.js') }}"></script>

  @if ($nav === 'home' && ! empty($payload['INTRO']['poster']))
  <link rel="preload" as="image" href="{{ $payload['INTRO']['poster'] }}" fetchpriority="high">
  @endif

  {{-- Turbo Drive: page changes without a reload (SPA feel). Pages are never cached (Alpine / GSAP state), so Back always gets a fresh page. --}}
  <meta name="turbo-cache-control" content="no-cache">
  <script defer data-turbo-track="reload" src="{{ Assets::v('assets/vendor/turbo.js') }}"></script>
  {{-- header / footer / preloader are built by layout.js: for the first page here (before Alpine starts), for later pages by Turbo's before-render --}}
  <script defer data-turbo-track="reload" src="{{ Assets::v('assets/js/layout.js') }}"></script>
  <script defer src="{{ Assets::v('assets/vendor/alpine-collapse.min.js') }}"></script>
  <script defer src="{{ Assets::v('assets/vendor/alpine.min.js') }}"></script>
  <script defer src="{{ Assets::v('assets/vendor/gsap.min.js') }}"></script>
  <script defer src="{{ Assets::v('assets/vendor/ScrollTrigger.min.js') }}"></script>
  <script defer src="{{ Assets::v('assets/vendor/lenis.min.js') }}"></script>
  @if ($nav === 'home')
  <script defer src="{{ Assets::v('assets/js/marquee.js') }}"></script>
  @endif
  <script defer data-turbo-track="reload" src="{{ Assets::v('assets/js/app.js') }}"></script>

  {!! $seo['head_code'] !!}
</head>

<body class="font-sans antialiased" x-data="@yield('xdata', '{}')">
  @if ($seo['gtm'])
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $seo['gtm'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  @endif
  {!! $seo['body_start_code'] !!}

  @yield('content')

  @stack('scripts')

  {!! $seo['body_end_code'] !!}
  @if ($seo['js'])
  <script id="custom-js">{!! $seo['js'] !!}</script>
  @endif
</body>

</html>
