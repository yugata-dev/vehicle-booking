<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Vehicle Booking') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Navbar Sederhana -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">Vehicle Booking</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if(Route::has('bookings.index'))
                            <a href="{{ route('bookings.index') }}" class="text-gray-600 hover:text-gray-900">Pemesanan</a>
                        @endif
                        @if(Route::has('bookings.create'))
                            <a href="{{ route('bookings.create') }}" class="text-gray-600 hover:text-gray-900">Ajukan Pemesanan</a>
                        @endif
                        @if(auth()->user()->role === 'admin')
                            @if(Route::has('vehicles.index'))
                                <a href="{{ route('vehicles.index') }}" class="text-gray-600 hover:text-gray-900">Kendaraan</a>
                            @endif
                            @if(Route::has('drivers.index'))
                                <a href="{{ route('drivers.index') }}" class="text-gray-600 hover:text-gray-900">Driver</a>
                            @endif
                        @endif
                        @if(Route::has('approvals.index') && auth()->user()->role === 'approver')
                            <a href="{{ route('approvals.index') }}" class="text-gray-600 hover:text-gray-900">Persetujuan</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-900">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Konten Utama -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>