<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ClientAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone'  => 'required|string',
            'ci_nit' => 'required|string',
        ]);

        $client = Client::where('phone', $request->phone)
            ->where('ci_nit', $request->ci_nit)
            ->first();

        if (! $client) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if ($client->status === 'blocked' || $client->status === 'deleted') {
            return response()->json(['message' => 'Cuenta suspendida o eliminada'], 403);
        }

        // Crear Access Token (Corto: 15 mins)
        $accessToken = $client->createToken('access_token', ['client'], now()->addMinutes(15))->plainTextToken;

        // Crear Refresh Token (Largo: 7 días)
        $refreshToken = $client->createToken('refresh_token', ['client'], now()->addDays(7))->plainTextToken;

        $cookie = cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7,                        // 1 semana
            '/',                                // Path
            null,                               // Domain
            config('app.env') !== 'local',      // Secure
            true,                               // HttpOnly
            false,                              // Raw
            'Lax'                               // SameSite
        );

        return response()->json([
            'user'        => $client,
            'accessToken' => $accessToken,
        ])->withCookie($cookie);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'ci_nit' => 'required|string|max:255',
            'phone'  => 'required|string|max:20',
        ]);

        // Verificar si ya existe un cliente con ese teléfono o ci_nit
        $existing = Client::where('phone', $request->phone)
            ->orWhere('ci_nit', $request->ci_nit)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'El teléfono o CI/NIT ya se encuentra registrado.'], 400);
        }

        $client = Client::create([
            'name'   => $request->name,
            'ci_nit' => $request->ci_nit,
            'phone'  => $request->phone,
            'status' => 'normal',
        ]);

        // Authenticate the user directly after registration
        $accessToken = $client->createToken('access_token', ['client'], now()->addMinutes(15))->plainTextToken;
        $refreshToken = $client->createToken('refresh_token', ['client'], now()->addDays(7))->plainTextToken;

        $cookie = cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7,
            '/',
            null,
            config('app.env') !== 'local',
            true,
            false,
            'Lax'
        );

        return response()->json([
            'message'     => 'Cliente registrado satisfactoriamente',
            'user'        => $client,
            'accessToken' => $accessToken,
        ])->withCookie($cookie);
    }

    public function myShipments(Request $request)
    {
        $client = $request->user();

        // Evitar que un User (Admin) intente consultar esto y falle si las relaciones cambian
        if (!($client instanceof Client)) {
            return response()->json(['message' => 'No autorizado para ver recursos de cliente'], 403);
        }

        // Cargar los envíos con sus oficinas para tener status, origin, destination
        $shipments = $client->shipments()
            ->with(['originOffice', 'destinationOffice'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'shipments' => $shipments
        ]);
    }
}
