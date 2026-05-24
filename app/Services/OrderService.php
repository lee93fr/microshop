<?php

namespace App\Services;

use App\Jobs\SendEmailNotification;
use App\Jobs\SendOrderStatusEmail;
use App\Jobs\SendOrderStatusSms;
use App\Mail\AdminNewOrder;
use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Credit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private readonly PromoCodeService $promoCodeService) {}


    public function generateReference(): string
    {
        $year   = now()->year;
        $prefix = "CMD-{$year}-";

        $last = Order::withTrashed()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $next = $last ? ((int) substr($last->reference, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function createFromCart(User $client, array $deliveryData, string $paymentMethod, bool $useCredit = false): Order
    {
        $order = DB::transaction(function () use ($client, $deliveryData, $paymentMethod, $useCredit) {
            $cartItems = Cart::where('user_id', $client->id)->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException('Le panier est vide.');
            }

            $subtotal     = $cartItems->sum(fn ($item) => $item->product->sale_price * $item->quantity);
            $deliveryMode = $deliveryData['delivery_mode'] ?? 'home';
            $deliveryFee  = $deliveryMode === 'home'
                ? (float) (\App\Models\Setting::where('key', 'delivery_fee')->value('value') ?? 0)
                : 0;

            // Apply promo code if present in session and still valid
            $promo         = $this->promoCodeService->currentValidFor((float) $subtotal);
            $promoDiscount = $this->promoCodeService->discountFor((float) $subtotal, $promo);
            if ($promo) {
                PromoCode::whereKey($promo->id)->increment('used_count');
            }

            // Apply available credit if requested
            $creditUsed = 0;
            if ($useCredit) {
                $amountDue = $subtotal + $deliveryFee - $promoDiscount;
                $credits   = Credit::availableFor($client->id)->orderBy('created_at')->get();
                foreach ($credits as $credit) {
                    if ($amountDue <= 0) break;
                    $apply = min($credit->remaining, $amountDue);
                    $credit->increment('used_amount', $apply);
                    $creditUsed  += $apply;
                    $amountDue   -= $apply;
                }
            }

            $order = Order::create([
                'reference'            => $this->generateReference(),
                'user_id'              => $client->id,
                'status'               => 'pending',
                'delivery_mode'        => $deliveryMode,
                'delivery_fee'         => $deliveryFee,
                'delivery_address'     => $deliveryData['delivery_address'] ?? null,
                'delivery_city'        => $deliveryData['delivery_city'] ?? null,
                'delivery_postal_code' => $deliveryData['delivery_postal_code'] ?? null,
                'delivery_country'     => $deliveryData['delivery_country'] ?? 'France',
                'notes'                => $deliveryData['notes'] ?? null,
                'payment_method'       => $paymentMethod,
                'payment_status'       => 'unpaid',
                'subtotal'             => $subtotal,
                'discount'             => $promoDiscount,
                'promo_code_id'        => $promo?->id,
                'promo_code_label'     => $promo?->code,
                'credit_used'          => $creditUsed,
                'total'                => max(0, $subtotal + $deliveryFee - $promoDiscount - $creditUsed),
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->product->sale_price,
                    'purchase_price' => $item->product->purchase_price,
                ]);
            }

            Cart::where('user_id', $client->id)->delete();

            $this->recordStatusChange($order, null, null, 'pending', 'Commande créée');

            return $order;
        });

        $this->promoCodeService->forget();
        $this->dispatchOrderCreatedNotifications($order);

        return $order;
    }

    private function dispatchOrderCreatedNotifications(Order $order): void
    {
        $order->load('items.product', 'user');

        if (Setting::get('notif_order_confirmation', '1')) {
            SendEmailNotification::dispatch(
                $order->user->email,
                new OrderConfirmation($order),
                'confirmation commande ' . $order->reference,
            )->onQueue('notifications');
        }

        $adminEmail = Setting::get('admin_notification_email') ?: Setting::get('shop_email');
        if ($adminEmail && Setting::get('notif_admin_new_order', '1')) {
            SendEmailNotification::dispatch(
                $adminEmail,
                new AdminNewOrder($order),
                'nouvelle commande admin ' . $order->reference,
            )->onQueue('notifications');
        }
    }

    public function createManually(User $admin, User $client, array $data, array $items): Order
    {
        return DB::transaction(function () use ($admin, $client, $data, $items) {
            $subtotal     = collect($items)->sum(fn ($i) => $i['unit_price'] * $i['quantity']);
            $discount     = $data['discount'] ?? 0;
            $deliveryFee  = $data['delivery_mode'] === 'pickup' ? 0 : (float) ($data['delivery_fee'] ?? 0);

            $order = Order::create([
                'reference'            => $this->generateReference(),
                'user_id'              => $client->id,
                'created_by'           => $admin->id,
                'status'               => $data['status'] ?? 'pending',
                'delivery_mode'        => $data['delivery_mode'] ?? 'home',
                'delivery_fee'         => $deliveryFee,
                'delivery_address'     => $data['delivery_address'] ?? null,
                'delivery_city'        => $data['delivery_city'] ?? null,
                'delivery_postal_code' => $data['delivery_postal_code'] ?? null,
                'delivery_country'     => $data['delivery_country'] ?? 'France',
                'notes'                => $data['notes'] ?? null,
                'supplier_notes'       => $data['supplier_notes'] ?? null,
                'payment_method'       => $data['payment_method'],
                'payment_status'       => $data['payment_status'] ?? 'unpaid',
                'payment_link'         => $data['payment_link'] ?? null,
                'subtotal'             => $subtotal,
                'discount'             => $discount,
                'total'                => $subtotal + $deliveryFee - $discount,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'purchase_price' => $item['purchase_price'],
                ]);
            }

            $this->recordStatusChange($order, $admin->id, null, $order->status, 'Commande saisie par admin');

            return $order;
        });

        // Notifier l'admin (pas de confirmation client car saisie manuelle)
        $adminEmail = Setting::get('admin_notification_email') ?: Setting::get('shop_email');
        if ($adminEmail && Setting::get('notif_admin_new_order', '1')) {
            $order->load('items.product', 'user');
            SendEmailNotification::dispatch(
                $adminEmail,
                new AdminNewOrder($order),
                'nouvelle commande admin ' . $order->reference,
            )->onQueue('notifications');
        }

        return $order;
    }

    public function updateStatus(Order $order, string $newStatus, ?int $adminId, ?string $comment = null): void
    {
        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $newStatus, $adminId, $oldStatus, $comment) {
            $order->update(['status' => $newStatus]);
            $this->recordStatusChange($order, $adminId, $oldStatus, $newStatus, $comment);
        });

        if (Setting::get('notif_status_update', '1')) {
            SendOrderStatusEmail::dispatch($order)->onQueue('notifications');
        }
        SendOrderStatusSms::dispatch($order)->onQueue('notifications');
    }

    public function recalculateTotals(Order $order): void
    {
        $subtotal = $order->items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $order->update([
            'subtotal' => $subtotal,
            'total'    => $subtotal - $order->discount,
        ]);
    }

    private function recordStatusChange(Order $order, ?int $changedBy, ?string $from, ?string $to, ?string $comment): void
    {
        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'changed_by' => $changedBy,
            'from_status'=> $from,
            'to_status'  => $to,
            'comment'    => $comment,
            'changed_at' => now(),
        ]);
    }
}
