<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\FileStorageService;

class DriverController extends Controller
{
    public function index()
    {
        return new \App\Http\Resources\Driver\DriverCollection(Driver::with('user')->get());
    }

    public function store(\App\Http\Requests\DriverRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // 1. Find and Update User
            $user = User::findOrFail($request->user_id);

            // Should we update role? Yes, to ensure they have access.
            // Should we update status? Maybe ensuring they are active.
            $user->update([
                'role' => 'driver',
                'status' => 'active'
            ]);

            // 2. Create Driver
            $driverData = $request->only(['vehicle_type', 'plate_number', 'license_number', 'status']);
            $driverData['user_id'] = $user->id;
            $driverData['rating'] = 5.0; // Default
            $driverData['total_deliveries'] = 0;

            $driver = Driver::create($driverData);

            return new \App\Http\Resources\Driver\DriverResource($driver->load('user'));
        });
    }

    public function show(Driver $driver)
    {
        return new \App\Http\Resources\Driver\DriverResource($driver->load('user'));
    }

    public function update(\App\Http\Requests\DriverRequest $request, Driver $driver)
    {
        return DB::transaction(function () use ($request, $driver) {
            // 1. Update Driver
            $driverData = $request->only(['vehicle_type', 'plate_number', 'license_number', 'status']);
            $driver->update($driverData);

            // Note: We are NOT updating User details here anymore as per the "Select User" refactor.
            // If user details need update, it should be done in Users module.

            return new \App\Http\Resources\Driver\DriverResource($driver->load('user'));
        });
    }

    public function destroy(Driver $driver)
    {
        // Delete User (Driver will be deleted via cascade if set, otherwise manual)
        // Check Driver model relationship or DB constraint. 
        // Assuming we want to delete the User account entirely.
        $user = $driver->user;
        $user->delete(); // This should delete the driver if ON DELETE CASCADE is set on drivers.user_id

        // If not cascade, delete driver first? 
        // $driver->delete();
        // $user->delete();

        return response()->noContent();
    }
}
