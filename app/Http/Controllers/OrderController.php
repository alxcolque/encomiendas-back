<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Events\OrderDeleted;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderColletion;
use App\Models\Order;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->take(50)->get();
        return response()->json([
            'orders' => new OrderColletion($orders)
        ]);
    }

    public function store(OrderRequest $request)
    {
        $data = $request->validated();
        $order = Order::create($data);
        // Esto envía la orden a Reverb instantáneamente
        broadcast(new OrderCreated($order))->toOthers();

        return (new OrderResource($order))
            ->additional(['message' => '¡Reto logístico publicado!']);
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(OrderRequest $request, Order $order)
    {
        $order->update($request->validated());

        return response()->json([
            'message' => 'Orden actualizada correctamente',
            'order'   => $order
        ]);
    }

    public function destroy(Order $order)
    {
        $order = Order::findOrFail($order->id);
        $order->delete();

        // Avisar a Reverb que se eliminó
        broadcast(new OrderDeleted($order))->toOthers();

        return response()->json(['message' => 'Pedido eliminado']);
    }
}
