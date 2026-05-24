<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderStatusSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Order $order) {}

    public function handle(SmsService $sms): void
    {
        $phone = $this->order->user->phone;
        if (!$phone) return;

        try {
            $message = "AlcoGest — Commande {$this->order->reference} : {$this->order->status_label}";
            $sms->send($phone, $message);
        } catch (\Throwable $e) {
            Log::warning("SMS statut commande {$this->order->reference} non envoyé : {$e->getMessage()}");
        }
    }
}
