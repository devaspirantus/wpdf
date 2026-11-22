<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'موظف الاستقبال',
                'email' => 'reception@wpdf.test',
                'role' => Role::reception,
            ],
            [
                'name' => 'موظف المعمل النظري',
                'email' => 'theory@wpdf.test',
                'role' => Role::theory,
            ],
            [
                'name' => 'مقيّم المعمل العملي',
                'email' => 'practical@wpdf.test',
                'role' => Role::practical,
            ],
            [
                'name' => 'موظف رفع البيانات',
                'email' => 'review@wpdf.test',
                'role' => Role::review,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'password' => Hash::make('password123'),
                ]
            );
        }

        $this->command->info('✅ Test accounts created successfully!');
        $this->command->table(
            ['Station', 'Email', 'Password'],
            collect($users)->map(fn ($u) => [
                $u['role']->label(),
                $u['email'],
                'password123',
            ])->toArray()
        );
    }
}
