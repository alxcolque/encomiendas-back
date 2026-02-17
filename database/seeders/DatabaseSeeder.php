<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\User::create([
            'name'   => 'Carlos Mamani',
            'phone'  => '+59167239563',
            'pin'    => '1234',
            'city'   => 'Oruro',
            'role'   => 'driver',
            'points' => 0,
        ]);

        \App\Models\User::create([
            'name'   => 'Juan Perez',
            'phone'  => '+59160427039',
            'pin'    => '4321',
            'city'   => 'Oruro',
            'role'   => 'admin',
            'points' => 0,
        ]);
    }
}
