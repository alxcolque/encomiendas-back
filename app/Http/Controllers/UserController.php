<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Files\FileStorage;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private const FOLDER_PATH = "kolmox/users";

    /* Get all users */
    public function index()
    {
        /* Get all users, exclude user with id 1 if needed, or just all */
        // Assuming ID 1 is the main admin and we might want to hide it or protect it
        // The user code excluded ID 1. Let's keep it safe.
        $users = User::where('id', '!=', 1)->get();

        return response()->json([
            "message" => "Usuarios obtenidos correctamente",
            "users" => $users->map(function ($user) {
                return [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "avatar" => $user->avatar,
                    "role" => $user->role,
                    "status" => $user->status,
                    "created_at" => $user->created_at,
                    "updated_at" => $user->updated_at,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();

        // PIN is hashed by model cast, but if we want to be explicit or if raw value is needed:
        // Model has 'pin' => 'hashed', so we can just save it. 
        // BUT the provided code used Hash::make. 
        // If the model casts it, setting it to a plain string works (Laravel hashes it).
        // If we Hash::make it AND the model casts it, it might double hash?
        // Let's check User.php cast. 'pin' => 'hashed'.
        // So we should pass PLAIN text pin.

        $data["role"] = $request->role ?? "client"; // Default to client if not set or enforced

        if ($request->avatar != null) {
            $url = FileStorage::upload($request->avatar, self::FOLDER_PATH);
            if ($url == 'Error33' || strpos($url, 'Error') === 0) { // Check for error
                return response()->json([
                    "message" => "No se subió su foto: " . $url
                ], 400);
            } else if (strpos($url, ",") !== false) {
                // ImageKit returns fileId,url
                $imageArr = explode(",", $url);
                $data["avatar"] = $imageArr[1]; // URL
                $data["avatar_key"] = $imageArr[0]; // FileID
            } else {
                // Local returns just URL
                $data["avatar"] = $url;
                $data["avatar_key"] = null;
            }
        }

        $user = User::create($data);

        return response()->json([
            "message" => "Usuario creado correctamente",
            "user" => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with('driverProfile')->find($id);
        if (!$user) {
            return response()->json(["message" => "Usuario no encontrado"], 404);
        }
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(["message" => "Usuario no encontrado"], 404);
        }

        $data = $request->validated();

        // Handle avatar update
        if ($request->has('avatar') && $request->avatar != null && $request->avatar != $user->avatar) {
            // Delete old avatar
            if ($user->avatar) {
                FileStorage::delete($user->avatar, $user->avatar_key);
            }

            // Upload new
            $url = FileStorage::upload($request->avatar, self::FOLDER_PATH);

            if ($url == "Error33" || strpos($url, 'Error') === 0) {
                return response()->json([
                    "message" => "No se subió su foto: " . $url
                ], 400);
            } else if (strpos($url, ",") !== false) {
                $imageArr = explode(",", $url);
                $data["avatar"] = $imageArr[1];
                $data["avatar_key"] = $imageArr[0];
            } else {
                $data["avatar"] = $url;
                $data["avatar_key"] = null;
            }
        }

        // Handle PIN update only if provided
        if (isset($data['pin']) && !empty($data['pin'])) {
            // Model casts it, just assignment is enough? 
            // If we assign $user->pin = '1234', it hashes it.
            // valid data['pin'] is plain text.
        } else {
            unset($data['pin']);
        }

        $user->update($data);

        return response()->json([
            "message" => "Usuario actualizado correctamente",
            "user" => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /* El ultimo usuario no se puede eliminar - Safety check? */
        /* Or prevent deleting self? */

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                "message" => "El usuario no existe"
            ], 404);
        }

        // Protect strict Admin (ID 1)
        if ($user->id == 1) {
            return response()->json([
                "message" => "No se puede eliminar al administrador principal"
            ], 400);
        }

        try {
            // Delete avatar first
            if ($user->avatar) {
                FileStorage::delete($user->avatar, $user->avatar_key);
            }

            $user->delete();

            return response()->json([
                "message" => "Usuario eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al eliminar el usuario"
            ], 500);
        }
    }

    /* Profile */
    public function profile(Request $request)
    {
        return response()->json($request->user()->load('driverProfile'));
    }

    // ✅ Actualizar nombre, email, phone o foto (Perfil propio)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|string', // Base64 expected
        ]);

        $data = $validated;

        if ($request->avatar != null && $request->avatar != $user->avatar) {
            if ($user->avatar) {
                FileStorage::delete($user->avatar, $user->avatar_key);
            }

            $url = FileStorage::upload($request->avatar, self::FOLDER_PATH);

            if (strpos($url, 'Error') === 0) {
                return response()->json([
                    "message" => "No se subió su foto"
                ], 400);
            } else if (strpos($url, ",") !== false) {
                $imageArr = explode(",", $url);
                $data["avatar"] = $imageArr[1];
                $data["avatar_key"] = $imageArr[0];
            } else {
                $data["avatar"] = $url;
                $data["avatar_key"] = null;
            }
        }

        $user->update($data);

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user' => $user
        ]);
    }

    // ✅ Cambiar PIN (en lugar de password)
    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required',
            'pin' => ['required', 'confirmed', 'size:4'], // confirmed expects pin_confirmation
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_pin, $user->pin)) {
            return response()->json(['message' => 'El PIN actual es incorrecto.'], 422);
        }

        $user->update([
            'pin' => $request->pin, // Casts will hash it
        ]);

        return response()->json(['message' => 'PIN actualizado correctamente.']);
    }
}
