<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>XYZ Reservations</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-figtree bg-gray-50 flex flex-col justify-center items-center min-h-screen">

    <div class="max-w-3xl w-full px-6 text-center">

        <div class="flex justify-center mb-6">
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-full">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-6">
            Room and Equipments Reservation for XYZ University
        </h1>

        <p class="text-lg text-gray-500 mb-10">
            Please log in or create an account to request and manage your bookings.
        </p>

        @if (Route::has('login'))
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition">
                        Enter Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-sm transition">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3 bg-white hover:bg-gray-50 text-gray-700 font-semibold border border-gray-300 rounded-lg shadow-sm transition">
                            Create an Account
                        </a>
                    @endif
                @endauth
            </div>
        @endif

    </div>

</body>
</html>