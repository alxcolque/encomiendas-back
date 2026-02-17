<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'in:admin,worker,driver,client',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create($validated);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'user' => $user->load('driverProfile'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6', // Optional update
            'role' => 'in:admin,worker,driver,client',
            'phone' => 'nullable|string|max:20',
        ]);

        if (isset($validated['password'])) {
            $user->password = $validated['password']; // Casts will hash it
        }

        $user->update(collect($validated)->except('password')->toArray());

        if (isset($validated['password'])) {
            $user->save();
        }

        return response()->json([
            'message' => 'Usuario actualizado con éxito',
            'user' => $user
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
    public function getDriversActive()
    {
        $totalDriversActive = User::getDriversActiveAttribute();
        return response()->json(
            $totalDriversActive
        );
    }
}
