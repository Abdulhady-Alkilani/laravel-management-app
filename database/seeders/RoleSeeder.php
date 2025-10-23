<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Manager',
            'Worker',
            'Investor',
            'Workshop Supervisor',
            'Reviewer',
            'Architectural Engineer',
            'Civil Engineer',
            'Structural Engineer',
            'Electrical Engineer',
            'Mechanical Engineer',
            'Geotechnical Engineer',
            'Quantity Surveyor',
            'Site Engineer',
            'Environmental Engineer',
            'Surveying Engineer',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
        $this->command->info('Roles seeded successfully!');
    }
}