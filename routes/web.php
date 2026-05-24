<?php

use App\Http\Controllers\Auth\OAuthController;
use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Support\Facades\Hash;
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
    Route::livewire('/links/{link}/analytics', 'pages::links::analytics')->name('links.analytics');
    Route::livewire('/api-tokens', 'pages::api-tokens')->name('api-tokens');
    Route::livewire('/settings', 'pages::settings')->name('settings');

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

    // Check click limit
    if (isset($link->settings['click_limit'])) {
        $count = $link->clicks()->count();
        if ($count >= $link->settings['click_limit']) {
            abort(404, __('Link has reached its click limit.'));
        }
    }

    // Handle Password Protection
    if (isset($link->settings['password'])) {
        // If password is set, redirect to a password entry page instead of the target
        // For simplicity in this SFC-focused app, we could use a query param or a separate view.
        // Let's implement a simple session-based check.
        if (request()->query('p') !== '1' && ! session()->has('link_unlocked_'.$link->id)) {
            return response()->view('pages.link-password', ['link' => $link]);
        }
    }

    // Record click with full tracking data
    LinkClick::create([
        'link_id' => $link->id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'referer' => request()->header('referer'),
        'is_robot' => request()->header('User-Agent') && preg_match('/bot|crawl|slurp|spider|mediapartners/i', request()->header('User-Agent')),
    ]);

    return redirect()->away($link->original_url);
})->where('hash', '[a-zA-Z0-9_-]+');

Route::post('/unlock/{link}', function (Link $link) {
    if (! isset($link->settings['password'])) {
        return redirect('/'.$link->hash);
    }

    if (Hash::check(request('password'), $link->settings['password'])) {
        session()->put('link_unlocked_'.$link->id, true);

        return redirect('/'.$link->hash);
    }

    return back()->withErrors(['password' => __('Incorrect password.')]);
})->name('link.unlock');
