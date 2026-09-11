<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0b0f17]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Struk Digital Resmi #{{ $order->order_number }} - SIPA Festival 2026</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body { 
                background: white !important; 
                padding: 0 !important; 
                color: black !important;
            }
            .no-print { 
                display: none !important; 
            }
            #thermal-invoice-slip {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-full bg-[#0b0f17] text-slate-100 font-sans antialiased py-6 sm:py-10 px-4 flex flex-col items-center justify-center selection:bg-[#e63946] selection:text-white">

    <!-- Public Brand Header (Customer Facing) -->
    <div class="w-full max-w-[380px] mb-4 text-center no-print space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-300">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="font-bold tracking-tight">SIPA FESTIVAL 2026</span>
            <span class="text-slate-600">•</span>
            <span class="text-[11px] text-slate-400">Bukti Transaksi Resmi</span>
        </div>
    </div>

    <!-- The Compact Thermal Invoice Slip -->
    <div id="thermal-invoice-slip" class="thermal-receipt-container w-full max-w-[380px] bg-white text-slate-900 rounded-2xl shadow-2xl p-6 sm:p-7 border border-slate-200 font-sans text-xs">
        
        <!-- Header with Official SIPA Logo -->
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <img src="{{ $logoBase64 ?? asset('images/sipa-logo.png') }}" alt="SIPA Logo" class="h-11 mx-auto object-contain mb-1.5">
            <p class="text-[10px] text-slate-500 font-bold tracking-wider uppercase">Official Merchandise Slip</p>
            <div class="mt-1.5 inline-block px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider {{ $order->channel === 'ots' ? 'bg-[#e63946]/15 text-[#e63946] border border-[#e63946]/30' : 'bg-purple-100 text-purple-800' }}">
                {{ $order->channel === 'ots' ? 'Transaksi On The Spot' : 'Transaksi Pre-Order' }}
            </div>
        </div>

        <!-- Meta Details -->
        <div class="py-2.5 border-b border-dashed border-slate-300 font-mono text-[11px] text-slate-600 space-y-0.5">
            <div class="flex justify-between">
                <span>No. Faktur:</span>
                <strong class="text-slate-900">#{{ $order->order_number }}</strong>
            </div>
            <div class="flex justify-between">
                <span>Tanggal:</span>
                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($order->customer_name)
                <div class="flex justify-between">
                    <span>Pelanggan:</span>
                    <strong class="text-slate-900">{{ $order->customer_name }}</strong>
                </div>
            @endif
            @if($order->customer_phone)
                <div class="flex justify-between">
                    <span>No. Kontak:</span>
                    <span>{{ $order->customer_phone }}</span>
                </div>
            @endif
        </div>

        <!-- Itemized Table -->
        <div class="py-2.5 border-b border-dashed border-slate-300">
            <div class="text-[10px] font-semibold uppercase text-slate-400 tracking-wider mb-2 font-mono">RINCIAN ITEM:</div>
            
            <div class="space-y-2">
                @foreach($order->items as $item)
                    <div>
                        <div class="flex justify-between font-semibold text-slate-900">
                            <span>
                                {{ $item->product_name }}
                                @if($item->variant)
                                    <span class="font-normal text-slate-600">({{ $item->variant }})</span>
                                @endif
                            </span>
                            <span class="font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[10px] font-mono text-slate-500 flex justify-between">
                            <span>{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Payment & Totals -->
        <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1 font-mono text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Metode:</span>
                <span class="font-semibold uppercase">{{ $order->payment_method }}</span>
            </div>

            <div class="flex justify-between items-baseline pt-1">
                <span class="font-bold text-slate-900">TOTAL:</span>
                <span class="font-bold text-slate-950 text-sm">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>

            @if($order->payment_method === 'cash')
                <div class="pt-1 border-t border-slate-200 text-[11px] space-y-0.5 text-slate-600">
                    <div class="flex justify-between">
                        <span>Tunai:</span>
                        <span>Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-slate-900">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            @if($order->channel === 'po')
                <div class="pt-1 border-t border-slate-200 text-[11px] text-indigo-700 space-y-0.5">
                    <div class="flex justify-between">
                        <span>Status Bayar:</span>
                        <strong class="uppercase">{{ $order->payment_status === 'dp' ? 'Uang Muka (DP)' : 'Lunas' }}</strong>
                    </div>
                    @if($order->pickup_date)
                        <div class="flex justify-between">
                            <span>Estimasi Ambil:</span>
                            <span>{{ $order->pickup_date->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            @if($order->customer_notes)
                <div class="pt-1.5 text-[10px] text-slate-500 font-sans border-t border-slate-200">
                    <strong>Catatan:</strong> {{ $order->customer_notes }}
                </div>
            @endif
        </div>

        <!-- Footer Notice -->
        <div class="text-center pt-3 font-mono">
            <p class="text-[10px] text-slate-500 font-sans leading-relaxed">
                Terima kasih telah mendukung merchandise resmi SIPA Festival!
            </p>
            <p class="text-[10px] text-slate-400 font-sans mt-0.5">
                Instagram: @sipafestival
            </p>
        </div>

    </div>

    <!-- Hidden Raw Text for Clipboard & Sharing -->
    <textarea id="wa-receipt-text" class="hidden" readonly>{{ $waText }}</textarea>

    <!-- Customer Actions (No POS Navigation Links) -->
    <div class="w-full max-w-[380px] mt-4 flex flex-col gap-2.5 no-print" x-data="invoiceActions()">
        
        <!-- Primary Action: Download PDF -->
        <button type="button" 
                @click="handleDownloadPdf()"
                :disabled="isExportingPdf"
                class="w-full py-2.5 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-[#e63946]/25 transition-all active:scale-[0.99] cursor-pointer">
            <svg x-show="!isExportingPdf" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <svg x-cloak x-show="isExportingPdf" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span x-text="isExportingPdf ? 'Sedang Memproses PDF...' : 'Download Struk PDF'">Download Struk PDF</span>
        </button>

        <!-- Secondary Actions Grid: Simpan Gambar, Cetak, Salin -->
        <div class="grid grid-cols-2 gap-2">
            <!-- Simpan Gambar PNG -->
            <button type="button" 
                    @click="handleDownloadImage()"
                    :disabled="isExportingImg"
                    class="py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 border border-slate-700 transition-colors disabled:opacity-50 cursor-pointer">
                <svg x-show="!isExportingImg" class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <svg x-cloak x-show="isExportingImg" class="w-3.5 h-3.5 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isExportingImg ? 'Menyimpan...' : 'Simpan Gambar'">Simpan Gambar</span>
            </button>

            <!-- Cetak Struk -->
            <button type="button" 
                    onclick="window.print()"
                    class="py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 border border-slate-700 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Struk</span>
            </button>
        </div>

        <!-- Optional: Salin Teks / Bagikan -->
        <button type="button" 
                @click="copyWaText()"
                class="w-full py-2 rounded-xl bg-slate-900 hover:bg-slate-850 text-slate-400 hover:text-white font-medium text-xs flex items-center justify-center gap-1.5 border border-slate-800 transition-colors cursor-pointer">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
            <span x-text="copied ? '✓ Teks Rincian Tersalin!' : 'Salin Teks Ringkasan'">Salin Teks Ringkasan</span>
        </button>

    </div>

    <!-- Official Event Footer -->
    <div class="mt-8 text-center text-slate-500 text-[11px] font-sans no-print space-y-1">
        <p class="font-semibold text-slate-400">Solo International Performing Arts (SIPA) Festival 2026</p>
        <p>Panggung Terbuka Benteng Vastenburg, Surakarta • Official Merchandise</p>
    </div>

    <!-- Toast Notification Container -->
    <div x-data="{
        toasts: [],
        addToast(msg, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, msg, type });
            setTimeout(() => this.removeToast(id), 3500);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }" 
    @show-toast.window="addToast($event.detail.message, $event.detail.type || 'success')"
    class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none no-print">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="pointer-events-auto px-4 py-3 rounded-xl shadow-lg flex items-center gap-2.5 border text-xs sm:text-sm font-semibold bg-[#13161b] border-slate-700 text-white">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span x-text="toast.msg"></span>
            </div>
        </template>
    </div>

<script>
function invoiceActions() {
    return {
        copied: false,
        isExportingPdf: false,
        isExportingImg: false,
        async handleDownloadPdf() {
            this.isExportingPdf = true;
            try {
                if (typeof window.downloadReceiptAsPdf === 'function') {
                    await window.downloadReceiptAsPdf('thermal-invoice-slip', 'struk-{{ $order->order_number }}.pdf');
                } else {
                    window.print();
                }
            } catch (e) {
                console.error('PDF error:', e);
                window.print();
            } finally {
                this.isExportingPdf = false;
            }
        },
        async handleDownloadImage() {
            this.isExportingImg = true;
            try {
                if (typeof window.downloadReceiptAsImage === 'function') {
                    await window.downloadReceiptAsImage('thermal-invoice-slip', 'struk-{{ $order->order_number }}.png');
                }
            } catch (e) {
                console.error('Image error:', e);
            } finally {
                this.isExportingImg = false;
            }
        },
        copyWaText() {
            const txt = document.getElementById('wa-receipt-text').value;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(txt).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2500);
                }).catch(() => this.fallbackCopy(txt));
            } else {
                this.fallbackCopy(txt);
            }
        },
        fallbackCopy(txt) {
            const ta = document.getElementById('wa-receipt-text');
            ta.classList.remove('hidden');
            ta.select();
            document.execCommand('copy');
            ta.classList.add('hidden');
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    };
}
</script>

</body>
</html>
