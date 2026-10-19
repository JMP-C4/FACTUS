@props([
    'keys' => [
        'error' => 'error',
        'danger' => 'error',
        'warning' => 'warning',
    ],
    'dismissible' => true,
    'container' => true,
])

@php
    $banners = [];

    foreach ($keys as $key => $type) {
        if (! session()->has($key)) {
            continue;
        }

        foreach ((array) session($key) as $message) {
            if ($message === null || $message === '') {
                continue;
            }

            $banners[] = ['type' => $type, 'message' => $message];
        }
    }
@endphp

@if (count($banners) > 0)
    @if ($container)
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
    @endif

    <div class="space-y-3">
        @foreach ($banners as $banner)
            <x-feedback.alert :type="$banner['type']" :dismissible="$dismissible">
                {{ $banner['message'] }}
            </x-feedback.alert>
        @endforeach
    </div>

    @if ($container)
        </div>
    @endif
@endif
