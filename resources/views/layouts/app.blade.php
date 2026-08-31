<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', '3EONE')
    </title>
 
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body>

    @include('components.navbar')

    <div class="d-flex">

        @include('components.sidebar')

        <main class="flex-grow-1 p-4">

            @yield('content')

        </main>

    </div>

    @stack('scripts')

</body>

</html>