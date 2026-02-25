<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Http\Resources\Client\ClientCollection;
use App\Http\Resources\Client\ClientResource;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * GET /api/clients
     * Retorna todos los clientes.
     */
    public function index()
    {
        $clients = Client::orderBy('name')->get();

        return new ClientCollection($clients);
    }

    /**
     * POST /api/clients
     * Crea un nuevo cliente.
     */
    public function store(ClientStoreRequest $request)
    {
        $client = Client::create($request->validated());

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/clients/{client}
     * Muestra un cliente específico.
     */
    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    /**
     * PUT/PATCH /api/clients/{client}
     * Actualiza un cliente.
     */
    public function update(ClientUpdateRequest $request, Client $client)
    {
        $client->update($request->validated());

        return new ClientResource($client);
    }

    /**
     * DELETE /api/clients/{client}
     * Elimina un cliente.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente.',
        ]);
    }

    /**
     * PATCH /api/clients/{client}/status
     * Cambia únicamente el estado del cliente (normal, blocked, deleted).
     */
    public function changeStatus(Client $client, string $status)
    {
        $allowed = ['normal', 'blocked', 'deleted'];

        if (!in_array($status, $allowed)) {
            return response()->json([
                'message' => 'Estado no válido. Los valores permitidos son: normal, blocked, deleted.',
            ], 422);
        }

        $client->update(['status' => $status]);

        return new ClientResource($client);
    }

    /**
     * GET /api/clients/search?q=
     * Búsqueda rápida por nombre o CI/NIT.
     */
    public function search()
    {
        $q = request('q', '');

        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('ci_nit', 'like', "%{$q}%")
            ->orderBy('name')
            ->get();

        return new ClientCollection($clients);
    }
}
