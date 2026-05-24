<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Forgot Password')] class extends Component
{
    public string $email = '';
    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->reset('email');
        } else {
            $this->addError('email', __($status));
        }
    }
};
?>

<div class="flex-grow flex items-center justify-center px-6 py-12 lg:px-8 bg-white dark:bg-slate-950">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <a href="/" class="flex justify-center items-center gap-2 group mb-10">
            <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-2xl group-hover:scale-110 transition-transform">
                y
            </div>
            <span class="font-bold text-2xl tracking-tight text-gray-900 dark:text-white">yourlink<span class="text-blue-600">.app</span></span>
        </a>

        <h2 class="text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
            {{ __('Reset your password') }}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('Enter your email address and we\'ll send you a link to reset your password.') }}
        </p>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            @if ($status)
                <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                    {{ $status }}
                </div>
            @endif

            <form wire:submit="sendResetLink" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Email address') }}</label>
                    <div class="mt-2">
                        <input wire:model="email" id="email" name="email" type="email" autocomplete="email" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                        {{ __('Send reset link') }}
                    </button>
                </div>
            </form>

            <p class="mt-10 text-center text-sm text-gray-500 dark:text-gray-400">
                <a href="/login" class="font-semibold leading-6 text-blue-600 hover:text-blue-500" wire:navigate>&larr; {{ __('Back to login') }}</a>
            </p>
        </div>
    </div>
</div>
