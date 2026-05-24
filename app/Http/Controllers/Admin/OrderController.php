<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderManualRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\SendEmailNotification;
use App\Mail\PaymentReceived;
use App\Models\Credit;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $allowed   = ['reference', 'total', 'created_at', 'status', 'payment_status'];
        $sort      = in_array($request->sort, $allowed) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $orders = Order::with('user')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->search, fn ($q) => $q->where('reference', 'ilike', "%{$request->search}%"))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy($sort, $direction)
            ->paginate(25)
            ->withQueryString();

        $clients = User::where('role', 'client')->orderBy('name')->get();

        return view('admin.orders.index', compact('orders', 'clients', 'sort', 'direction'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user', 'statusHistories.changedBy', 'creator', 'credits');
        return view('admin.orders.show', compact('order'));
    }

    public function create()
    {
        $clients  = User::orderBy('name')->get();
        $products = Product::active()->with('category')->orderBy('name')->get();
        $me       = auth()->user();
        return view('admin.orders.create', compact('clients', 'products', 'me'));
    }

    public function store(StoreOrderManualRequest $request)
    {
        $order = $this->orderService->createManually(
            auth()->user(),
            User::findOrFail($request->user_id),
            $request->validated(),
            $request->items,
        );

        return redirect()->route('admin.commandes.show', $order)
            ->with('success', "Commande {$order->reference} créée.");
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $this->orderService->updateStatus(
            $order,
            $request->status,
            auth()->id(),
            $request->comment,
        );

        return back()->with('success', 'Statut mis à jour. Notifications envoyées.');
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
            'payment_method' => 'required|in:stripe,revolut,rib,cash',
            'payment_link'   => 'nullable|url',
        ]);

        $oldStatus = $order->payment_status;
        $order->update($request->only(['payment_status', 'payment_method', 'payment_link']));

        // Notifier le client si passage à "payé"
        if ($oldStatus !== 'paid' && $request->payment_status === 'paid'
            && Setting::get('notif_payment_received', '1')) {
            $order->load('user', 'items.product');
            SendEmailNotification::dispatch(
                $order->user->email,
                new PaymentReceived($order),
                'paiement reçu ' . $order->reference,
            )->onQueue('notifications');
        }

        return back()->with('success', 'Paiement mis à jour.');
    }

    public function generateStripeLink(Order $order)
    {
        $url = $this->paymentService->createStripeCheckout($order);
        return back()->with('success', 'Lien Stripe généré.');
    }

    public function editItems(Order $order)
    {
        $order->load('items.product', 'user');
        return view('admin.orders.edit-items', compact('order'));
    }

    public function updateItems(Request $request, Order $order)
    {
        $request->validate([
            'quantities'   => 'required|array',
            'quantities.*' => 'required|integer|min:0',
            'reason'       => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $order) {
            $creditAmount = 0;
            $creditLines  = [];

            $order->load('items.product');

            foreach ($request->quantities as $itemId => $newQty) {
                $item = $order->items->firstWhere('id', $itemId);
                if (!$item) continue;

                $newQty = (int) $newQty;

                if ($newQty === $item->quantity) continue;

                if ($newQty <= 0) {
                    $creditAmount += $item->line_total;
                    $creditLines[] = "{$item->product->name} x{$item->quantity} (supprimé)";
                    $item->delete();
                } else {
                    $diff = $item->quantity - $newQty;
                    $creditAmount += round($diff * $item->unit_price, 2);
                    $creditLines[] = "{$item->product->name} : {$item->quantity} → {$newQty}";
                    $item->update(['quantity' => $newQty]);
                }
            }

            // Recalculate totals
            $order->load('items');
            $subtotal = round($order->items->sum(fn ($i) => $i->unit_price * $i->quantity), 2);
            $total    = max(0, $subtotal + $order->delivery_fee - $order->discount - $order->credit_used);
            $order->update(['subtotal' => $subtotal, 'total' => $total]);

            if ($creditAmount > 0) {
                $reason = $request->reason . "\n" . implode(', ', $creditLines);
                Credit::create([
                    'user_id'   => $order->user_id,
                    'order_id'  => $order->id,
                    'reference' => Credit::generateReference(),
                    'amount'    => $creditAmount,
                    'reason'    => $reason,
                ]);

                session()->flash('success',
                    "Commande modifiée. Avoir de " . number_format($creditAmount, 2, ',', ' ') .
                    " € généré pour {$order->user->name} (réf. AV-)."
                );
            } else {
                session()->flash('success', 'Commande modifiée sans écart de montant.');
            }
        });

        return redirect()->route('admin.commandes.show', $order);
    }

    public function deliveryNote(Order $order)
    {
        $order->load('items.product', 'user');
        $settings = \App\Models\Setting::whereIn('key', ['shop_name', 'shop_email', 'shop_phone', 'shop_address'])
            ->pluck('value', 'key');

        return view('admin.orders.delivery-note', compact('order', 'settings'));
    }

    public function destroy(Order $order)
    {
        if ($order->status !== 'cancelled') {
            return back()->with('error', 'Seules les commandes annulées peuvent être supprimées.');
        }

        $ref = $order->reference;
        $order->forceDelete(); // cascade DB supprime order_items automatiquement

        return redirect()->route('admin.commandes.index')
            ->with('success', "Commande {$ref} supprimée.");
    }

    public function purge(Request $request)
    {
        $request->validate([
            'older_than_days' => 'nullable|integer|min:0|max:3650',
        ]);

        $days = (int) ($request->older_than_days ?? 0);

        $query = Order::where('status', 'cancelled');
        if ($days > 0) {
            $query->where('created_at', '<', now()->subDays($days));
        }

        $count = $query->count();
        $query->each(fn ($order) => $order->forceDelete());

        return redirect()->route('admin.commandes.index')
            ->with('success', "{$count} commande(s) annulée(s) supprimée(s) définitivement.");
    }
}
