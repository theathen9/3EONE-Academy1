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

<<<<<<< HEAD
   

    <div class="d-flex">

       
=======

    <div class="d-flex">

>>>>>>> 1de5650 (Dashboad Admin)

        <main class="flex-grow-1 p-4">

            @yield('content')

        </main>

    </div>

    @stack('scripts')

</body>

</html>
