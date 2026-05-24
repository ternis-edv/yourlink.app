<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.dashboard')] #[Title('API Access')] class extends Component
{
    public string $tokenName = '';
    public ?string $plainTextToken = null;

    public function createToken(): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
        ]);

        $token = Auth::user()->createToken($this->tokenName);
        $this->plainTextToken = $token->plainTextToken;
        $this->tokenName = '';
    }

    public function deleteToken(int $id): void
    {
        Auth::user()->tokens()->where('id', $id)->delete();
    }

    public function with(): array
    {
        return [
            'tokens' => Auth::user()->tokens,
        ];
    }
};
?>

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('API Access') }}</h1>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">{{ __('Create and manage personal access tokens to use yourlink.app via our upcoming API.') }}</p>
    </div>

    @if ($plainTextToken)
        <div class="mb-8 rounded-md bg-blue-50 dark:bg-blue-900/20 p-4 ring-1 ring-inset ring-blue-700/10 animate-pulse">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-400">{{ __('Token Created Successfully!') }}</h3>
            <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">{{ __('Please copy your new API token now. For your security, it won\'t be shown again.') }}</p>
            <div class="mt-3 flex items-center gap-2">
                <input type="text" readonly value="{{ $plainTextToken }}" class="block w-full rounded-md border-0 py-1.5 text-blue-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-blue-300 dark:ring-blue-700 font-mono text-sm">
                <button onclick="navigator.clipboard.writeText('{{ $plainTextToken }}'); alert('Copied!');" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    {{ __('Copy') }}
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Create Token -->
        <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-4">{{ __('Create New Token') }}</h3>
            <form wire:submit="createToken" class="space-y-4">
                <div>
                    <label for="tokenName" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Token Name') }}</label>
                    <div class="mt-2">
                        <input wire:model="tokenName" type="text" id="tokenName" placeholder="{{ __('e.g. My Website Bot') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('tokenName') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    {{ __('Generate Token') }}
                </button>
            </form>
        </div>

        <!-- Token List -->
        <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-slate-800">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ __('Active Tokens') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200 dark:divide-slate-800">
                @forelse($tokens as $token)
                    <li class="px-4 py-4 sm:px-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $token->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Last used') }}: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : __('Never') }}</p>
                        </div>
                        <button wire:click="deleteToken({{ $token->id }})" wire:confirm="{{ __('Revoke this token?') }}" class="text-sm font-semibold text-red-600 hover:text-red-500">
                            {{ __('Revoke') }}
                        </button>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('You don\'t have any active API tokens yet.') }}
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
