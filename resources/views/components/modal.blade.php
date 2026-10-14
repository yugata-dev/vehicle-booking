@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ][$maxWidth] ?? 'sm:max-w-2xl';
@endphp

<div
    x-data="{ show: @js($show) }"
    x-on:open-modal.window="$event.detail === '{{ $name }}' && (show = true)"
    x-on:close-modal.window="$event.detail === '{{ $name }}' && (show = false)"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-effect="document.body.classList.toggle('overflow-y-hidden', show)"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none"
    role="dialog"
    aria-modal="true">
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transform transition-all"
        @click="show = false">
        <div class="absolute inset-0 bg-slate-900/50"></div>
    </div>

    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="mb-6 transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:mx-auto {{ $maxWidth }}"
        @click.stop>
        {{ $slot }}
    </div>
</div>
