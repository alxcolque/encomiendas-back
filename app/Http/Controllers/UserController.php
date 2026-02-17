<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Models\Wallet;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function store(UserStoreRequest $request)
    {
        $user = User::create($request->validated());
        /* Create Wallet */
        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
        ]);
        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user
        ], 201);
    }



    public function show(User $user)
    {
        $wallet = Wallet::where('user_id', $user->id)->first();
        return response()->json([
            'user' => $user,
            'wallet' => $wallet
        ]);
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        // Solo actualizamos los campos validados
        $data = $request->validated();

        // Si el pin viene vacío en el update, lo quitamos para no sobreescribir con null
        if (empty($data['pin'])) {
            unset($data['pin']);
        }

        $user->update($data);

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
