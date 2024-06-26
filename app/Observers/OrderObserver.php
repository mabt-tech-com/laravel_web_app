<?php

namespace App\Observers;

use App\Models\Order;

// use App\Notifications\OrderIsConfirmedNotification;
// use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->type == Order::ORDER) {
            // Notification::send($order->student, new OrderIsConfirmedNotification($order));
        }
    }
}
