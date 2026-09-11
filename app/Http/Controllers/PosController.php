<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Display the main POS Terminal.
     */
    public function index(): View
    {
        $products = Product::active()
            ->orderBy('type')
            ->orderBy('id')
            ->get();

        $categories = $products->pluck('category')->unique()->values();

        $todayStats = [
            'total_sales' => Order::today()->sum('total_price'),
            'orders_count' => Order::today()->count(),
            'ots_count' => Order::today()->ots()->count(),
            'po_count' => Order::today()->po()->count(),
        ];

        $logoPath = public_path('images/sipa-logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : asset('images/sipa-logo.png');

        return view('pos.index', compact('products', 'categories', 'todayStats', 'logoBase64'));
    }

    /**
     * Process checkout for OTS or PO orders.
     */
    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'channel' => 'required|in:ots,po',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'customer_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,qris,transfer',
            'payment_status' => 'required|in:paid,dp,unpaid',
            'amount_paid' => 'required|integer|min:0',
            'pickup_date' => 'nullable|date',
        ]);

        if ($validated['channel'] === 'po' && empty($validated['customer_name'])) {
            return response()->json([
                'success' => false,
                'message' => 'Nama pemesan wajib diisi untuk transaksi Pre-Order (PO).',
            ], 422);
        }

        $order = DB::transaction(function () use ($validated): Order {
            $channel = $validated['channel'];
            $datePrefix = Carbon::now()->format('ymd');
            $prefix = 'SIPA-'.strtoupper($channel).'-'.$datePrefix;

            // Generate next sequence number
            $countToday = Order::where('order_number', 'like', $prefix.'%')->count() + 1;
            $orderNumber = $prefix.'-'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);

            $totalPrice = 0;
            $totalCost = 0;
            $orderItemsData = [];

            // Preload products to prevent individual queries
            $productIds = collect($validated['items'])->pluck('id')->all();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($validated['items'] as $itemInput) {
                /** @var Product|null $product */
                $product = $products->get($itemInput['id']);
                if (! $product) {
                    continue;
                }

                $quantity = (int) $itemInput['quantity'];
                $unitPrice = (int) $product->selling_price;
                $costPrice = (int) $product->cost_price;

                $subtotal = $unitPrice * $quantity;
                $subtotalCost = $costPrice * $quantity;

                $totalPrice += $subtotal;
                $totalCost += $subtotalCost;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'cost_price' => $costPrice,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'subtotal_cost' => $subtotalCost,
                    'variant' => $itemInput['variant'] ?? null,
                    'notes' => null,
                ];

                // Decrement stock gracefully
                $product->decrement('stock', $quantity);
            }

            $amountPaid = (int) $validated['amount_paid'];
            $changeAmount = max(0, $amountPaid - $totalPrice);
            $profit = $totalPrice - $totalCost;

            $orderStatus = $channel === 'ots' ? 'completed' : 'pending_pickup';

            $order = Order::create([
                'order_number' => $orderNumber,
                'channel' => $channel,
                'customer_name' => $validated['customer_name'] ?? ($channel === 'ots' ? 'Pelanggan OTS' : null),
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'total_cost' => $totalCost,
                'total_price' => $totalPrice,
                'profit' => $profit,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'order_status' => $orderStatus,
                'pickup_date' => $validated['pickup_date'] ?? null,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });

        $order->load('items.product');

        // Prepare WhatsApp message text
        $waText = $this->generateWhatsAppReceiptText($order);
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $waUrl = $cleanPhone
            ? 'https://api.whatsapp.com/send?phone='.$cleanPhone.'&text='.rawurlencode($waText)
            : 'https://api.whatsapp.com/send?text='.rawurlencode($waText);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'order' => $order,
                'invoice_url' => route('pos.invoice', $order),
                'wa_url' => $waUrl,
                'wa_text' => $waText,
            ]);
        }

        return redirect()->route('pos.invoice', $order)->with('success', 'Transaksi berhasil disimpan!');
    }

    /**
     * Display POS transaction history with itemized breakdowns and metrics.
     */
    public function history(Request $request): View
    {
        $channel = $request->query('channel', 'all');
        $dateFilter = $request->query('date', 'all');
        $search = $request->query('search');

        $query = Order::with('items.product')->latest();

        if ($channel === 'ots') {
            $query->ots();
        } elseif ($channel === 'po') {
            $query->po();
        }

        if ($dateFilter === 'today') {
            $query->today();
        } elseif ($dateFilter && $dateFilter !== 'all') {
            $query->whereDate('created_at', $dateFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Calculate summary metrics based on current filters
        $metricQuery = Order::query();
        if ($channel === 'ots') {
            $metricQuery->ots();
        } elseif ($channel === 'po') {
            $metricQuery->po();
        }
        if ($dateFilter === 'today') {
            $metricQuery->today();
        } elseif ($dateFilter && $dateFilter !== 'all') {
            $metricQuery->whereDate('created_at', $dateFilter);
        }

        $totalRevenue = (clone $metricQuery)->sum('total_price');
        $totalCost = (clone $metricQuery)->sum('total_cost');
        $totalProfit = (clone $metricQuery)->sum('profit');
        $totalOrdersCount = (clone $metricQuery)->count();

        // Item sales recap (quantity sold per product)
        $itemSalesRecap = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->when($dateFilter === 'today', function ($q) {
                $q->whereHas('order', fn ($o) => $o->today());
            })
            ->when($channel !== 'all', function ($q) use ($channel) {
                $q->whereHas('order', fn ($o) => $o->where('channel', $channel));
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->get();

        $totalItemsSold = $itemSalesRecap->sum('total_qty');

        return view('pos.history', compact(
            'orders',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'totalOrdersCount',
            'totalItemsSold',
            'itemSalesRecap',
            'channel',
            'dateFilter',
            'search'
        ));
    }

    /**
     * Update order payment or pickup status (useful for PO).
     */
    public function updateStatus(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => 'nullable|in:paid,dp,unpaid',
            'order_status' => 'nullable|in:completed,pending_pickup,cancelled',
        ]);

        if (isset($validated['payment_status'])) {
            $order->payment_status = $validated['payment_status'];
        }

        if (isset($validated['order_status'])) {
            $order->order_status = $validated['order_status'];
        }

        $order->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui!',
                'order' => $order,
            ]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui!');
    }

    /**
     * Display compact digital mini invoice (58mm/80mm thermal layout).
     */
    public function invoice(Order $order): View
    {
        $order->load('items.product');
        $waText = $this->generateWhatsAppReceiptText($order);

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $waUrl = $cleanPhone
            ? 'https://api.whatsapp.com/send?phone='.$cleanPhone.'&text='.rawurlencode($waText)
            : 'https://api.whatsapp.com/send?text='.rawurlencode($waText);

        $logoPath = public_path('images/sipa-logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : asset('images/sipa-logo.png');

        return view('pos.invoice', compact('order', 'waText', 'waUrl', 'logoBase64'));
    }

    /**
     * Helper to generate clean, formatted WhatsApp invoice text.
     */
    private function generateWhatsAppReceiptText(Order $order): string
    {
        $channelText = $order->channel === 'ots' ? 'ON THE SPOT (OTS)' : 'PRE-ORDER (PO)';
        $dateStr = $order->created_at ? $order->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i');

        $lines = [];
        $lines[] = '*SIPA MERCHANDISE - STRUK PEMBELIAN*';
        $lines[] = 'Solo International Performing Arts';
        $lines[] = '----------------------------------------';
        $lines[] = "No. Faktur : #{$order->order_number}";
        $lines[] = "Waktu      : {$dateStr}";
        $lines[] = "Kanal      : {$channelText}";

        if ($order->customer_name) {
            $lines[] = "Pelanggan  : {$order->customer_name}";
        }
        if ($order->customer_phone) {
            $lines[] = "Kontak     : {$order->customer_phone}";
        }

        $lines[] = '----------------------------------------';
        $lines[] = 'RINCIAN ITEM:';

        foreach ($order->items as $item) {
            $variantText = $item->variant ? " [{$item->variant}]" : '';
            $unitFormatted = 'Rp '.number_format($item->unit_price, 0, ',', '.');
            $subtotalFormatted = 'Rp '.number_format($item->subtotal, 0, ',', '.');
            $lines[] = "• {$item->product_name}{$variantText}";
            $lines[] = "  {$item->quantity}x {$unitFormatted} = *{$subtotalFormatted}*";
        }

        $lines[] = '----------------------------------------';
        $lines[] = '*TOTAL     : Rp '.number_format($order->total_price, 0, ',', '.').'*';
        $lines[] = 'Metode     : '.strtoupper($order->payment_method);

        if ($order->channel === 'po' && $order->payment_status === 'dp') {
            $lines[] = 'Status     : UANG MUKA (DP)';
        } else {
            $lines[] = 'Status     : '.strtoupper($order->payment_status);
        }

        if ($order->payment_method === 'cash') {
            $lines[] = 'Tunai      : Rp '.number_format($order->amount_paid, 0, ',', '.');
            $lines[] = 'Kembalian  : Rp '.number_format($order->change_amount, 0, ',', '.');
        }

        if ($order->customer_notes) {
            $lines[] = 'Catatan    : '.$order->customer_notes;
        }

        // Only include invoice URL if not localhost/127.0.0.1, because localhost cannot be accessed by customer and triggers WhatsApp Web forwarder bug
        $host = request()->getHost();
        if (! in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            $lines[] = '----------------------------------------';
            $lines[] = '🔗 *Lihat / Unduh Struk Digital:*';
            $lines[] = route('pos.invoice', $order);
        }

        $lines[] = '----------------------------------------';
        $lines[] = 'Terima kasih telah mendukung merchandise resmi SIPA Festival!';
        $lines[] = 'Instagram: @sipafestival';

        return implode("\n", $lines);
    }
}
