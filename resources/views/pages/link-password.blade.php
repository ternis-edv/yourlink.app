<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Password Protected') }} - yourlink.app</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans">
    <div class="min-h-full flex flex-col justify-center items-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yourlink-100 dark:bg-yourlink-900/30 mb-8">
                <svg class="h-8 w-8 text-yourlink-600 dark:text-yourlink-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ __('This link is protected') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Please enter the password to access the destination URL.') }}
            </p>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form action="{{ route('link.unlock', $link) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Password') }}</label>
                    <div class="mt-2">
                        <input id="password" name="password" type="password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-yourlink-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-yourlink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yourlink-600 transition-colors">
                        {{ __('Unlock Link') }}
                    </button>
                </div>
            </form>

            <p class="mt-10 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} yourlink.app by ternis-edv.de & xpsystems.eu
            </p>
        </div>
    </div>
</body>
</html>
