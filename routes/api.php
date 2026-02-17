<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

// Rutas Públicas
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// Rutas Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Rutas de Usuario/Driver
    Route::get('/user/profile', function () {
        return auth()->user();
    });

    // Rutas de Admin (puedes usar un middleware personalizado de rol después)
    Route::prefix('admin')->group(function () {
        Route::get('/stats', function () {
            return response()->json(['message' => 'Datos de administración']);
        });
    });
    // CRUD de Usuarios
    Route::apiResource('users', UserController::class);

    // Ruta extra para obtener el perfil del usuario autenticado
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
    Route::get('/drivers-active', [UserController::class, 'getDriversActive']);
    /* Rutas de Wallet */
    // Consulta de recargo por coordenadas
    Route::get('zones/check-rate', [ZoneController::class, 'checkRate']);
    // CRUD de zonas
    Route::apiResource('zones', ZoneController::class);


    // Listado de zonas para el mapa
    Route::get('active-zones', [ZoneController::class, 'activeZones']);

    Route::post('/maps/expand-url', [ZoneController::class, 'expandShortUrl']);
    /* Orders */
    Route::apiResource('orders', OrderController::class);
});
