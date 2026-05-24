<?php

use App\Http\Controllers\Auth\OAuthController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::landing')->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth::login')->name('login');
    Route::livewire('/register', 'pages::auth::register')->name('register');
    Route::livewire('/forgot-password', 'pages::auth::forgot-password')->name('password.request');
    Route::livewire('/reset-password/{token}', 'pages::auth::reset-password')->name('password.reset');

    Route::get('/auth/{provider}/redirect', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return 'Dashboard Placeholder';
    })->name('dashboard');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
