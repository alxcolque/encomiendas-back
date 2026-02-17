<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'pin'   => 'required',
        ]);
        $user = User::where('phone', $request->phone)->first();
        // Verificamos el PIN (en producción usa Hash::check si guardas el pin hasheado)
        if (! $user || $user->pin !== $request->pin) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }
        // Crear Access Token (Corto: 15 mins)
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(15))->plainTextToken;

        // Crear Refresh Token (Largo: 7 días)
        $refreshToken = $user->createToken('refresh_token', ['*'], now()->addDays(7))->plainTextToken;

        // Guardar Refresh Token en Cookie HttpOnly
        /* $cookie = cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 7, // minutos
                null,
                null,
                false, // true en producción con HTTPS
                true,  // HttpOnly
                false,
                'Lax'
            ); */

        $cookie = cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7,                        // 1 semana
            '/',                                // Path
            null,                               // Domain
            config('app.env') !== 'local', // Secure: Solo true en producción (HTTPS)
            true,                               // HttpOnly: SIEMPRE true por seguridad
            false,                              // Raw
            'Lax'                               // SameSite
        );

        return response()->json([
            'user'        => $user,
            'accessToken' => $accessToken,
        ])->withCookie($cookie);
    }

    public function refresh(Request $request)
    {
        $refreshTokenString = $request->cookie('refresh_token');

        if (! $refreshTokenString) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // Buscar el token en la base de datos de Sanctum
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($refreshTokenString);

        if (! $tokenModel || $tokenModel->name !== 'refresh_token' || $tokenModel->expires_at->isPast()) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $user           = $tokenModel->tokenable;
        $newAccessToken = $user->createToken('access_token', ['*'], now()->addMinutes(15))->plainTextToken;

        return response()->json([
            'user'        => $user,
            'accessToken' => $newAccessToken,
        ]);
    }

    public function logout()
    {
        // Revocar tokens del usuario actual
        auth()->user()->tokens()->delete();

        // Eliminar la cookie
        $cookie = Cookie::forget('refresh_token');

        return response()->json(['message' => 'Sesión cerrada'])->withCookie($cookie);
    }
}
