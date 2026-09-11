@extends('layouts.pos')

@section('title', 'SIPA Merch POS - Riwayat & Rekap Transaksi')

@section('content')
<div class="flex-1 flex flex-col h-full overflow-hidden bg-[#f4f5f7]">
    
    <!-- Top Header (Matching Dribbble Outvetch POS) -->
    <header class="bg-white border-b border-slate-200/80 px-5 sm:px-8 py-3.5 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat & Rekap Penjualan</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Laporan per item, omset transaksi kasir, dan status pesanan Pre-Order.</p>
                </div>
            </div>

            <!-- Right Button: + Transaksi Baru (Lime-Green Accent) -->
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.index') }}" 
                   class="px-4 py-2 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-md shadow-[#e63946]/20 transition-all active:scale-[0.99]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Transaksi Baru</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Content Workspace (Scrollable) -->
    <div class="flex-1 overflow-y-auto px-5 sm:px-8 py-6 space-y-6">
        
        <!-- 1. KPI Metric Summary Cards (Outvetch Style) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Total Penjualan -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Penjualan</span>
                    <span class="w-2 h-2 rounded-full bg-[#e63946]"></span>
                </div>
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Dari {{ $totalOrdersCount }} transaksi tercatat
                </div>
            </div>

            <!-- Laba Kotor -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Laba Kotor</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="font-mono font-black text-xl sm:text-2xl text-emerald-600">
                    Rp {{ number_format($totalProfit, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Total HPP: Rp {{ number_format($totalCost, 0, ',', '.') }}
                </div>
            </div>

            <!-- Total Item Terjual -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Item Terjual</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900">
                    {{ number_format($totalItemsSold, 0, ',', '.') }} <span class="text-xs font-sans font-bold text-slate-400">pcs</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Akumulasi seluruh produk
                </div>
            </div>

            <!-- Kanal Penjualan (OTS / PO) -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kanal Penjualan</span>
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                </div>
                <div class="flex items-center gap-2 font-mono font-bold text-base sm:text-lg">
                    <span class="px-2 py-0.5 rounded-lg bg-[#f4f5f7] text-slate-900">{{ $orders->where('channel', 'ots')->count() }} OTS</span>
                    <span class="text-slate-300">/</span>
                    <span class="px-2 py-0.5 rounded-lg bg-[#f4f5f7] text-purple-700">{{ $orders->where('channel', 'po')->count() }} PO</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Pada transaksi aktif
                </div>
            </div>

        </div>

        <!-- 2. Rekap Kuantiti Penjualan Per Item -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900">
                        Rekap Kuantiti Penjualan Per Item
                    </h3>
                    <p class="text-xs text-slate-400">Jumlah kuantiti dan omset dari masing-masing merchandise festival.</p>
                </div>
            </div>

            @if($itemSalesRecap->isEmpty())
                <div class="py-8 text-center text-slate-400 text-xs">
                    Belum ada data barang terjual pada filter ini.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($itemSalesRecap as $recap)
                        <div class="bg-[#f8f9fa] border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900">{{ $recap->product_name }}</h4>
                                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-[#e63946]/15 text-[#e63946]">
                                    {{ $recap->total_qty }} pcs
                                </span>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex justify-between items-baseline text-xs">
                                <span class="text-slate-400 text-[11px]">Omset:</span>
                                <span class="font-mono font-bold text-slate-900">Rp {{ number_format($recap->total_sales, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 3. Filter Bar (Matching Outvetch Category Pills) -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            
            <!-- Channel Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <a href="{{ route('pos.history', array_merge(request()->query(), ['channel' => 'all'])) }}"
                   class="px-4 py-1.5 rounded-full border text-xs font-bold transition-all whitespace-nowrap {{ $channel === 'all' ? 'bg-white text-slate-900 border-slate-300 shadow-xs' : 'bg-transparent text-slate-500 hover:text-slate-900 border-transparent' }}">
                    Semua Transaksi
                </a>
                <a href="{{ route('pos.history', array_merge(request()->query(), ['channel' => 'ots'])) }}"
                   class="px-4 py-1.5 rounded-full border text-xs font-bold transition-all whitespace-nowrap {{ $channel === 'ots' ? 'bg-white text-slate-900 border-slate-300 shadow-xs' : 'bg-transparent text-slate-500 hover:text-slate-900 border-transparent' }}">
                    On The Spot (OTS)
                </a>
                <a href="{{ route('pos.history', array_merge(request()->query(), ['channel' => 'po'])) }}"
                   class="px-4 py-1.5 rounded-full border text-xs font-bold transition-all whitespace-nowrap {{ $channel === 'po' ? 'bg-white text-slate-900 border-slate-300 shadow-xs' : 'bg-transparent text-slate-500 hover:text-slate-900 border-transparent' }}">
                    Pre-Order (PO)
                </a>
            </div>

            <!-- Date Selector & Search Form -->
            <form method="GET" action="{{ route('pos.history') }}" class="flex items-center gap-2">
                <input type="hidden" name="channel" value="{{ $channel }}">

                <select name="date" onchange="this.form.submit()" class="bg-white border border-slate-200/90 rounded-full px-3.5 py-1.5 text-xs text-slate-700 font-semibold focus:outline-none focus:border-slate-400 shadow-2xs">
                    <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>Semua Tanggal</option>
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Hari Ini</option>
                </select>

                <div class="relative flex-1 sm:w-64">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Cari faktur / nama..." 
                           class="w-full bg-white border border-slate-200/90 rounded-full pl-8 pr-3 py-1.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-400 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <button type="submit" class="px-4 py-1.5 rounded-full bg-[#13161b] hover:bg-slate-900 text-white text-xs font-bold transition-colors">
                    Filter
                </button>
            </form>

        </div>

        <!-- 4. Detailed Orders List with Item Breakdown -->
        <div class="space-y-3 pb-8">
            @if($orders->isEmpty())
                <div class="bg-white border border-slate-200/80 rounded-2xl p-12 text-center text-slate-400 shadow-xs">
                    <h3 class="font-bold text-slate-700 text-sm">Belum Ada Transaksi</h3>
                    <p class="text-xs text-slate-400 mt-1">Belum ditemukan transaksi yang sesuai dengan kriteria filter.</p>
                </div>
            @else
                @foreach($orders as $order)
                    <div x-data="{ expanded: false }" class="bg-white border border-slate-200/80 hover:border-slate-300 rounded-2xl p-4 sm:p-5 transition-all shadow-xs">
                        
                        <!-- Main Order Header Line -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            
                            <div class="flex items-start sm:items-center gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono font-bold text-sm sm:text-base text-slate-900">#{{ $order->order_number }}</span>
                                        
                                        <!-- Channel Badge -->
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $order->channel === 'ots' ? 'bg-[#e63946]/15 text-[#e63946] border border-[#e63946]/30' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $order->channel === 'ots' ? 'OTS' : 'Pre-Order' }}
                                        </span>

                                        <!-- Payment Status Badge -->
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($order->payment_status === 'dp' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                            {{ $order->payment_status }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400 mt-1">
                                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                                        <span>•</span>
                                        <span>Metode: {{ strtoupper($order->payment_method) }}</span>
                                        @if($order->customer_name)
                                            <span>•</span>
                                            <span class="text-slate-800 font-semibold">{{ $order->customer_name }}</span>
                                        @endif
                                        @if($order->customer_phone)
                                            <span class="text-slate-400 font-mono">({{ $order->customer_phone }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Total & Action Buttons -->
                            <div class="flex items-center justify-between lg:justify-end gap-3 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                <div class="text-left lg:text-right">
                                    <span class="text-[10px] text-slate-400 uppercase block font-semibold">Total</span>
                                    <span class="font-mono font-black text-base sm:text-lg text-slate-900">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- Expand Items Toggle -->
                                    <button type="button" 
                                            @click="expanded = !expanded" 
                                            class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 transition-colors">
                                        <span x-text="expanded ? 'Tutup' : 'Rincian (' + {{ $order->items->count() }} + ')'"></span>
                                        <svg class="w-3.5 h-3.5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- Mini Invoice Direct Button -->
                                    <a href="{{ route('pos.invoice', $order) }}" 
                                       target="_blank"
                                       class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold flex items-center gap-1 transition-all border border-slate-200 shadow-2xs">
                                        <span>Struk</span>
                                    </a>
                                </div>
                            </div>

                        </div>

                        <!-- Expandable Section: Rincian Per Item -->
                        <div x-show="expanded" 
                             x-transition.opacity
                             class="mt-3 pt-3 border-t border-slate-100 bg-[#f8f9fa] rounded-xl p-4 space-y-2.5">
                            
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <span>Item yang Dibeli</span>
                                <span>Subtotal</span>
                            </div>

                            <div class="divide-y divide-slate-200/60">
                                @foreach($order->items as $item)
                                    <div class="py-2.5 flex items-center justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-xs sm:text-sm text-slate-900">{{ $item->product_name }}</span>
                                                @if($item->variant)
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-semibold bg-white border border-slate-200 text-slate-700">
                                                        Size: {{ $item->variant }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs font-mono text-slate-400 mt-0.5">
                                                {{ $item->quantity }} pcs × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-mono font-bold text-xs sm:text-sm text-slate-900">
                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                            </span>
                                            <div class="text-[10px] text-emerald-600 font-mono font-medium">
                                                Laba: Rp {{ number_format($item->subtotal - $item->subtotal_cost, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($order->customer_notes)
                                <div class="pt-2 border-t border-slate-200/60 text-xs text-slate-500">
                                    <strong class="text-slate-700">Catatan:</strong> {{ $order->customer_notes }}
                                </div>
                            @endif

                            <!-- If PO, Status quick updater -->
                            @if($order->channel === 'po')
                                <div class="pt-3 border-t border-slate-200/60 flex flex-wrap items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80">
                                    <div class="text-xs text-slate-700">
                                        <span class="font-bold">Status PO:</span>
                                        <span class="ml-1 uppercase font-mono font-semibold">{{ $order->order_status }} ({{ $order->payment_status }})</span>
                                        @if($order->pickup_date)
                                            <span class="ml-2 text-slate-400">• Estimasi: {{ $order->pickup_date->format('d/m/Y') }}</span>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('pos.orders.status', $order) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        @if($order->payment_status !== 'paid')
                                            <input type="hidden" name="payment_status" value="paid">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-2xs">
                                                Tandai Lunas
                                            </button>
                                        @endif
                                        @if($order->order_status !== 'completed')
                                            <input type="hidden" name="order_status" value="completed">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#13161b] hover:bg-slate-900 text-white font-bold text-[11px] shadow-2xs">
                                                Selesai Diambil
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            @endif

                        </div>

                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="pt-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
