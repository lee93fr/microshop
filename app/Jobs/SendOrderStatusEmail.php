<?php
// app/Jobs/SendOrderStatusEmail.php

namespace App\Jobs;

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(public readonly Order $order) {}

    public function handle(): void
    {
        try {
            Mail::to($this->order->user->email)
                ->send(new OrderStatusUpdated($this->order));
        } catch (\Throwable $e) {
            Log::warning("Email statut commande {$this->order->reference} non envoyé : {$e->getMessage()}");
        }
    }
}
