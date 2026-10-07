<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Administrateur',
            'email' => 'admin@boutique.test',
        ]);

        $admin->assignRole('administrateur');
    }
}