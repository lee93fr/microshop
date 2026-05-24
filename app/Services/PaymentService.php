<?php

namespace App\Services;

use App\Jobs\SendEmailNotification;
use App\Mail\PaymentReceived;
use App\Models\Order;
use App\Models\Setting;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Coupon as StripeCoupon;
use Stripe\Stripe;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createStripeCheckout(Order $order): string
    {
        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => (int) ($item->unit_price * 100),
                'product_data' => ['name' => $item->product->name],
            ],
            'quantity' => $item->quantity,
        ])->toArray();

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => route('client.orders.show', $order) . '?payment=success',
            'cancel_url'           => route('client.orders.show', $order) . '?payment=cancelled',
            'metadata'             => ['order_id' => $order->id],
            'customer_email'       => $order->user->email,
        ];

        if ((float) $order->discount > 0) {
            $coupon = StripeCoupon::create([
                'amount_off' => (int) round($order->discount * 100),
                'currency'   => 'eur',
                'name'       => $order->promo_code_label
                    ? "Code promo {$order->promo_code_label}"
                    : 'Remise',
                'duration'   => 'once',
            ]);
            $sessionParams['discounts'] = [['coupon' => $coupon->id]];
        }

        $session = StripeSession::create($sessionParams);

        $order->update([
            'stripe_session_id' => $session->id,
            'payment_link'      => $session->url,
        ]);

        return $session->url;
    }

    public function handleWebhook(string $payload, string $sigHeader): void
    {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('services.stripe.webhook_secret')
        );

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $order   = Order::where('stripe_session_id', $session->id)->first();

            if ($order) {
                $order->update([
                    'payment_status'        => 'paid',
                    'stripe_payment_intent' => $session->payment_intent,
                ]);

                if (Setting::get('notif_payment_received', '1')) {
                    $order->load('user', 'items.product');
                    SendEmailNotification::dispatch(
                        $order->user->email,
                        new PaymentReceived($order),
                        'paiement reçu ' . $order->reference,
                    )->onQueue('notifications');
                }
            }
        }
    }
}
