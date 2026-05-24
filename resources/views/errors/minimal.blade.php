<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - yourlink.app</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased font-sans">
    <div class="min-h-full flex flex-col justify-center items-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h2 class="text-9xl font-extrabold text-blue-600 dark:text-blue-500 animate-pulse">
                @yield('code')
            </h2>
            <p class="mt-6 text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                @yield('message')
            </p>
            <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400">
                @yield('description', __('Something went wrong. Please try again later.'))
            </p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                <a href="/" class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all">
                    {{ __('Go back home') }}
                </a>
                <a href="mailto:business@ternis-edv.de" class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Contact support') }} <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
        <div class="mt-16 text-center text-sm text-gray-500 dark:text-gray-500">
            &copy; {{ date('Y') }} ternis-edv.de & xpsystems.eu. {{ __('All rights reserved.') }}
        </div>
    </div>
</body>
</html>
