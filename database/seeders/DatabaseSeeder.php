<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ShieldSeeder::class);

        Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ryu_dev', 'guard_name' => 'web']);

        $user = User::factory()->create([
            'name' => 'Test User',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('super_admin');

        $dev = User::factory()->create([
            'name' => 'Ryu Dev',
            'phone' => '1234567890',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
        ]);

        $dev->assignRole('ryu_dev');

        $this->call(JournalSeeder::class);
    }
}
