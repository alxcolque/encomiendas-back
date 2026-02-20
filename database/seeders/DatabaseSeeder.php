<?php

namespace Database\Seeders;

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
        // 1. Offices
        $oruro = Office::create([
            'name' => 'Oficina Oruro Principal',
            'city' => 'Oruro',
            'address' => 'Av. 6 de Agosto y Aroma',
            'status' => 'active',
            'coordinates' => '-17.9647,-67.1060'
        ]);

        $lpz = Office::create([
            'name' => 'Oficina La Paz Centro',
            'city' => 'La Paz',
            'address' => 'Zona San Pedro',
            'status' => 'active',
            'coordinates' => '-16.5000,-68.1500'
        ]);

        $cba = Office::create([
            'name' => 'Oficina Cochabamba',
            'city' => 'Cochabamba',
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
            'phone' => '70000001',
            'pin' => '1234',
            'role' => 'worker',
        ]);

        $driverUser = User::create([
            'name' => 'Carlos Mamani (Driver)',
            'email' => 'driver@kolmox.com',
            'phone' => '67239563',
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

        $client = User::create([
            'name' => 'Ana Lopez (Client)',
            'email' => 'client@kolmox.com',
            'phone' => '71111111',
            'pin' => '0000',
            'role' => 'client',
        ]);

        // 3. Shipments
        // Shipment 1: Created (Oruro -> La Paz)
        Shipment::create([
            'tracking_code' => 'TRK-001-OR-LP',
            'origin_office_id' => $oruro->id,
            'destination_office_id' => $lpz->id,
            'sender_name' => 'Juan Sender',
            'sender_phone' => '77711111',
            'receiver_name' => 'Pedro Receiver',
            'receiver_phone' => '77722222',
            'current_status' => 'created',
            'price' => 50.00,
            'estimated_delivery' => now()->addDays(2),
        ]);

        // Shipment 2: In Transit (Oruro -> Cba)
        Shipment::create([
            'tracking_code' => 'TRK-002-OR-CBA',
            'origin_office_id' => $oruro->id,
            'destination_office_id' => $cba->id,
            'sender_name' => 'Maria Sender',
            'sender_phone' => '77733333',
            'receiver_name' => 'Luis Receiver',
            'receiver_phone' => '77744444',
            'current_status' => 'in_transit',
            'price' => 80.50,
            'estimated_delivery' => now()->addDay(1),
        ]);

        // Shipment 3: Delivered (La Paz -> Oruro)
        $deliveredShipment = Shipment::create([
            'tracking_code' => 'TRK-003-LP-OR',
            'origin_office_id' => $lpz->id,
            'destination_office_id' => $oruro->id,
            'sender_name' => 'Carlos Sender',
            'sender_phone' => '77755555',
            'receiver_name' => 'Sofia Receiver',
            'receiver_phone' => '77766666',
            'current_status' => 'delivered',
            'price' => 45.00,
            'estimated_delivery' => now()->subDay(1),
        ]);

        // 4. Invoices
        Invoice::create([
            'shipment_id' => $deliveredShipment->id,
            'nit_ci' => '1234567',
            'business_name' => 'Carlos Sender',
            'amount' => 45.00,
            'invoice_number' => 'INV-001',
            'payment_method' => 'cash',
            'status' => 'paid',
            'issued_at' => now(),
        ]);

        // 5. Shipment Events
        \App\Models\ShipmentEvent::create([
            'shipment_id' => $deliveredShipment->id,
            'status' => 'created',
            'description' => 'Shipment created at Oruro office',
            'location' => 'Oruro',
            'timestamp' => now()->subDays(2),
        ]);

        \App\Models\ShipmentEvent::create([
            'shipment_id' => $deliveredShipment->id,
            'status' => 'in_transit',
            'description' => 'Departed from Oruro',
            'location' => 'Oruro',
            'timestamp' => now()->subDays(1),
        ]);

        \App\Models\ShipmentEvent::create([
            'shipment_id' => $deliveredShipment->id,
            'status' => 'delivered',
            'description' => 'Delivered to receiver',
            'location' => 'Oruro',
            'timestamp' => now(),
        ]);

        // 5. Settings
        $this->call(SettingsSeeder::class);
    }
}
