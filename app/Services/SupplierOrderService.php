<?php

namespace App\Services;

use App\Jobs\SendSupplierOrderJob;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SupplierOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class SupplierOrderService
{
    public function generateReference(): string
    {
        $year   = now()->year;
        $prefix = "BF-{$year}-";

        $last = SupplierOrder::where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $next = $last ? ((int) substr($last->reference, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $orderIds, ?string $notes = null): SupplierOrder
    {
        $orders = Order::whereIn('id', $orderIds)->get();

        if ($orders->count() !== count($orderIds)) {
            throw new \RuntimeException('Certaines commandes sont introuvables.');
        }

        return SupplierOrder::create([
            'reference' => $this->generateReference(),
            'order_ids' => $orderIds,
            'status'    => 'draft',
            'notes'     => $notes,
        ]);
    }

    public function generatePdf(SupplierOrder $supplierOrder): string
    {
        $orders       = Order::whereIn('id', $supplierOrder->order_ids)->with('items.product', 'user')->get();
        $consolidated = $this->consolidate($orders);

        $pdf = Pdf::loadView('admin.supplier-orders.pdf', [
            'supplierOrder' => $supplierOrder,
            'orders'        => $orders,
            'consolidated'  => $consolidated,
            'shopName'      => Setting::get('shop_name', 'AlcoGest'),
            'shopEmail'     => Setting::get('shop_email'),
            'supplierName'  => Setting::get('supplier_name'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    public function send(SupplierOrder $supplierOrder, string $via = 'both'): void
    {
        $supplierOrder->update([
            'status'   => 'sent',
            'sent_at'  => now(),
            'sent_via' => $via,
        ]);

        SendSupplierOrderJob::dispatch($supplierOrder, $via)->onQueue('default');
    }

    public function getConsolidatedProducts(SupplierOrder $supplierOrder): Collection
    {
        $orders = Order::whereIn('id', $supplierOrder->order_ids)->with('items.product')->get();

        return $this->consolidate($orders)->values();
    }

    private function consolidate($orders): Collection
    {
        $consolidated = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->product_id;
                if (isset($consolidated[$key])) {
                    $consolidated[$key]['quantity']   += $item->quantity;
                    $consolidated[$key]['total_cost'] += $item->purchase_price * $item->quantity;
                } else {
                    $consolidated[$key] = [
                        'product'    => $item->product,
                        'quantity'   => $item->quantity,
                        'total_cost' => $item->purchase_price * $item->quantity,
                    ];
                }
            }
        }

        return collect($consolidated);
    }
}
