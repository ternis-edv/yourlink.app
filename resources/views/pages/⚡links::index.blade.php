<?php

use App\Models\Link;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.dashboard')] #[Title('My Links')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function deleteLink(Link $link): void
    {
        if ($link->user_id === Auth::id()) {
            $link->delete();
            session()->flash('message', __('Link deleted successfully.'));
        }
    }

    public function toggleActive(Link $link): void
    {
        if ($link->user_id === Auth::id()) {
            $link->update(['is_active' => !$link->is_active]);
        }
    }

    public function with(): array
    {
        return [
            'links' => Auth::user()->links()
                ->where(function($query) {
                    $query->where('hash', 'like', "%{$this->search}%")
                          ->orWhere('original_url', 'like', "%{$this->search}%")
                          ->orWhere('title', 'like', "%{$this->search}%");
                })
                ->latest()
                ->withCount('clicks')
                ->paginate(10),
        ];
    }
};
?>

<div>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('My Links') }}</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">{{ __('A list of all your shortened URLs and their performance.') }}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="/" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" wire:navigate>
                {{ __('Create new link') }}
            </a>
        </div>
    </div>

    <div class="mt-8">
        <!-- Search -->
        <div class="max-w-sm mb-6">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search links...') }}" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 p-4 ring-1 ring-inset ring-green-700/10">
                <p class="text-sm font-medium text-green-700 dark:text-green-400">{{ session('message') }}</p>
            </div>
        @endif

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-slate-800">
                <thead class="bg-gray-50 dark:bg-slate-900">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">{{ __('Short Link') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('Original URL') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('Clicks') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('Status') }}</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-slate-950">
                    @foreach($links as $link)
                        <tr wire:key="{{ $link->id }}">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-blue-600 sm:pl-6">
                                <a href="{{ url('/' . $link->hash) }}" target="_blank">yourlink.app/{{ $link->hash }}</a>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                {{ $link->original_url }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $link->clicks_count }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <button wire:click="toggleActive('{{ $link->id }}')" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $link->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $link->is_active ? __('Active') : __('Inactive') }}
                                </button>
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('links.analytics', $link) }}" class="text-yourlink-600 hover:text-yourlink-900" wire:navigate>{{ __('Analytics') }}</a>
                                    <a href="{{ route('links.edit', $link) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>{{ __('Edit') }}</a>
                                    <button wire:click="deleteLink('{{ $link->id }}')" wire:confirm="{{ __('Are you sure you want to delete this link?') }}" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $links->links() }}
        </div>
    </div>
</div>
