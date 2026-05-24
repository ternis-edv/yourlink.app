<?php

use App\Models\Activity;
use App\Models\User;

test('it logs user creation and updates', function () {
    $user = User::factory()->create(['name' => 'Original Name']);

    $activity = Activity::where('subject_id', $user->id)->where('description', 'created')->first();

    expect($activity)->not->toBeNull();

    // Log properties for debugging in case of failure
    // dump($activity->properties->toArray());

    $user->update(['name' => 'New Name']);

    $activity = Activity::where('subject_id', $user->id)->where('description', 'updated')->first();

    expect($activity)->not->toBeNull();
    // dump($activity->properties->toArray());
});
