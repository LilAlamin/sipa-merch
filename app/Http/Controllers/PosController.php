<?php

namespace App\Http\Controllers;

use App\Exports\SalesExcelExport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $dateFilter = $request->query('date', 'today');
        $search = $request->query('search');

        $query = $this->buildFilteredOrdersQuery($channel, $dateFilter, $search);
        $orders = (clone $query)->paginate(15)->withQueryString();

        // Calculate summary metrics based on current filters
        $metricQuery = $this->buildMetricQuery($channel, $dateFilter, $search);

        $totalRevenue = (clone $metricQuery)->sum('total_price');
        $totalCost = (clone $metricQuery)->sum('total_cost');
        $totalProfit = (clone $metricQuery)->sum('profit');
        $totalOrdersCount = (clone $metricQuery)->count();
        $otsCount = (clone $metricQuery)->where('channel', 'ots')->count();
        $poCount = (clone $metricQuery)->where('channel', 'po')->count();

        // Item sales recap (quantity sold per product)
        $itemSalesRecap = $this->getItemSalesRecap($channel, $dateFilter, $search);
        $totalItemsSold = $itemSalesRecap->sum('total_qty');
        $periodLabel = $this->getDatePeriodLabel($dateFilter);

        return view('pos.history', compact(
            'orders',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'totalOrdersCount',
            'totalItemsSold',
            'otsCount',
            'poCount',
            'itemSalesRecap',
            'channel',
            'dateFilter',
            'search',
            'periodLabel'
        ));
    }

    /**
     * Export the filtered POS report to a styled Excel spreadsheet.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $channel = $request->query('channel', 'all');
        $dateFilter = $request->query('date', 'today');
        $search = $request->query('search');

        $orders = $this->buildFilteredOrdersQuery($channel, $dateFilter, $search)->get();
        $metricQuery = $this->buildMetricQuery($channel, $dateFilter, $search);

        $metrics = [
            'totalRevenue' => (int) (clone $metricQuery)->sum('total_price'),
            'totalCost' => (int) (clone $metricQuery)->sum('total_cost'),
            'totalProfit' => (int) (clone $metricQuery)->sum('profit'),
            'totalOrdersCount' => (int) (clone $metricQuery)->count(),
            'totalItemsSold' => (int) $this->getItemSalesRecap($channel, $dateFilter, $search)->sum('total_qty'),
            'otsCount' => (int) (clone $metricQuery)->where('channel', 'ots')->count(),
            'poCount' => (int) (clone $metricQuery)->where('channel', 'po')->count(),
        ];

        $itemSalesRecap = $this->getItemSalesRecap($channel, $dateFilter, $search);
        $periodLabel = $this->getDatePeriodLabel($dateFilter);

        $filters = [
            'channel' => $channel,
            'dateFilter' => $dateFilter,
            'periodLabel' => $periodLabel,
            'search' => $search,
        ];

        return SalesExcelExport::download($orders, $itemSalesRecap, $metrics, $filters);
    }

    /**
     * Build filtered orders query with items and product relations.
     */
    private function buildFilteredOrdersQuery(string $channel, string $dateFilter, ?string $search)
    {
        $query = Order::with('items.product')->latest();

        if ($channel === 'ots') {
            $query->ots();
        } elseif ($channel === 'po') {
            $query->po();
        }

        $this->applyDateFilter($query, $dateFilter);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Build base query for summary metric calculations.
     */
    private function buildMetricQuery(string $channel, string $dateFilter, ?string $search)
    {
        $metricQuery = Order::query();

        if ($channel === 'ots') {
            $metricQuery->ots();
        } elseif ($channel === 'po') {
            $metricQuery->po();
        }

        $this->applyDateFilter($metricQuery, $dateFilter);

        if ($search) {
            $metricQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        return $metricQuery;
    }

    /**
     * Apply date constraints to an Order query builder.
     */
    private function applyDateFilter($query, string $dateFilter): void
    {
        if ($dateFilter === 'today') {
            $query->today();
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($dateFilter === 'week') {
            $query->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay());
        } elseif ($dateFilter === 'month') {
            $query->where('created_at', '>=', Carbon::today()->startOfMonth());
        } elseif ($dateFilter === 'all') {
            // No filter applied
        } else {
            try {
                $date = Carbon::parse($dateFilter)->toDateString();
                $query->whereDate('created_at', $date);
            } catch (\Throwable $th) {
                $query->today();
            }
        }
    }

    /**
     * Get itemized sales recap per product with revenue, cost and profit.
     */
    private function getItemSalesRecap(string $channel, string $dateFilter, ?string $search)
    {
        return OrderItem::query()
            ->select(
                'product_name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_sales'),
                DB::raw('SUM(subtotal_cost) as total_cost'),
                DB::raw('(SUM(subtotal) - SUM(subtotal_cost)) as total_profit')
            )
            ->whereHas('order', function ($q) use ($channel, $dateFilter, $search) {
                if ($channel === 'ots') {
                    $q->ots();
                } elseif ($channel === 'po') {
                    $q->po();
                }
                $this->applyDateFilter($q, $dateFilter);
                if ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%");
                    });
                }
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->get();
    }

    /**
     * Generate user-friendly Indonesian period label.
     */
    private function getDatePeriodLabel(string $dateFilter): string
    {
        if ($dateFilter === 'today') {
            return 'Hari Ini ('.Carbon::today()->locale('id')->isoFormat('D MMMM Y').')';
        } elseif ($dateFilter === 'yesterday') {
            return 'Kemarin ('.Carbon::yesterday()->locale('id')->isoFormat('D MMMM Y').')';
        } elseif ($dateFilter === 'week') {
            return '7 Hari Terakhir ('.Carbon::today()->subDays(6)->locale('id')->isoFormat('D MMM').' - '.Carbon::today()->locale('id')->isoFormat('D MMM Y').')';
        } elseif ($dateFilter === 'month') {
            return 'Bulan Ini ('.Carbon::today()->locale('id')->isoFormat('MMMM Y').')';
        } elseif ($dateFilter === 'all') {
            return 'Semua Riwayat Transaksi (All Time)';
        }

        try {
            return 'Tanggal '.Carbon::parse($dateFilter)->locale('id')->isoFormat('D MMMM Y');
        } catch (\Throwable $th) {
            return 'Tanggal '.$dateFilter;
        }
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
     * Display public, standalone e-receipt for customers (accessible via WhatsApp link without POS menu).
     */
    public function publicReceipt(Order $order): View
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

        return view('pos.public-receipt', compact('order', 'waText', 'waUrl', 'logoBase64'));
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

        // Only include invoice URL if not localhost/127.0.0.1, pointing to the secure public customer receipt
        $host = request()->getHost();
        if (! in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            $lines[] = '----------------------------------------';
            $lines[] = '🔗 *Lihat / Unduh Struk Digital:*';
            $lines[] = Route::has('pos.receipt.public')
                ? route('pos.receipt.public', $order)
                : url('/struk/'.$order->id);
        }

        $lines[] = '----------------------------------------';
        $lines[] = 'Terima kasih telah mendukung merchandise resmi SIPA Festival!';
        $lines[] = 'Instagram: @sipafestival';

        return implode("\n", $lines);
    }
}
