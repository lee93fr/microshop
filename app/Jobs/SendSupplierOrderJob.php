<?php

namespace App\Jobs;

use App\Mail\SupplierOrderMail;
use App\Models\Setting;
use App\Models\SupplierOrder;
use App\Services\SmsService;
use App\Services\SupplierOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSupplierOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly SupplierOrder $supplierOrder,
        public readonly string        $via,
    ) {}

    public function handle(SupplierOrderService $service, SmsService $sms): void
    {
        $supplierEmail = Setting::get('supplier_email');
        $supplierPhone = Setting::get('supplier_phone');

        if (in_array($this->via, ['email', 'both']) && $supplierEmail) {
            $pdf = $service->generatePdf($this->supplierOrder);
            Mail::to($supplierEmail)->send(new SupplierOrderMail($this->supplierOrder, $pdf));
        }

        if (in_array($this->via, ['sms', 'both']) && $supplierPhone) {
            $msg = "Bon de commande {$this->supplierOrder->reference} envoyé. Merci de confirmer.";
            $sms->send($supplierPhone, $msg);
        }
    }
}
