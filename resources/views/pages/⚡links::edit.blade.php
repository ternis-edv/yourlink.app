<?php

use App\Models\Link;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.dashboard')] #[Title('Edit Link')] class extends Component
{
    public Link $link;
    public string $original_url;
    public string $hash;
    public ?string $title;
    public ?string $description;
    public bool $is_active;
    public ?string $expires_at;
    public ?int $click_limit;

    public function mount(Link $link): void
    {
        if ($link->user_id !== Auth::id()) {
            abort(403);
        }

        $this->link = $link;
        $this->original_url = $link->original_url;
        $this->hash = $link->hash;
        $this->title = $link->title;
        $this->description = $link->description;
        $this->is_active = $link->is_active;
        $this->expires_at = $link->expires_at?->format('Y-m-d\TH:i');
        $this->click_limit = $link->settings['click_limit'] ?? null;
    }

    public function save(): void
    {
        $this->validate([
            'original_url' => ['required', 'url', 'max:2048'],
            'hash' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:links,hash,' . $this->link->id],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date'],
            'click_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $settings = $this->link->settings ?? [];
        if ($this->click_limit) {
            $settings['click_limit'] = (int) $this->click_limit;
        } else {
            unset($settings['click_limit']);
        }

        $this->link->update([
            'original_url' => $this->original_url,
            'hash' => $this->hash,
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'expires_at' => $this->expires_at ?: null,
            'settings' => empty($settings) ? null : $settings,
        ]);

        session()->flash('message', __('Link updated successfully.'));
        $this->redirect(route('links.index'), navigate: true);
    }
};
?>

<div>
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">{{ __('Edit Link') }}</h1>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('links.index') }}" class="inline-flex items-center rounded-md bg-white dark:bg-slate-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors" wire:navigate>
                {{ __('Cancel') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-200 dark:ring-slate-800 rounded-lg overflow-hidden">
        <form wire:submit="save" class="p-6 space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <div class="sm:col-span-4">
                    <label for="original_url" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Original URL') }}</label>
                    <div class="mt-2">
                        <input wire:model="original_url" type="url" id="original_url" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('original_url') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="hash" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Custom Alias') }}</label>
                    <div class="mt-2 flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-600">
                        <span class="flex select-none items-center pl-3 text-gray-500 sm:text-sm">yourlink.app/</span>
                        <input wire:model="hash" type="text" id="hash" class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6">
                    </div>
                    @error('hash') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-6">
                    <label for="title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Title (Internal or OG Tag)') }}</label>
                    <div class="mt-2">
                        <input wire:model="title" type="text" id="title" placeholder="{{ __('My Great Link') }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-6">
                    <label for="description" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Description') }}</label>
                    <div class="mt-2">
                        <textarea wire:model="description" id="description" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"></textarea>
                    </div>
                    @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="expires_at" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Expiration Date') }}</label>
                    <div class="mt-2">
                        <input wire:model="expires_at" type="datetime-local" id="expires_at" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('expires_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="click_limit" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">{{ __('Click Limit') }}</label>
                    <div class="mt-2">
                        <input wire:model="click_limit" type="number" id="click_limit" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('click_limit') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-6">
                    <div class="flex items-center">
                        <input wire:model="is_active" type="checkbox" id="is_active" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600 bg-white dark:bg-slate-900">
                        <label for="is_active" class="ml-3 block text-sm leading-6 text-gray-900 dark:text-gray-300">{{ __('Active') }}</label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-x-6 border-t border-gray-200 dark:border-slate-800 pt-6">
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
