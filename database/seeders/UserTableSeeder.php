<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

         $user1 = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'full_name' => 'Greek',
                'password' => bcrypt('123456'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );



        // Second user
        $user2 = User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'full_name' => 'Test User',
                'password' => bcrypt('password123'),
                'role' => 'member',
                'is_active' => true,
            ]
        );
    }
}
