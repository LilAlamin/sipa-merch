@extends('layouts.pos')

@section('title', 'Invoice Digital - #' . $order->order_number)

@section('content')
<div class="flex-1 bg-slate-100 p-4 sm:p-6 lg:p-8 flex flex-col items-center justify-center min-h-[calc(100vh-4rem)] overflow-y-auto">
    
    <!-- Action Bar (Hidden on Print) -->
    <div class="w-full max-w-[360px] mb-3 flex items-center justify-between no-print">
        <a href="{{ route('pos.index') }}" class="flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Kembali ke Kasir</span>
        </a>

        <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-white text-slate-600 border border-slate-200">
            Slip 58/80mm
        </span>
    </div>

    <!-- The Compact Thermal Invoice Slip -->
    <div id="thermal-invoice-slip" class="thermal-receipt-container w-full max-w-[360px] bg-white text-slate-900 rounded-xl shadow-md p-5 sm:p-6 border border-slate-200 font-sans text-xs">
        
        <!-- Header with Official SIPA Logo -->
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <img src="{{ $logoBase64 ?? asset('images/sipa-logo.png') }}" alt="SIPA Logo" class="h-10 mx-auto object-contain mb-1">
            <p class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase">Official Merchandise Slip</p>
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

    <!-- Practical Action Buttons (No-print) -->
    <div class="w-full max-w-[360px] mt-3 flex flex-col gap-2 no-print" x-data="invoiceActions()">
        
        <!-- Primary Action: Copy Image to Clipboard for 1-Click Paste in WhatsApp -->
        <button type="button" 
                @click="copyReceiptImage()"
                :disabled="isCopyingImg"
                class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-all active:scale-[0.99] cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span x-text="copiedImg ? '✓ Gambar Struk Tersalin! Paste (Ctrl+V) di WA' : 'Salin Gambar Struk (Paste di WA)'">Salin Gambar Struk (Paste di WA)</span>
        </button>

        <!-- WhatsApp Direct Link -->
        <a href="{{ $waUrl }}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="w-full py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            <span>Kirim Invoice via WhatsApp</span>
        </a>

        <!-- Secondary Actions: PDF & PNG Download -->
        <div class="grid grid-cols-2 gap-2">
            <!-- Download PDF Button -->
            <button type="button" 
                    @click="handleDownloadPdf()"
                    :disabled="isExportingPdf"
                    class="py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition-colors disabled:opacity-50 cursor-pointer">
                <svg x-show="!isExportingPdf" class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <svg x-cloak x-show="isExportingPdf" class="w-3.5 h-3.5 animate-spin text-rose-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isExportingPdf ? 'Memproses PDF...' : 'Download PDF'">Download PDF</span>
            </button>

            <!-- Download PNG Image Button -->
            <button type="button" 
                    @click="handleDownloadImage()"
                    :disabled="isExportingImg"
                    class="py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-semibold text-xs flex items-center justify-center gap-1.5 border border-slate-300 shadow-2xs transition-colors disabled:opacity-50 cursor-pointer">
                <svg x-show="!isExportingImg" class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <svg x-cloak x-show="isExportingImg" class="w-3.5 h-3.5 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isExportingImg ? 'Memproses...' : 'Simpan Gambar'">Simpan Gambar</span>
            </button>
        </div>

        <!-- Print Thermal & Copy Text Buttons -->
        <div class="grid grid-cols-2 gap-2">
            <!-- Print Thermal Button -->
            <button type="button" 
                    onclick="window.print()"
                    class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Struk</span>
            </button>

            <!-- Copy Text Button -->
            <button type="button" 
                    @click="copyWaText()"
                    class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                <span x-text="copied ? '✓ Tersalin' : 'Salin Teks'">Salin Teks</span>
            </button>
        </div>

        <a href="{{ route('pos.index') }}" 
           class="w-full py-2.5 mt-1 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white font-bold text-xs text-center shadow-md shadow-[#e63946]/20 transition-all active:scale-[0.99]">
            + Transaksi Baru
        </a>

    </div>

</div>

<script>
function invoiceActions() {
    return {
        copied: false,
        copiedImg: false,
        isCopyingImg: false,
        isExportingPdf: false,
        isExportingImg: false,
        async copyReceiptImage() {
            this.isCopyingImg = true;
            try {
                if (typeof window.copyReceiptAsImage === 'function') {
                    const ok = await window.copyReceiptAsImage('thermal-invoice-slip');
                    if (ok) {
                        this.copiedImg = true;
                        setTimeout(() => this.copiedImg = false, 3500);
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isCopyingImg = false;
            }
        },
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
@endsection
