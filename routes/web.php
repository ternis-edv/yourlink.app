<?php

use App\Http\Controllers\Auth\OAuthController;
use App\Models\Link;
use App\Models\LinkClick;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/links', 'pages::links::index')->name('links.index');
    Route::livewire('/links/{link}/edit', 'pages::links::edit')->name('links.edit');
    Route::livewire('/api-tokens', 'pages::api-tokens')->name('api-tokens');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});

// Redirection Logic (Last route)
Route::get('/{hash}', function (string $hash) {
    $link = Link::where('hash', $hash)->where('is_active', true)->firstOrFail();

    // Check expiration
    if ($link->expires_at && $link->expires_at->isPast()) {
        abort(404, __('Link has expired.'));
    }

    // Record click
    LinkClick::create([
        'link_id' => $link->id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'referer' => request()->header('referer'),
        // Country/City can be added with a GeoIP library later
    ]);

    return redirect()->away($link->original_url);
})->where('hash', '[a-zA-Z0-9_-]+');
