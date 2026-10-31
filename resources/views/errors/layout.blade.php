{{-- Branded error page. Uses NO database (the error may be a database error) and no shared layout. --}}
@php
    $en = request()->cookie('lang') === 'en' || (request()->cookie('lang') === null && app()->getLocale() === 'en');   // unknown URLs never reach the language middleware, so read the cookie directly
    $v = fn (string $p) => '/'.$p.(is_file(public_path($p)) ? '?v='.filemtime(public_path($p)) : '');
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'bn' }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>{{ $code }} · Adnan Architects &amp; Engineers</title>
  <link rel="stylesheet" href="{{ $v('assets/fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ $v('assets/css/tailwind.css') }}">
  <style>
    :root { --brand-50: 255 243 239; --brand-100: 255 225 216; --brand-400: 255 122 77; --brand-500: 255 90 54; --brand-600: 232 50 44; --brand-700: 196 32 31; --brand-900: 90 15 18 }
    body { font-family: 'Hind Siliguri', system-ui, sans-serif; background: #f8f8f9; color: #1c1c1f }
    @media (prefers-color-scheme: dark) { body { background: #0d0d10; color: #ececf0 } .card { background: #17171b !important } }
  </style>
</head>

<body class="grid min-h-screen place-items-center px-4 antialiased">
  <main class="card w-full max-w-lg rounded-3xl bg-white p-8 text-center shadow-xl ring-1 ring-black/5 sm:p-12">
    <span class="inline-block rounded-xl bg-white p-2 ring-1 ring-gray-100"><img src="{{ $v('assets/img/logo.png') }}" alt="Adnan Architects &amp; Engineers" class="h-14 w-auto"></span>
    <p class="mt-6 font-display text-7xl font-bold text-brand-600">{{ $code }}</p>
    <h1 class="mt-3 text-2xl font-bold leading-snug">{{ $en ? $titleEn : $titleBn }}</h1>
    <p class="mt-3 text-gray-500">{{ $en ? $textEn : $textBn }}</p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
      <a href="/" class="rounded-full bg-brand-600 px-6 py-3 font-semibold text-white hover:bg-brand-700">{{ $en ? 'Back to home' : 'হোমে ফিরুন' }}</a>
      <a href="javascript:history.back()" class="rounded-full border border-gray-300 px-6 py-3 font-semibold hover:border-brand-600 hover:text-brand-600">{{ $en ? 'Go back' : 'আগের পাতায়' }}</a>
    </div>
  </main>
</body>

</html>
