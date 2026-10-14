<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Pool',
                'username' => 'admin',
                'email' => 'admin@company.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Approver Level 1',
                'username' => 'approver1',
                'email' => 'approver1@company.com',
                'role' => 'approver',
            ],
            [
                'name' => 'Approver Level 2',
                'username' => 'approver2',
                'email' => 'approver2@company.com',
                'role' => 'approver',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}
