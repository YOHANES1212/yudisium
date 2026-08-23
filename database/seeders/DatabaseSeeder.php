<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@yudisium.ac.id'],
            [
                'name'     => 'Admin Panitia',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role'     => 'admin',
                'pin'      => '123456',
            ]
        );

        User::firstOrCreate(
            ['email' => 'panitia@yudisium.ac.id'],
            [
                'name'     => 'Panitia Yudisium',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role'     => 'panitia',
                'pin'      => '654321',
            ]
        );
    }
}
