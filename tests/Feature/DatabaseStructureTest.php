<?php

use App\Models\Link;
use App\Models\LinkClick;
use App\Models\User;

test('users can be created with ULID', function () {
    $user = User::factory()->create();
    expect($user->id)->toBeString()->toHaveLength(26);
});

test('links can be created and belong to users', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $user->id]);

    expect($link->user_id)->toBe($user->id)
        ->and($link->user)->toBeInstanceOf(User::class);
});

test('links can be created without users (guests)', function () {
    $link = Link::factory()->guest()->create();
    expect($link->user_id)->toBeNull();
});

test('link clicks can be recorded and have masked IP', function () {
    $link = Link::factory()->create();
    $click = LinkClick::factory()->create(['link_id' => $link->id, 'ip_address' => '127.0.0.1']);

    expect($click->link_id)->toBe($link->id)
        ->and($click->ip_address)->toBe('127.0.0.0');
});
