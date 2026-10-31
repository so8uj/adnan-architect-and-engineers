@php use App\Support\Assets; use App\Support\Theme; @endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title>Admin login</title>
  <link rel="stylesheet" href="{{ Assets::v('assets/fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ Assets::v('assets/css/tailwind.css') }}">
  <style>{!! Theme::css() !!} body { font-family: 'Hind Siliguri', system-ui, sans-serif }</style>
</head>

<body class="grid min-h-screen place-items-center bg-ink-950 px-4">
  <form method="POST" action="{{ route('admin.login') }}" class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl">
    @csrf
    <div class="mb-6 text-center">
      <span class="inline-block rounded-xl bg-white p-2 ring-1 ring-gray-100"><img src="/{{ ltrim(\App\Models\Setting::get('logo', 'assets/img/logo.png'), '/') }}" alt="" class="h-14 w-auto"></span>
      <h1 class="mt-4 text-xl font-bold text-gray-900">Admin login</h1>
    </div>
    @error('email')<p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
    <label class="block text-sm font-semibold text-gray-700">Email
      <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20"></label>
    <label class="mt-4 block text-sm font-semibold text-gray-700">Password
      <input type="password" name="password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-600/20"></label>
    <label class="mt-4 flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="remember" value="1" class="rounded border-gray-300"> Keep me signed in</label>
    <button class="mt-6 w-full rounded-lg bg-brand-600 py-3 text-sm font-semibold text-white hover:bg-brand-700">Sign in</button>
    <a href="{{ url('/') }}" class="mt-4 block text-center text-xs text-gray-500 hover:text-brand-600">← Back to website</a>
  </form>
</body>

</html>
