<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Jobs\SendEmailNotification;
use App\Mail\OrderCancelled;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PromoCodeService;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService  $orderService,
        private readonly CartService   $cartService,
        private readonly PaymentService $paymentService,
        private readonly PromoCodeService $promoCodeService,
    ) {}

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())->latest()->paginate(10);
        return view('client.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items.product', 'statusHistories');
        return view('client.orders.show', compact('order'));
    }

    public function checkout()
    {
        $items = $this->cartService->items();
        if ($items->isEmpty()) {
            return redirect()->route('client.cart')->with('error', 'Votre panier est vide.');
        }

        $total           = $this->cartService->total();
        $deliveryFee     = (float) (\App\Models\Setting::get('delivery_fee', 0));
        $revolutId       = \App\Models\Setting::get('revolut_id', '');
        $paypalLink      = \App\Models\Setting::get('paypal_link', '');
        $availableCredit = auth()->user()->availableCredit();
        $promo           = $this->promoCodeService->currentValidFor($total);
        $promoDiscount   = $this->promoCodeService->discountFor($total, $promo);

        $paymentMethods = [];
        if (\App\Models\Setting::get('payment_stripe_enabled', '1'))  $paymentMethods['stripe']  = ['💳', 'Carte bancaire', 'Paiement sécurisé Stripe'];
        if (\App\Models\Setting::get('payment_bank_enabled', '1'))    $paymentMethods['rib']     = ['🏦', 'Virement bancaire', 'IBAN fourni à la confirmation'];
        if (\App\Models\Setting::get('payment_revolut_enabled', '1') && $revolutId) $paymentMethods['revolut'] = ['🔵', 'Revolut', 'Paiement instantané via Revolut'];
        if (\App\Models\Setting::get('payment_paypal_enabled', '0') && $paypalLink) $paymentMethods['paypal']  = ['🅿️', 'PayPal', 'Paiement via votre compte PayPal'];
        if (\App\Models\Setting::get('payment_cash_enabled', '1'))    $paymentMethods['cash']    = ['💵', 'Espèces', 'Règlement à la livraison / retrait'];

        return view('client.orders.checkout', compact(
            'items', 'total', 'deliveryFee', 'revolutId', 'paypalLink',
            'paymentMethods', 'availableCredit', 'promo', 'promoDiscount'
        ));
    }

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);

        $order->load('user', 'items.product');
        $order->update(['status' => 'cancelled']);

        if (Setting::get('notif_order_cancelled', '1')) {
            SendEmailNotification::dispatch(
                $order->user->email,
                new OrderCancelled($order),
                'annulation commande ' . $order->reference,
            )->onQueue('notifications');
        }

        return back()->with('success', "Commande {$order->reference} annulée.");
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createFromCart(
            auth()->user(),
            $request->validated(),
            $request->payment_method,
            (bool) $request->use_credit,
        );

        if ($order->payment_method === 'stripe') {
            $stripeUrl = $this->paymentService->createStripeCheckout($order);
            return redirect($stripeUrl);
        }

        return redirect()->route('client.orders.show', $order)
            ->with('success', "Commande {$order->reference} passée avec succès !");
    }
}
