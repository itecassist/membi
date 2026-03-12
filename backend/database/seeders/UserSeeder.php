<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Membix platform admins ────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@membix.com'],
            [
                'password' => Hash::make('password'),
                'is_admin'  => true,
                'is_active' => true,
            ]
        );

        // ── Regular users (will become org members) ───────────────────────────
        $regularUsers = [
            ['email' => 'alice@example.com', 'name' => 'Alice'],
            ['email' => 'bob@example.com',   'name' => 'Bob'],
            ['email' => 'carol@example.com', 'name' => 'Carol'],
            ['email' => 'dave@example.com',  'name' => 'Dave'],
        ];

        foreach ($regularUsers as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'password'  => Hash::make('password'),
                    'is_admin'  => false,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Users seeded.');
    }
}
