<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'Vehicle Booking') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    {{-- Background biru full screen --}}
    <div class="min-h-screen w-full bg-gradient-to-br from-blue-600 to-blue-800 relative overflow-hidden flex items-center justify-center p-6 md:p-12">

        {{-- Dekorasi blur --}}
        <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-blue-500 opacity-30 blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] rounded-full bg-blue-400 opacity-20 blur-3xl"></div>

        {{-- Container utama --}}
        <div class="relative z-10 w-full max-w-6xl grid grid-cols-1 md:grid-cols-2 gap-12 items-center">

            {{-- Sisi Kiri: Teks WELCOME --}}
            <div class="text-white px-4 md:px-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-lg bg-white/10 backdrop-blur mb-6">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-4 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
                    </svg>
                </div>
                <p class="text-xs tracking-widest text-blue-200 font-semibold mb-3">VEHICLE BOOKING SYSTEM</p>
                <h1 class="text-4xl md:text-5xl font-bold tracking-wide mb-6">WELCOME</h1>
                <p class="text-sm md:text-base text-blue-100 leading-relaxed max-w-md">
                    Aplikasi pemesanan kendaraan untuk perusahaan tambang nikel.
                    Setiap pemesanan memerlukan persetujuan dua tingkat sebelum
                    kendaraan digunakan.
                </p>
                <p class="text-xs text-blue-200 mt-10">© 2026 Vehicle Booking. All rights reserved.</p>
            </div>

            {{-- Sisi Kanan: Card Putih Form Login --}}
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 w-full max-w-md mx-auto md:mx-0">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Sign in</h2>
                <p class="text-sm text-gray-500 mb-8">Masukkan email dan password Anda</p>

                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-md text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                               placeholder="nama@perusahaan.com" />
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                               placeholder="••••••••" />
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember_me"
                               type="checkbox"
                               name="remember"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        <label for="remember_me" class="ml-2 text-sm text-gray-600">
                            Remember me
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-md transition text-sm">
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
