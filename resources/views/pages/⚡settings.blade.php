<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('components.layouts.dashboard')] #[Title('Account Settings')] class extends Component
{
    public string $name;
    public string $email;
    public int $default_link_length;
    
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->default_link_length = $user->settings['default_link_length'] ?? 7;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'default_link_length' => ['required', 'integer', 'min:5', 'max:20'],
        ]);

        $settings = $user->settings ?? [];
        $settings['default_link_length'] = $this->default_link_length;

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'settings' => $settings,
        ]);

        session()->flash('profile-status', __('Profile updated successfully.'));
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('password-status', __('Password updated successfully.'));
    }
};
?>

<div class="space-y-10 divide-y divide-gray-900/10 dark:divide-white/10">
    <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
        <div class="px-4 sm:px-0">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">{{ __('Profile Information') }}</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('Update your account\'s profile information and email address.') }}</p>
        </div>

        <form wire:submit="updateProfile" class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10 sm:rounded-xl md:col-span-2">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">{{ __('Name') }}</label>
                        <div class="mt-2">
                            <input wire:model="name" type="text" id="name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">{{ __('Email') }}</label>
                        <div class="mt-2">
                            <input wire:model="email" type="email" id="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="default_link_length" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">{{ __('Default Random Link Length') }}</label>
                        <div class="mt-2">
                            <select wire:model="default_link_length" id="default_link_length" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                                @for($i = 5; $i <= 15; $i++)
                                    <option value="{{ $i }}">{{ $i }} {{ __('characters') }}</option>
                                @endfor
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Applies to new links when no custom alias is provided.') }}</p>
                        @error('default_link_length') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-white/10 px-4 py-4 sm:px-8">
                @if (session('profile-status'))
                    <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ session('profile-status') }}</p>
                @endif
                <button type="submit" class="rounded-md bg-yourlink-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yourlink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yourlink-600">{{ __('Save') }}</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-x-8 gap-y-8 pt-10 md:grid-cols-3">
        <div class="px-4 sm:px-0">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">{{ __('Update Password') }}</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>

        <form wire:submit="updatePassword" class="bg-white dark:bg-slate-900 shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10 sm:rounded-xl md:col-span-2">
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="current_password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">{{ __('Current Password') }}</label>
                        <div class="mt-2">
                            <input wire:model="current_password" type="password" id="current_password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('current_password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="new_password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">{{ __('New Password') }}</label>
                        <div class="mt-2">
                            <input wire:model="new_password" type="password" id="new_password" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('new_password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="new_password_confirmation" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">{{ __('Confirm Password') }}</label>
                        <div class="mt-2">
                            <input wire:model="new_password_confirmation" type="password" id="new_password_confirmation" class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-white bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-yourlink-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-white/10 px-4 py-4 sm:px-8">
                @if (session('password-status'))
                    <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ session('password-status') }}</p>
                @endif
                <button type="submit" class="rounded-md bg-yourlink-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yourlink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yourlink-600">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
