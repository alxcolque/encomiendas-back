<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId; // Solo necesitamos el ID para borrarlo del front

    public function __construct($order)
    {
        // Si recibes el modelo completo o solo el ID
        $this->orderId = $order instanceof Order ? $order->id : $order;
    }

    public function broadcastOn(): array
    {
        return [new Channel('admin-orders')];
    }

    public function broadcastAs(): string
    {
        return 'order.deleted';
    }
}