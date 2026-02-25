<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use App\Models\Office;
use App\Models\Shipment;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Todo: City
        $lpz = City::create([
            'name' => 'La Paz',
            'is_active' => true,
        ]);

        $cba = City::create([
            'name' => 'Cochabamba',
            'is_active' => true,
        ]);

        $oruro = City::create([
            'name' => 'Oruro',
            'is_active' => true,
        ]);
        // 1. Offices
        $oruro = Office::create([
            'name' => 'Oficina Oruro Principal',
            'city_id' => $oruro->id,
            'address' => 'Av. 6 de Agosto y Aroma',
            'status' => 'active',
            'coordinates' => '-17.9647,-67.1060'
        ]);

        $lpz = Office::create([
            'name' => 'Oficina La Paz Centro',
            'city_id' => $lpz->id,
            'address' => 'Zona San Pedro',
            'status' => 'active',
            'coordinates' => '-16.5000,-68.1500'
        ]);

        $cba = Office::create([
            'name' => 'Oficina Cochabamba',
            'city_id' => $cba->id,
            'address' => 'Av. Ayacucho',
            'status' => 'active',
            'coordinates' => '-17.3935,-66.1570'
        ]);

        // 2. Users (Admin, Worker, Driver, Client)
        $admin = User::create([
            'name' => 'Juan Perez (Admin)',
            'email' => 'admin@kolmox.com',
            'phone' => '60427039',
            'pin' => '4321', // Encrypted by model cast
            'role' => 'admin',
        ]);

        $worker = User::create([
            'name' => 'Maria Gomez (Worker)',
            'email' => 'worker@kolmox.com',
            'phone' => '67239563',
            'pin' => '1234',
            'role' => 'worker',
        ]);

        $driverUser = User::create([
            'name' => 'Carlos Mamani (Driver)',
            'email' => 'driver@kolmox.com',
            'phone' => '70000000',
            'pin' => '1234',
            'role' => 'driver',
        ]);

        // Create Driver Profile
        Driver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'Lic-12345',
            'plate_number' => 'ABC-123',
            'status' => 'active',
            'vehicle_type' => 'motorcycle',
            'rating' => 5.00,
            'total_deliveries' => 0
        ]);
        // 5. Settings
        $this->call(SettingsSeeder::class);
    }
}
