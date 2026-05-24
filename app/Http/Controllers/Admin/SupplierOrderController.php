<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SupplierOrder;
use App\Services\SupplierOrderService;
use Illuminate\Http\Request;

class SupplierOrderController extends Controller
{
    public function __construct(private readonly SupplierOrderService $service) {}

    public function index(Request $request)
    {
        $allowed   = ['reference', 'created_at', 'sent_at', 'status'];
        $sort      = in_array($request->sort, $allowed) ? $request->sort : 'created_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        $supplierOrders = SupplierOrder::query()
            ->when($request->search, fn ($q) => $q->where('reference', 'ilike', "%{$request->search}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('admin.supplier-orders.index', compact('supplierOrders', 'sort', 'direction'));
    }

    public function create()
    {
        $existingOrderIds = SupplierOrder::pluck('order_ids')->flatten()->toArray();

        $orders = Order::with('user')
            ->whereNotIn('id', $existingOrderIds)
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->get();

        return view('admin.supplier-orders.create', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'notes'       => 'nullable|string',
        ]);

        $supplierOrder = $this->service->create($request->order_ids, $request->notes);

        return redirect()->route('admin.supplier-orders.show', $supplierOrder)
            ->with('success', "Bon {$supplierOrder->reference} créé.");
    }

    public function show(SupplierOrder $supplierOrder)
    {
        $orders       = Order::whereIn('id', $supplierOrder->order_ids)->with('items.product', 'user')->get();
        $consolidated = $this->service->getConsolidatedProducts($supplierOrder);

        return view('admin.supplier-orders.show', compact('supplierOrder', 'orders', 'consolidated'));
    }

    public function downloadPdf(SupplierOrder $supplierOrder)
    {
        $pdfContent = $this->service->generatePdf($supplierOrder);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$supplierOrder->reference}.pdf\"",
        ]);
    }

    public function send(Request $request, SupplierOrder $supplierOrder)
    {
        $request->validate(['via' => 'required|in:email,sms,both']);
        $this->service->send($supplierOrder, $request->via);

        return back()->with('success', 'Bon fournisseur envoyé.');
    }

    public function confirm(SupplierOrder $supplierOrder)
    {
        $supplierOrder->update(['status' => 'confirmed']);
        return back()->with('success', 'Bon fournisseur confirmé.');
    }

    public function destroy(SupplierOrder $supplierOrder)
    {
        $ref = $supplierOrder->reference;
        $supplierOrder->delete();

        return redirect()->route('admin.supplier-orders.index')
            ->with('success', "Bon fournisseur {$ref} supprimé.");
    }
}
