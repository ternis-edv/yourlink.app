<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guest can see login page', function () {
    $this->get('/login')->assertSuccessful();
});

test('guest can see register page', function () {
    $this->get('/register')->assertSuccessful();
});

test('user can register', function () {
    Livewire::test('pages::auth::register')
        ->set('name', 'Test User')
        ->set('email', 'new@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('terms', true)
        ->call('register')
        ->assertRedirect('/dashboard');

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
    ]);

    Livewire::test('pages::auth::login')
        ->set('email', 'user@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/'); // Set session

    $this->post('/logout', ['_token' => session()->token()])
        ->assertRedirect('/');

    $this->assertGuest();
});
