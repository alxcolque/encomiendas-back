<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // <-- Importante
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        // Los datos públicos se envían automáticamente al socket
        $this->order = $order;
    }

    public function broadcastOn(): array
    {
        // El canal debe coincidir con el del frontend (admin-orders)
        return [new Channel('admin-orders')];
    }

    public function broadcastAs(): string
    {
        // El nombre del evento (el punto en el front es para esto)
        return 'order.created';
    }
}