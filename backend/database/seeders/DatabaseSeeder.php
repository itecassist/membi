<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,    // must be first — roles reference permissions
            UserSeeder::class,
            OrganisationSeeder::class,  // creates orgs, system roles, assigns owner + super-admin
            MemberSeeder::class,        // additional members + groups
            SubscriptionSeeder::class,  // subscriptions + price options
        ]);
    }
}
