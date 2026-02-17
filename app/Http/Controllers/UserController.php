<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return new \App\Http\Resources\User\UserCollection(User::all());
    }

    public function store(\App\Http\Requests\UserStoreRequest $request)
    {
        $user = User::create($request->validated());
        return new \App\Http\Resources\User\UserResource($user);
    }

    public function show(User $user)
    {
        return new \App\Http\Resources\User\UserResource($user->load('driverProfile'));
    }

    public function update(\App\Http\Requests\UserUpdateRequest $request, User $user)
    {
        $validated = $request->validated();

        if (array_key_exists('password', $validated) && is_null($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return new \App\Http\Resources\User\UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }

    public function getDriversActive()
    {
        $totalDriversActive = User::getDriversActiveAttribute();
        return response()->json(
            $totalDriversActive
        );
    }
}
