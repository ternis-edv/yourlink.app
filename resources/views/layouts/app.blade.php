<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name', 'yourlink.app') }} - {{ __('Link Shortener') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased bg-white text-gray-900 dark:bg-slate-950 dark:text-gray-100 font-sans min-h-screen flex flex-col">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
