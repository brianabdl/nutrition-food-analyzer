<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['nim' => '123456789', 'name' => 'Demo User 1', 'password' => 'password123'],
            ['nim' => '987654321', 'name' => 'Demo User 2', 'password' => 'password123'],
            ['nim' => '111222333', 'name' => 'Admin User',  'password' => 'admin123'],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['nim' => $data['nim']],
                ['name' => $data['name'], 'password' => bcrypt($data['password'])]
            );
        }
    }
}
