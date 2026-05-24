<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\LinkClick;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default test user
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@yourlink.app',
        ]);

        // Create some links for the admin user
        Link::factory(5)->create([
            'user_id' => $user->id,
        ])->each(function (Link $link) {
            LinkClick::factory(rand(5, 20))->create([
                'link_id' => $link->id,
            ]);
        });

        // Create some guest links
        Link::factory(10)->guest()->create()->each(function (Link $link) {
            LinkClick::factory(rand(0, 10))->create([
                'link_id' => $link->id,
            ]);
        });

        // Create some random users with links
        User::factory(5)->create()->each(function (User $u) {
            Link::factory(rand(1, 3))->create([
                'user_id' => $u->id,
            ])->each(function (Link $link) {
                LinkClick::factory(rand(1, 5))->create([
                    'link_id' => $link->id,
                ]);
            });
        });
    }
}
