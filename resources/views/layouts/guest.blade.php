<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Sign in | 3EONE')
    </title>

    <link class="favicon"
        rel="icon"
        type="image/png"
        href="{{ asset('images/icon.webp') }}">

    @vite([
    'resources/css/app.css',
    'resources/css/signin.css',
    'resources/js/app.js'
    ])

    @stack('styles')
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

</head>

<body class="background-image min-h-screen bg-slate-100">

    @yield('content')

    @stack('scripts')

</body>

</html>