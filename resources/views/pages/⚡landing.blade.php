<?php

use App\Models\Link;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public string $original_url = '';
    public ?string $custom_alias = null;
    public ?string $password = null;
    public ?string $expires_at = null;
    public ?int $click_limit = null;
    
    public ?string $shortened_url = null;
    public bool $show_advanced = false;

    public function toggleAdvanced(): void
    {
        $this->show_advanced = !$this->show_advanced;
    }

    public function shorten(): void
    {
        $rules = [
            'original_url' => ['required', 'url', 'max:2048'],
        ];

        if (Auth::check()) {
            $rules['custom_alias'] = ['nullable', 'string', 'alpha_dash', 'max:50', 'unique:links,hash'];
            $rules['password'] = ['nullable', 'string', 'min:4', 'max:255'];
            $rules['expires_at'] = ['nullable', 'date', 'after:now'];
            $rules['click_limit'] = ['nullable', 'integer', 'min:1', 'max:1000000'];
        }

        $this->validate($rules);

        $hash = Auth::check() && $this->custom_alias 
            ? $this->custom_alias 
            : Str::random(7);
            
        // Ensure guest hashes are unique
        while (Link::where('hash', $hash)->exists()) {
            $hash = Str::random(7);
        }

        $settings = [];
        if ($this->password) {
            $settings['password'] = bcrypt($this->password);
        }
        if ($this->click_limit) {
            $settings['click_limit'] = $this->click_limit;
        }

        $link = Link::create([
            'user_id' => Auth::id(),
            'original_url' => $this->original_url,
            'hash' => $hash,
            'expires_at' => $this->expires_at,
            'settings' => empty($settings) ? null : $settings,
        ]);

        $this->shortened_url = url('/' . $link->hash);
        $this->reset(['original_url', 'custom_alias', 'password', 'expires_at', 'click_limit', 'show_advanced']);
    }

    public function resetForm(): void
    {
        $this->shortened_url = null;
    }
};
?>

<div class="flex-grow flex flex-col">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 w-full bg-white/80 dark:bg-slate-950/80 backdrop-blur-md border-b border-gray-200 dark:border-slate-800 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex flex-shrink-0 items-center gap-2 group">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xl group-hover:scale-110 transition-transform">
                            y
                        </div>
                        <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">yourlink<span class="text-blue-600">.app</span></span>
                    </a>
                    <div class="hidden md:flex sm:space-x-8 sm:ml-10">
                        <a href="#features" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ __('Features') }}</a>
                        <a href="#pricing" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ __('Pricing') }}</a>
                        <a href="#faq" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ __('FAQ') }}</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="/dashboard" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">{{ __('Dashboard') }} <span aria-hidden="true">&rarr;</span></a>
                    @else
                        <a href="/login" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">{{ __('Login') }}</a>
                        <a href="/register" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">{{ __('Register') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        <!-- Hero Section -->
        <div class="relative isolate overflow-hidden bg-white dark:bg-slate-950">
            <!-- Background Gradients -->
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 dark:opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 pt-16 pb-24 sm:pt-32 lg:px-8 lg:pt-40">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-6xl">{{ __('Links with Superpowers.') }}</h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                        {{ __('Shorten, track, and brand your links in seconds. 100% Free for individuals. 100% DSGVO-conform.') }}
                    </p>

                    <!-- Shortener Component -->
                    <div class="mt-10 max-w-xl mx-auto">
                        @if($shortened_url)
                            <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-gray-900/5 dark:ring-white/10 p-8 text-center animate-in fade-in slide-in-from-bottom-4 duration-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Your link is ready!') }}</h3>
                                <div class="flex items-center justify-center gap-2 mb-6">
                                    <input type="text" readonly value="{{ $shortened_url }}" class="block w-full rounded-md border-0 py-2.5 px-4 text-gray-900 dark:text-white bg-gray-50 dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-lg sm:leading-6 font-mono text-center">
                                </div>
                                <div class="flex gap-4 justify-center">
                                    <button 
                                        type="button" 
                                        onclick="navigator.clipboard.writeText('{{ $shortened_url }}'); alert('Copied!');"
                                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors"
                                    >
                                        {{ __('Copy Link') }}
                                    </button>
                                    <button wire:click="resetForm" class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white transition-colors">
                                        {{ __('Shorten Another') }}
                                    </button>
                                </div>
                                @guest
                                    <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Want custom aliases and analytics?') }} <a href="/register" class="font-semibold text-blue-600 hover:text-blue-500">{{ __('Sign up for free') }}</a>
                                    </p>
                                @endguest
                            </div>
                        @else
                            <form wire:submit="shorten" class="rounded-2xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-gray-900/5 dark:ring-white/10 p-2 relative overflow-hidden transition-all duration-300">
                                <div class="flex items-center gap-x-2">
                                    <div class="relative flex-grow">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M12.232 4.232a2.5 2.5 0 013.536 3.536l-1.225 1.224a.75.75 0 001.061 1.06l1.224-1.224a4 4 0 00-5.656-5.656l-3 3a4 4 0 00.225 5.865.75.75 0 00.977-1.138 2.5 2.5 0 01-.142-3.667l3-3z" />
                                                <path d="M11.603 7.963a.75.75 0 00-.977 1.138 2.5 2.5 0 01.142 3.667l-3 3a2.5 2.5 0 01-3.536-3.536l1.225-1.224a.75.75 0 00-1.061-1.06l-1.224 1.224a4 4 0 105.656 5.656l3-3a4 4 0 00-.225-5.865z" />
                                            </svg>
                                        </div>
                                        <input wire:model="original_url" type="url" required placeholder="{{ __('Enter your long URL here') }}" class="block w-full rounded-lg border-0 py-4 pl-12 pr-4 text-gray-900 dark:text-white bg-transparent ring-0 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-lg sm:leading-6">
                                    </div>
                                    <button type="submit" class="shrink-0 rounded-lg bg-blue-600 px-6 py-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors flex items-center justify-center gap-2" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="shorten">{{ __('Shorten') }}</span>
                                        <span wire:loading wire:target="shorten">...</span>
                                    </button>
                                </div>
                                @error('original_url') <span class="text-sm text-red-500 px-4 pb-2 block text-left">{{ $message }}</span> @enderror

                                @auth
                                    <div class="border-t border-gray-100 dark:border-slate-800 mt-2 p-2">
                                        <button type="button" wire:click="toggleAdvanced" class="flex w-full items-center justify-between px-2 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            <span>{{ __('Advanced Customization') }}</span>
                                            <svg class="h-5 w-5 transform transition-transform duration-200 {{ $show_advanced ? 'rotate-180' : '' }}" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        
                                        @if($show_advanced)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-slate-800/50 rounded-lg mt-2 text-left animate-in slide-in-from-top-2 fade-in duration-200">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Custom Alias') }}</label>
                                                    <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-600">
                                                        <span class="flex select-none items-center pl-3 text-gray-500 sm:text-sm">yourlink.app/</span>
                                                        <input wire:model="custom_alias" type="text" class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6">
                                                    </div>
                                                    @error('custom_alias') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Password Protection') }}</label>
                                                    <input wire:model="password" type="password" placeholder="{{ __('Optional') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 bg-white dark:bg-slate-900">
                                                    @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Expiration Date') }}</label>
                                                    <input wire:model="expires_at" type="datetime-local" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 bg-white dark:bg-slate-900">
                                                    @error('expires_at') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Click Limit') }}</label>
                                                    <input wire:model="click_limit" type="number" min="1" placeholder="{{ __('Optional') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 bg-white dark:bg-slate-900">
                                                    @error('click_limit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endauth
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 dark:opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
        </div>

        <!-- Interactive Stats (Mocked) -->
        <div class="bg-gray-50 dark:bg-slate-900 py-12 sm:py-16 border-y border-gray-200 dark:border-slate-800">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-16 text-center lg:grid-cols-3">
                    <div class="mx-auto flex max-w-xs flex-col gap-y-4">
                        <dt class="text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('Links Created') }}</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-5xl">142k+</dd>
                    </div>
                    <div class="mx-auto flex max-w-xs flex-col gap-y-4">
                        <dt class="text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('Clicks Tracked Anonymously') }}</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-5xl">12M+</dd>
                    </div>
                    <div class="mx-auto flex max-w-xs flex-col gap-y-4">
                        <dt class="text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('Uptime') }}</dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-5xl">99.9%</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Features Bento Grid -->
        <div id="features" class="py-24 sm:py-32 bg-white dark:bg-slate-950">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center mb-16">
                    <h2 class="text-base font-semibold leading-7 text-blue-600">{{ __('Everything you need') }}</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ __('Powerful features in a simple package') }}</p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                        <div class="flex flex-col bg-gray-50 dark:bg-slate-900 rounded-3xl p-8 ring-1 ring-gray-200 dark:ring-slate-800 shadow-sm transition-all hover:shadow-md">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-blue-600 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                    </svg>
                                </div>
                                {{ __('Privacy First & DSGVO Conform') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-400">
                                <p class="flex-auto">{{ __('We mask IPs automatically and never use tracking cookies. Your data and your visitors\' data remain private.') }}</p>
                            </dd>
                        </div>
                        <div class="flex flex-col bg-gray-50 dark:bg-slate-900 rounded-3xl p-8 ring-1 ring-gray-200 dark:ring-slate-800 shadow-sm transition-all hover:shadow-md">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-blue-600 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                    </svg>
                                </div>
                                {{ __('Real-time Analytics') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-400">
                                <p class="flex-auto">{{ __('Track your link performance with beautiful, privacy-friendly dashboards showing referrers, locations, and devices.') }}</p>
                            </dd>
                        </div>
                        <div class="flex flex-col bg-gray-50 dark:bg-slate-900 rounded-3xl p-8 ring-1 ring-gray-200 dark:ring-slate-800 shadow-sm transition-all hover:shadow-md">
                            <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-blue-600 text-white">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                </div>
                                {{ __('Advanced Security') }}
                            </dt>
                            <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-400">
                                <p class="flex-auto">{{ __('Protect your destination URLs with passwords, set expiration dates, or enforce maximum click limits.') }}</p>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <div id="pricing" class="py-24 sm:py-32 bg-gray-50 dark:bg-slate-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ __('Simple, transparent pricing') }}</h2>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">{{ __('No hidden fees. Free for individuals forever.') }}</p>
                </div>
                <div class="mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:mt-20 lg:max-w-4xl lg:grid-cols-2 lg:gap-x-8">
                    <!-- Free Tier -->
                    <div class="rounded-3xl p-8 ring-1 ring-gray-200 dark:ring-slate-800 bg-white dark:bg-slate-950 sm:p-10 shadow-lg relative transform lg:scale-105 z-10">
                        <h3 class="text-base font-semibold leading-7 text-blue-600">{{ __('For Individuals') }}</h3>
                        <p class="mt-4 flex items-baseline gap-x-2">
                            <span class="text-5xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('100% Free') }}</span>
                        </p>
                        <p class="mt-6 text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('Individual users can use yourlink.app completely for free.') }}</p>
                        <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-gray-600 dark:text-gray-400 sm:mt-10">
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-blue-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Unlimited Links') }}
                            </li>
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-blue-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Custom Aliases') }}
                            </li>
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-blue-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Password Protection') }}
                            </li>
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-blue-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Basic Analytics') }}
                            </li>
                        </ul>
                        <a href="/register" class="mt-8 block rounded-md bg-blue-600 px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors sm:mt-10">{{ __('Get started') }}</a>
                    </div>
                    
                    <!-- Enterprise Tier -->
                    <div class="rounded-3xl p-8 ring-1 ring-gray-200 dark:ring-slate-800 bg-gray-50/50 dark:bg-slate-900 sm:p-10">
                        <h3 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">{{ __('For Enterprises') }}</h3>
                        <p class="mt-4 flex items-baseline gap-x-2">
                            <span class="text-5xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('Custom') }}</span>
                        </p>
                        <p class="mt-6 text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('Enterprises should contact us for tailored solutions.') }}</p>
                        <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-gray-600 dark:text-gray-400 sm:mt-10">
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Custom Domains') }}
                            </li>
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Team Management') }}
                            </li>
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('API Access') }}
                            </li>
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                {{ __('Priority Support') }}
                            </li>
                        </ul>
                        <a href="mailto:business@ternis-edv.de" class="mt-8 block rounded-md bg-white dark:bg-slate-800 px-3.5 py-2.5 text-center text-sm font-semibold text-blue-600 dark:text-blue-400 shadow-sm ring-1 ring-inset ring-blue-200 dark:ring-blue-900 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors sm:mt-10">{{ __('Contact us') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-950 mt-auto border-t border-gray-200 dark:border-slate-800">
        <div class="mx-auto max-w-7xl px-6 py-12 md:flex md:items-center md:justify-between lg:px-8">
            <div class="flex justify-center space-x-6 md:order-2">
                <a href="#" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <span class="sr-only">{{ __('Privacy Policy') }}</span>
                    {{ __('Privacy Policy') }}
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <span class="sr-only">{{ __('Terms of Service') }}</span>
                    {{ __('Terms of Service') }}
                </a>
            </div>
            <div class="mt-8 md:order-1 md:mt-0">
                <p class="text-center text-xs leading-5 text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} ternis-edv.de & xpsystems.eu. {{ __('All rights reserved') }}. <span class="ml-2 px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-semibold">{{ __('DSGVO Conform') }}</span></p>
            </div>
        </div>
    </footer>
</div>
