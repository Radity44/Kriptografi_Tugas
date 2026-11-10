<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin default
        User::firstOrCreate(
            ['email' => 'admin@magang.ac.id'],
            [
                'name' => 'Administrator Magang',
                'password' => Hash::make('Admin123!'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Mahasiswa demo default
        User::firstOrCreate(
            ['email' => 'mahasiswa@magang.ac.id'],
            [
                'name' => 'Raditya Mahasiswa',
                'password' => Hash::make('Mahasiswa123!'),
                'role' => User::ROLE_MAHASISWA,
                'email_verified_at' => now(),
            ]
        );
    }
}
