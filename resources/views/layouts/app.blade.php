<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Vehicle Booking') }}</title>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-800">
    <div x-data="{ sidebarOpen: window.innerWidth >= 640 }" @resize.window="sidebarOpen = window.innerWidth >= 640" class="min-h-screen">
        <div
            x-cloak
            x-show="sidebarOpen && window.innerWidth < 640"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-950/40 backdrop-blur-[1px] sm:hidden"
            aria-hidden="true"></div>

        <aside
            x-show="sidebarOpen || window.innerWidth >= 640"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-slate-200 bg-white sm:w-64">
            <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-100 px-6">
                <a href="{{ auth()->user()->role === 'admin' ? route('dashboard') : route('approvals.index') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20">
                        <x-heroicon-o-truck class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-sm font-bold tracking-tight text-slate-900">Vehicle Booking</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ auth()->user()->role === 'admin' ? 'Panel administrator' : 'Panel persetujuan' }}</span>
                    </span>
                </a>
                <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 sm:hidden" aria-label="Tutup menu">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6" aria-label="Navigasi utama">
                <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Menu utama</p>
                @if (auth()->user()->role === 'admin')
                @php($navigationItems = [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'home'],
                ['label' => 'Kendaraan', 'route' => 'vehicles.index', 'active' => 'vehicles.*', 'icon' => 'truck'],
                ['label' => 'Driver', 'route' => 'drivers.index', 'active' => 'drivers.*', 'icon' => 'users'],
                ['label' => 'Pemesanan', 'route' => 'bookings.index', 'active' => 'bookings.index', 'icon' => 'calendar'],
                ['label' => 'Ajukan pemesanan', 'route' => 'bookings.create', 'active' => 'bookings.create', 'icon' => 'plus-circle'],
                ])
                @foreach ($navigationItems as $item)
                <a href="{{ route($item['route']) }}" @click="if (window.innerWidth < 640) sidebarOpen = false" @class([ 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition' , 'bg-blue-50 text-blue-700'=> request()->routeIs($item['active']),
                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs($item['active']),
                    ])>
                    @switch($item['icon'])
                    @case('home') <x-heroicon-o-home class="h-5 w-5 shrink-0" /> @break
                    @case('truck') <x-heroicon-o-truck class="h-5 w-5 shrink-0" /> @break
                    @case('users') <x-heroicon-o-users class="h-5 w-5 shrink-0" /> @break
                    @case('calendar') <x-heroicon-o-calendar class="h-5 w-5 shrink-0" /> @break
                    @case('plus-circle') <x-heroicon-o-plus-circle class="h-5 w-5 shrink-0" /> @break
                    @endswitch
                    <span>{{ $item['label'] }}</span>
                </a>
                @endforeach
                @elseif (auth()->user()->role === 'approver')
                <a href="{{ route('approvals.index') }}" @click="if (window.innerWidth < 640) sidebarOpen = false" @class([ 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition' , 'bg-blue-50 text-blue-700'=> request()->routeIs('approvals.*'),
                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('approvals.*'),
                    ])>
                    <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                    <span>Persetujuan</span>
                </a>
                @endif
            </nav>

        </aside>

        <div class="min-h-screen sm:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur sm:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 sm:hidden" aria-label="Buka menu">
                        <x-heroicon-o-bars-3 class="h-5 w-5" />
                    </button>
                    <div class="min-w-0 text-sm font-medium text-slate-500">
                        @isset($header)
                        <div class="truncate">{{ $header }}</div>
                        @else
                        <div class="truncate">Vehicle Booking</div>
                        @endisset
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden max-w-48 truncate text-sm font-medium text-slate-600 sm:block">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-3.5 py-2 text-sm font-semibold text-rose-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                            <x-heroicon-o-arrow-left-on-rectangle class="h-4 w-4" />
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="min-h-[calc(100vh-4rem)] px-4 py-6 sm:px-8 sm:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>