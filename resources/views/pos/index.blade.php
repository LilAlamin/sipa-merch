@extends('layouts.pos')

@section('title', 'SIPA Merch POS - Create Transaction')

@push('styles')
<style>
@media print {
    body.mini-invoice-printing * {
        visibility: hidden !important;
    }
    body.mini-invoice-printing #mini-invoice-card,
    body.mini-invoice-printing #mini-invoice-card * {
        visibility: visible !important;
    }
    body.mini-invoice-printing #mini-invoice-card {
        position: fixed !important;
        left: 50% !important;
        top: 0 !important;
        transform: translateX(-50%) !important;
        width: 100% !important;
        max-width: 72mm !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
    }
}
</style>
@endpush

@section('content')
<div x-data="posApp()" class="flex-1 flex flex-col h-full overflow-hidden bg-[#f4f5f7]">
    
    <!-- Top Header Bar (Matching Dribbble Outvetch POS) -->
    <header class="bg-white border-b border-slate-200/80 px-5 sm:px-8 py-3.5 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            
            <!-- Left: Title & OTS/PO Mode Switcher -->
            <div class="flex flex-wrap items-center gap-4">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Create Transaction</h1>
                
                <!-- Channel Switcher Pills -->
                <div class="flex items-center bg-[#f4f5f7] p-1 rounded-xl border border-slate-200/80 text-xs">
                    <button type="button" 
                            @click="setChannel('ots')"
                            :class="channel === 'ots' 
                                ? 'bg-white text-slate-950 font-bold shadow-xs' 
                                : 'text-slate-500 hover:text-slate-900 font-medium'"
                            class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="channel === 'ots' ? 'bg-[#e63946]' : 'bg-slate-300'"></span>
                        <span>On The Spot (OTS)</span>
                    </button>
                    <button type="button" 
                            @click="setChannel('po')"
                            :class="channel === 'po' 
                                ? 'bg-white text-slate-950 font-bold shadow-xs' 
                                : 'text-slate-500 hover:text-slate-900 font-medium'"
                            class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="channel === 'po' ? 'bg-[#d4af37]' : 'bg-slate-300'"></span>
                        <span>Pre-Order (PO)</span>
                    </button>
                </div>
            </div>

            <!-- Right: Action Bar (Sync, Settings, Cashier Team, Profile) -->
            <div class="flex items-center gap-2.5">
                <!-- Sync / Refresh Button -->
                <button type="button" @click="search = ''; category = 'all'" title="Refresh" class="w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>

                <!-- Riwayat Transaksi -->
                <a href="{{ route('pos.history') }}" title="Riwayat Transaksi" class="w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </a>

                <!-- Pengaturan Stok & Produk Button -->
                <a href="{{ route('pos.settings') }}" title="Pengaturan Stok & Produk" class="w-9 h-9 rounded-full border border-slate-200 bg-white hover:bg-slate-50 flex items-center justify-center text-slate-600 hover:text-[#e63946] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </a>

                <!-- Staff Avatar Stack -->
                <div class="hidden md:flex items-center -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center text-[10px] font-bold text-slate-700">KR</div>
                    <div class="w-8 h-8 rounded-full bg-emerald-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-emerald-800">SP</div>
                    <div class="w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-amber-800">+3</div>
                </div>

                <!-- Live Booth Indicator Pill -->
                <div class="hidden sm:flex items-center gap-2 border border-slate-200 bg-white rounded-full px-3 py-1 text-xs font-semibold text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Booth Solo</span>
                </div>

                <!-- User Profile -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-[#e63946] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                        KS
                    </div>
                    <span class="text-xs font-bold text-slate-800 hidden sm:inline">Kasir SIPA</span>
                </div>
            </div>

        </div>
    </header>

    <!-- Main Workspace (Catalog on Left, Detail Transaction on Right) -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- LEFT COLUMN: Categories & Product Grid -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto px-5 sm:px-8 py-5">
            
            <!-- Category Filter Bar with Count Badges (Matching Dribbble) -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
                    
                    <!-- All Products -->
                    <button type="button" 
                            @click="category = 'all'" 
                            :class="category === 'all' 
                                ? 'bg-white text-slate-900 font-bold border-slate-300 shadow-xs' 
                                : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent'"
                            class="px-3.5 py-1.5 rounded-full border text-xs whitespace-nowrap transition-all flex items-center gap-2">
                        <span>All Product</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="category === 'all' ? 'bg-[#e63946] text-white' : 'bg-slate-200 text-slate-600'"
                              x-text="products.length">
                        </span>
                    </button>

                    <!-- Apparel -->
                    <button type="button" 
                            @click="category = 'Apparel'" 
                            :class="category === 'Apparel' 
                                ? 'bg-white text-slate-900 font-bold border-slate-300 shadow-xs' 
                                : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent'"
                            class="px-3.5 py-1.5 rounded-full border text-xs whitespace-nowrap transition-all flex items-center gap-2">
                        <span>Apparel</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="category === 'Apparel' ? 'bg-[#e63946] text-white' : 'bg-slate-200 text-slate-600'"
                              x-text="products.filter(p => p.category === 'Apparel').length">
                        </span>
                    </button>

                    <!-- Aksesoris -->
                    <button type="button" 
                            @click="category = 'Aksesoris'" 
                            :class="category === 'Aksesoris' 
                                ? 'bg-white text-slate-900 font-bold border-slate-300 shadow-xs' 
                                : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent'"
                            class="px-3.5 py-1.5 rounded-full border text-xs whitespace-nowrap transition-all flex items-center gap-2">
                        <span>Aksesoris</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="category === 'Aksesoris' ? 'bg-[#e63946] text-white' : 'bg-slate-200 text-slate-600'"
                              x-text="products.filter(p => p.category === 'Aksesoris').length">
                        </span>
                    </button>

                    <!-- Merchandise -->
                    <button type="button" 
                            @click="category = 'Merchandise'" 
                            :class="category === 'Merchandise' 
                                ? 'bg-white text-slate-900 font-bold border-slate-300 shadow-xs' 
                                : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent'"
                            class="px-3.5 py-1.5 rounded-full border text-xs whitespace-nowrap transition-all flex items-center gap-2">
                        <span>Pin & Stiker</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="category === 'Merchandise' ? 'bg-[#e63946] text-white' : 'bg-slate-200 text-slate-600'"
                              x-text="products.filter(p => p.category === 'Merchandise').length">
                        </span>
                    </button>

                    <!-- Bundling -->
                    <button type="button" 
                            @click="category = 'Paket Bundling'" 
                            :class="category === 'Paket Bundling' 
                                ? 'bg-white text-slate-900 font-bold border-slate-300 shadow-xs' 
                                : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent'"
                            class="px-3.5 py-1.5 rounded-full border text-xs whitespace-nowrap transition-all flex items-center gap-2">
                        <span>Bundling</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="category === 'Paket Bundling' ? 'bg-[#e63946] text-white' : 'bg-slate-200 text-slate-600'"
                              x-text="products.filter(p => p.category === 'Paket Bundling').length">
                        </span>
                    </button>

                    <!-- Limited Drop -->
                    <button type="button" 
                            @click="category = 'Limited Drop'" 
                            :class="category === 'Limited Drop' 
                                ? 'bg-white text-slate-900 font-bold border-slate-300 shadow-xs' 
                                : 'bg-transparent text-slate-500 hover:text-slate-800 border-transparent'"
                            class="px-3.5 py-1.5 rounded-full border text-xs whitespace-nowrap transition-all flex items-center gap-2">
                        <span>Limited</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                              :class="category === 'Limited Drop' ? 'bg-[#e63946] text-white' : 'bg-slate-200 text-slate-600'"
                              x-text="products.filter(p => p.category === 'Limited Drop').length">
                        </span>
                    </button>

                </div>

                <!-- Search Input Pill -->
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           x-model="search" 
                           placeholder="Search merchandise..." 
                           class="w-full bg-white border border-slate-200/90 rounded-full pl-9 pr-7 py-1.5 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-400 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <button x-show="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">✕</button>
                </div>
            </div>

            <!-- Product Cards Grid (3 Columns on Desktop/Tablet) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-24 lg:pb-6">
                <template x-for="product in filteredProducts" :key="product.id">
                    
                    <!-- Dribbble Retail Product Card Style -->
                    <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
                        
                        <!-- Top Image / Mockup Preview Container -->
                        <div class="w-full h-36 bg-[#f4f5f7] rounded-xl relative overflow-hidden flex items-center justify-center p-3 cursor-pointer"
                             @click="handleProductClick(product)">
                            
                            <!-- Stock Badge on Top-Left -->
                            <div class="absolute top-2.5 left-2.5 z-10">
                                <span class="bg-[#13161b]/80 backdrop-blur-xs text-white text-[10px] font-semibold px-2 py-0.5 rounded-full font-mono" x-text="product.stock + ' Stock'"></span>
                            </div>

                            <!-- Optional Badge on Top-Right -->
                            <template x-if="product.badge_text">
                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="bg-[#d4af37] text-slate-950 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-2xs" x-text="product.badge_text"></span>
                                </div>
                            </template>

                            <!-- Modern Clean Product Visual Artwork (Image or SVG Fallback) -->
                            <div class="w-full h-full flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                
                                <!-- If Product has uploaded photo -->
                                <template x-if="product.image_url">
                                    <img :src="product.image_url" :alt="product.name" class="w-full h-full object-contain">
                                </template>

                                <!-- Fallback SVG when no photo uploaded -->
                                <template x-if="!product.image_url">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <template x-if="product.category === 'Apparel'">
                                            <!-- Clean T-shirt SVG -->
                                            <svg class="w-24 h-24 text-slate-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.47a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.47a2 2 0 00-1.34-2.23z"/>
                                            </svg>
                                        </template>

                                        <template x-if="product.category === 'Aksesoris'">
                                            <!-- Clean Keychain SVG -->
                                            <svg class="w-20 h-20 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="7.5" cy="15.5" r="5.5"/>
                                                <path d="m21 2-9.6 9.6M15.5 7.5l3 3M18 5l2 2"/>
                                            </svg>
                                        </template>

                                        <template x-if="product.category === 'Merchandise'">
                                            <!-- Clean Pin / Sticker SVG -->
                                            <svg class="w-20 h-20 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="m9 12 2 2 4-4"/>
                                            </svg>
                                        </template>

                                        <template x-if="product.category === 'Paket Bundling'">
                                            <!-- Clean Gift Box / Bundle SVG -->
                                            <svg class="w-20 h-20 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                                <path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>
                                            </svg>
                                        </template>

                                        <template x-if="product.category === 'Limited Drop'">
                                            <!-- Clean Flame SVG in SIPA Crimson -->
                                            <svg class="w-20 h-20 text-[#e63946]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                                            </svg>
                                        </template>
                                    </div>
                                </template>

                            </div>

                        </div>

                        <!-- Card Info (Title, Description, Price) -->
                        <div class="mt-3">
                            <h3 class="font-bold text-sm text-slate-900 leading-snug line-clamp-1" x-text="product.name"></h3>
                            <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-2 leading-relaxed" x-text="product.description"></p>
                            
                            <div class="mt-2.5">
                                <span class="font-bold text-sm text-slate-900 font-mono" x-text="formatRupiah(product.selling_price)"></span>
                            </div>
                        </div>

                        <!-- Add to Cart Full Width Button -->
                        <button type="button" 
                                @click="handleProductClick(product)"
                                class="w-full mt-3 py-2 rounded-xl bg-white hover:bg-slate-900 text-slate-700 hover:text-white border border-slate-200 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-2xs active:scale-[0.99]">
                            <span>+ Add to Cart</span>
                        </button>

                    </div>
                </template>
            </div>

        </div>

        <!-- RIGHT COLUMN: Detail Transaction Panel (Matching Dribbble Right Drawer) -->
        <div class="hidden lg:flex w-[380px] xl:w-[410px] bg-white border-l border-slate-200/80 flex-col justify-between shadow-xs shrink-0">
            
            <!-- Detail Transaction Header -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-extrabold text-base text-slate-900 tracking-tight">Detail Transaction</h2>
                
                <button type="button" 
                        @click="clearCart()" 
                        x-show="cart.length > 0"
                        class="text-xs font-semibold text-rose-500 hover:text-rose-600 flex items-center gap-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Reset Order</span>
                </button>
            </div>

            <!-- Cart Items List (Soft Grey Cards) -->
            <div class="flex-1 overflow-y-auto p-5 space-y-3">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <p class="font-bold text-slate-700 text-sm">No items in transaction</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">Select merchandise items from the catalog on the left to add.</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="index">
                    <!-- Dribbble Cart Item Card Style -->
                    <div class="bg-[#f8f9fa] rounded-2xl p-3 border border-slate-100/90 flex items-center gap-3 relative group">
                        
                        <!-- Mini Thumbnail -->
                        <div class="w-14 h-14 bg-white rounded-xl border border-slate-100 p-1.5 flex items-center justify-center shrink-0 overflow-hidden">
                            <template x-if="item.image_url">
                                <img :src="item.image_url" :alt="item.name" class="w-full h-full object-contain">
                            </template>
                            <template x-if="!item.image_url">
                                <div class="w-full h-full flex items-center justify-center">
                                    <template x-if="item.name.toLowerCase().includes('kaos')">
                                        <svg class="w-7 h-7 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.47a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.47a2 2 0 00-1.34-2.23z"/></svg>
                                    </template>
                                    <template x-if="!item.name.toLowerCase().includes('kaos')">
                                        <svg class="w-7 h-7 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/></svg>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Item Title, Variant & Stepper -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1">
                                <h4 class="font-bold text-xs text-slate-900 truncate" x-text="item.name"></h4>
                                
                                <!-- Delete Item Button -->
                                <button type="button" @click="removeFromCart(index)" class="w-5 h-5 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>

                            <!-- Size Variant Pill -->
                            <div class="mt-0.5 flex items-center gap-1.5">
                                <template x-if="item.variant">
                                    <span class="text-[10px] font-mono font-medium text-slate-500 bg-white px-1.5 py-0.2 rounded border border-slate-200" x-text="'Size ' + item.variant"></span>
                                </template>
                            </div>

                            <!-- Stepper & Subtotal Line -->
                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-200/60">
                                <!-- Stepper with SIPA Red Plus -->
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="decreaseQty(index)" class="w-5 h-5 rounded-full bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs shadow-2xs">-</button>
                                    <span class="font-mono font-bold text-xs text-slate-900 px-1" x-text="String(item.quantity).padStart(2, '0')"></span>
                                    <button type="button" @click="increaseQty(index)" class="w-5 h-5 rounded-full bg-[#e63946] hover:bg-[#d62828] text-white font-black text-xs flex items-center justify-center shadow-2xs">+</button>
                                </div>

                                <span class="font-mono font-bold text-xs text-slate-900" x-text="'Total ' + formatRupiah(item.price * item.quantity)"></span>
                            </div>

                        </div>

                    </div>
                </template>
            </div>

            <!-- Customer Details for PO Mode -->
            <div x-show="channel === 'po' && cart.length > 0" class="px-5 py-3 bg-[#f8f9fa] border-t border-slate-100 space-y-2">
                <span class="text-xs font-bold text-slate-800 block">Pre-Order Customer Details:</span>
                <input type="text" x-model="customer.name" placeholder="Nama Pemesan *" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                <input type="text" x-model="customer.phone" placeholder="Nomor WhatsApp (08xxxxxxxxxx)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                <textarea x-model="customer.notes" rows="2" placeholder="Keterangan / Catatan PO (opsional)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-900 focus:outline-none focus:border-slate-400 resize-none"></textarea>
            </div>

            <!-- Channel / Promo Status Card (Matching Dribbble Promo Banner) -->
            <div class="px-5 pt-3">
                <div class="bg-[#f4f5f7] border border-slate-200/70 rounded-xl p-2.5 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-slate-700">
                        <span class="font-bold">Kanal:</span>
                        <span class="font-medium" x-text="channel === 'ots' ? 'On The Spot (OTS)' : 'Pre-Order (PO)'"></span>
                    </div>
                    <button type="button" 
                            @click="setChannel(channel === 'ots' ? 'po' : 'ots')"
                            class="px-2.5 py-1 rounded-lg bg-[#e63946] hover:bg-[#d62828] text-white font-bold text-[10px] shadow-2xs transition-colors">
                        Change Mode
                    </button>
                </div>
            </div>

            <!-- Transaction Summary & Continue Button -->
            <div class="p-5 space-y-3">
                
                <div class="space-y-1.5 text-xs text-slate-500 font-medium">
                    <div class="flex justify-between">
                        <span>Sub-Total</span>
                        <span class="font-mono font-bold text-slate-900" x-text="formatRupiah(totalPrice)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Items</span>
                        <span class="font-mono text-slate-700" x-text="totalItems + ' pcs'"></span>
                    </div>
                    <div class="flex justify-between items-baseline pt-2 border-t border-slate-100 text-sm font-bold text-slate-900">
                        <span>Total Payment</span>
                        <span class="font-mono font-black text-lg text-slate-950" x-text="formatRupiah(totalPrice)"></span>
                    </div>
                </div>

                <!-- Payment Method Pill (Matching Dribbble Pill) -->
                <div class="bg-white border border-slate-200 rounded-xl p-2.5 flex items-center justify-between text-xs cursor-pointer hover:border-slate-300"
                     @click="openCheckoutModal()">
                    <div class="flex items-center gap-2 font-bold text-slate-800">
                        <span class="w-3 h-3 rounded-full bg-[#e63946]"></span>
                        <span class="uppercase" x-text="payment.method === 'cash' ? 'Tunai (Cash)' : (payment.method === 'qris' ? 'QRIS Digital' : 'Bank Transfer')"></span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-0.5">
                        <span>Change Method</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </div>

                <!-- Big Signature SIPA Crimson Continue Button -->
                <button type="button" 
                        @click="openCheckoutModal()" 
                        :disabled="cart.length === 0"
                        :class="cart.length === 0 ? 'opacity-40 cursor-not-allowed bg-slate-300 text-slate-500' : 'bg-[#e63946] hover:bg-[#d62828] text-white font-black shadow-md shadow-[#e63946]/20 active:scale-[0.99]'"
                        class="w-full py-3.5 rounded-xl flex items-center justify-center gap-2 text-sm tracking-wide transition-all">
                    <span>Continue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>

            </div>

        </div>

    </div>

    <!-- MOBILE: Floating Bottom Cart Bar -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 p-3.5 bg-white border-t border-slate-200 shadow-lg">
        <div class="flex items-center justify-between gap-3">
            <div class="flex flex-col">
                <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold" x-text="channel === 'ots' ? 'Total OTS (' + totalItems + ' item)' : 'Total PO (' + totalItems + ' item)'"></span>
                <span class="font-mono font-black text-lg text-slate-900" x-text="formatRupiah(totalPrice)"></span>
            </div>
            <button type="button" 
                    @click="mobileDrawerOpen = true" 
                    :disabled="cart.length === 0" 
                    :class="cart.length === 0 ? 'opacity-50 bg-slate-200 text-slate-500' : 'bg-[#e63946] text-white font-bold shadow-sm'" 
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm flex items-center gap-2 transition-all">
                <span>View Order</span>
                <span class="w-5 h-5 rounded-full bg-white/20 text-white text-xs flex items-center justify-center font-bold" x-text="totalItems"></span>
            </button>
        </div>
    </div>

    <!-- MOBILE: Bottom Sheet Drawer -->
    <div x-show="mobileDrawerOpen" 
         x-transition.opacity
         class="lg:hidden fixed inset-0 z-50 bg-black/50 flex flex-col justify-end"
         @click.self="mobileDrawerOpen = false">
        
        <div class="bg-white rounded-t-3xl max-h-[85vh] flex flex-col shadow-2xl"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            
            <div class="p-2.5 flex items-center justify-center">
                <div class="w-10 h-1 rounded-full bg-slate-300"></div>
            </div>

            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Detail Transaction</h3>
                <button type="button" @click="mobileDrawerOpen = false" class="text-slate-400 hover:text-slate-700 text-sm p-1">
                    ✕
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-[#f8f9fa] rounded-xl p-3 border border-slate-100 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-xs text-slate-900" x-text="item.name"></h4>
                            <template x-if="item.variant">
                                <span class="text-[10px] font-mono text-slate-500" x-text="'Size ' + item.variant"></span>
                            </template>
                            <span class="font-mono font-bold text-xs text-slate-900 block mt-0.5" x-text="formatRupiah(item.price * item.quantity)"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="decreaseQty(index)" class="w-6 h-6 rounded-full bg-white border border-slate-200 text-slate-700 font-bold">-</button>
                            <span class="font-mono font-bold text-xs text-slate-900 px-1" x-text="item.quantity"></span>
                            <button type="button" @click="increaseQty(index)" class="w-6 h-6 rounded-full bg-[#e63946] text-white font-black">+</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-4 bg-white border-t border-slate-100 space-y-2.5">
                <div class="flex justify-between items-baseline">
                    <span class="text-xs text-slate-500">Total Payment</span>
                    <span class="font-mono font-black text-xl text-slate-900" x-text="formatRupiah(totalPrice)"></span>
                </div>
                <button type="button" 
                        @click="mobileDrawerOpen = false; openCheckoutModal();"
                        class="w-full py-3 rounded-xl bg-[#e63946] text-white font-black text-sm shadow-md shadow-[#e63946]/20">
                    Continue to Payment
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL 1: Size Variant Selection Modal -->
    <div x-show="variantModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
         @click.self="variantModalOpen = false">
        
        <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-2xl border border-slate-100"
             x-transition.scale>
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-900 text-base">Select Size</h3>
                <button type="button" @click="variantModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <p class="text-xs text-slate-400 mb-4">Choose size for <strong class="text-slate-900" x-text="selectedProductForVariant?.name"></strong>:</p>

            <div class="grid grid-cols-5 gap-2 mb-6">
                <template x-for="v in (selectedProductForVariant?.variants || ['S', 'M', 'L', 'XL', 'XXL'])" :key="v">
                    <button type="button" 
                            @click="selectedVariant = v"
                            :class="selectedVariant === v ? 'bg-[#e63946] text-white font-black border-[#e63946] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'"
                            class="py-2.5 rounded-xl border text-xs font-mono font-bold transition-all"
                            x-text="v">
                    </button>
                </template>
            </div>

            <div class="flex gap-2">
                <button type="button" @click="variantModalOpen = false" class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Cancel
                </button>
                <button type="button" @click="confirmAddWithVariant()" class="flex-1 py-2.5 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white text-xs font-black shadow-xs">
                    Confirm & Add
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Checkout & Payment Modal -->
    <div x-show="checkoutModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @click.self="checkoutModalOpen = false">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl my-8 border border-slate-100"
             x-transition.scale>
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Payment Details</h3>
                    <p class="text-xs text-slate-400" x-text="channel === 'ots' ? 'On The Spot (OTS) Checkout' : 'Pre-Order (PO) Checkout'"></p>
                </div>
                <button type="button" @click="checkoutModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="py-4 space-y-4">
                
                <!-- Total Display Banner -->
                <div class="bg-[#f4f5f7] rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Total Tagihan</span>
                        <span class="text-xs text-slate-600 font-mono" x-text="totalItems + ' item' "></span>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-black text-xl text-slate-900" x-text="formatRupiah(totalPrice)"></span>
                    </div>
                </div>

                <!-- Customer Fields if PO mode -->
                <div x-show="channel === 'po'" class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 space-y-2.5">
                    <span class="text-xs font-bold text-slate-800 block">Customer Information (PO):</span>
                    <div>
                        <label class="text-[10px] text-slate-500 uppercase block mb-1 font-semibold">Nama Lengkap *</label>
                        <input type="text" x-model="customer.name" placeholder="Nama Pemesan" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-500 uppercase block mb-1 font-semibold">Nomor WhatsApp *</label>
                        <input type="text" x-model="customer.phone" placeholder="08xxxxxxxxxx" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase block mb-1 font-semibold">Status Bayar</label>
                            <select x-model="payment.status" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs text-slate-900">
                                <option value="paid">Lunas Penuh</option>
                                <option value="dp">Uang Muka (DP)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-500 uppercase block mb-1 font-semibold">Estimasi Ambil</label>
                            <input type="date" x-model="customer.pickup_date" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs text-slate-900">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-500 uppercase block mb-1 font-semibold">Keterangan / Catatan PO</label>
                        <textarea x-model="customer.notes" rows="2" placeholder="Catatan pesanan, titip teman, request khusus, dll." class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400 resize-none"></textarea>
                    </div>
                </div>

                <!-- Payment Method Toggle -->
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-2">Metode Pembayaran:</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" 
                                @click="setPaymentMethod('cash')"
                                :class="payment.method === 'cash' ? 'bg-[#e63946] text-white font-black border-[#e63946] shadow-xs' : 'bg-[#f8f9fa] text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center transition-all">
                            <span>Tunai</span>
                        </button>
                        <button type="button" 
                                @click="setPaymentMethod('qris')"
                                :class="payment.method === 'qris' ? 'bg-[#e63946] text-white font-black border-[#e63946] shadow-xs' : 'bg-[#f8f9fa] text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center transition-all">
                            <span>QRIS</span>
                        </button>
                        <button type="button" 
                                @click="setPaymentMethod('transfer')"
                                :class="payment.method === 'transfer' ? 'bg-[#e63946] text-white font-black border-[#e63946] shadow-xs' : 'bg-[#f8f9fa] text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center transition-all">
                            <span>Transfer</span>
                        </button>
                    </div>
                </div>

                <!-- Cash Quick Denomination Calculator (Only for Cash) -->
                <div x-show="payment.method === 'cash'" class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="text-xs font-bold text-slate-700 block">Uang Diterima dari Pembeli:</label>
                    
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="payment.amount_paid = totalPrice" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-900 font-mono text-[11px] font-bold">
                            Uang Pas
                        </button>
                        <button type="button" @click="payment.amount_paid = 50000" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono text-[11px]">
                            50.000
                        </button>
                        <button type="button" @click="payment.amount_paid = 100000" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono text-[11px]">
                            100.000
                        </button>
                        <button type="button" @click="payment.amount_paid = 200000" class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono text-[11px]">
                            200.000
                        </button>
                    </div>

                    <div class="relative mt-2">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 font-mono text-xs text-slate-400">Rp</span>
                        <input type="number" 
                               x-model.number="payment.amount_paid" 
                               class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3 py-2 font-mono text-sm font-bold text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>

                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         :class="changeAmount >= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                        <span class="text-xs font-bold" x-text="changeAmount >= 0 ? 'Kembalian:' : 'Uang Kurang:'"></span>
                        <span class="font-mono font-black text-base" x-text="formatRupiah(Math.abs(changeAmount))"></span>
                    </div>
                </div>

                <!-- QRIS Notice Box -->
                <div x-show="payment.method === 'qris'" class="p-3.5 bg-[#f4f5f7] border border-slate-200 rounded-xl text-xs text-slate-800">
                    <p class="font-bold">Pembayaran QRIS</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tunjukkan QR code standee ke pembeli untuk nominal pas <strong class="font-mono text-slate-900" x-text="formatRupiah(totalPrice)"></strong></p>
                </div>

            </div>

            <!-- Submit Action -->
            <div class="pt-3 border-t border-slate-100 flex gap-2">
                <button type="button" @click="checkoutModalOpen = false" class="px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Batal
                </button>
                <button type="button" 
                        @click="submitCheckout()" 
                        :disabled="isSubmitting || (payment.method === 'cash' && changeAmount < 0)"
                        class="flex-1 py-3 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white font-black text-sm shadow-md shadow-[#e63946]/20 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!isSubmitting">
                        <span>Selesaikan Transaksi</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>Memproses...</span>
                    </template>
                </button>
            </div>

        </div>
    </div>

    <!-- MODAL 3: Mini Digital Invoice Modal (Compact 58mm/80mm Slip Size) -->
    <div x-show="miniInvoiceOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @click.self="closeInvoiceAndReset()">
        
        <div class="max-w-[360px] w-full my-6 flex flex-col items-center"
             x-transition.scale>
            
            <div id="mini-invoice-card" class="w-full bg-white text-slate-900 rounded-2xl shadow-2xl p-5 border border-slate-200 font-sans text-xs">
                
                <div class="text-center pb-3 border-b border-dashed border-slate-300">
                    <img src="{{ $logoBase64 ?? asset('images/sipa-logo.png') }}" alt="SIPA Logo" class="h-10 mx-auto object-contain mb-1">
                    <p class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase">Official Merchandise Slip</p>
                    <div class="mt-1 inline-block px-2.5 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase tracking-wider bg-[#e63946]/15 text-[#e63946]">
                        <span x-text="completedOrder?.channel === 'ots' ? 'TRANSAKSI ON THE SPOT' : 'TRANSAKSI PRE-ORDER'"></span>
                    </div>
                </div>

                <div class="py-2.5 border-b border-dashed border-slate-300 text-[11px] font-mono text-slate-600 space-y-0.5">
                    <div class="flex justify-between">
                        <span>No. Faktur:</span>
                        <strong class="text-slate-900" x-text="'#' + completedOrder?.order_number"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Waktu:</span>
                        <span x-text="new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })"></span>
                    </div>
                    <template x-if="completedOrder?.customer_name">
                        <div class="flex justify-between">
                            <span>Pelanggan:</span>
                            <span class="font-semibold text-slate-900" x-text="completedOrder?.customer_name"></span>
                        </div>
                    </template>
                    <template x-if="completedOrder?.customer_phone">
                        <div class="flex justify-between">
                            <span>Kontak:</span>
                            <span x-text="completedOrder?.customer_phone"></span>
                        </div>
                    </template>
                </div>

                <div class="py-2.5 border-b border-dashed border-slate-300">
                    <div class="space-y-2">
                        <template x-for="item in (completedOrder?.items || [])" :key="item.id">
                            <div>
                                <div class="flex justify-between font-semibold text-slate-900">
                                    <span x-text="item.product_name + (item.variant ? ' (' + item.variant + ')' : '')"></span>
                                    <span class="font-mono" x-text="formatRupiah(item.subtotal)"></span>
                                </div>
                                <div class="text-[10px] font-mono text-slate-500">
                                    <span x-text="item.quantity + ' x ' + formatRupiah(item.unit_price)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Metode Bayar:</span>
                        <span class="font-semibold uppercase" x-text="completedOrder?.payment_method"></span>
                    </div>
                    <div class="flex justify-between items-baseline pt-1">
                        <span class="font-bold text-slate-900">TOTAL:</span>
                        <span class="font-mono font-bold text-sm text-slate-900" x-text="formatRupiah(completedOrder?.total_price || 0)"></span>
                    </div>
                    <template x-if="completedOrder?.payment_method === 'cash'">
                        <div class="pt-1 text-[11px] font-mono text-slate-600 space-y-0.5">
                            <div class="flex justify-between">
                                <span>Bayar:</span>
                                <span x-text="formatRupiah(completedOrder?.amount_paid || 0)"></span>
                            </div>
                            <div class="flex justify-between font-semibold text-slate-900">
                                <span>Kembalian:</span>
                                <span x-text="formatRupiah(completedOrder?.change_amount || 0)"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="completedOrder?.channel === 'po'">
                        <div class="pt-1 text-[11px] font-mono text-indigo-700 space-y-0.5">
                            <div class="flex justify-between">
                                <span>Status Bayar:</span>
                                <strong class="uppercase" x-text="completedOrder?.payment_status === 'dp' ? 'Uang Muka (DP)' : 'Lunas'"></strong>
                            </div>
                            <template x-if="completedOrder?.pickup_date">
                                <div class="flex justify-between">
                                    <span>Estimasi Ambil:</span>
                                    <span x-text="new Date(completedOrder?.pickup_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="completedOrder?.customer_notes">
                        <div class="pt-1 text-[10px] text-slate-500 font-sans border-t border-dashed border-slate-300">
                            <strong>Catatan:</strong> <span x-text="completedOrder?.customer_notes"></span>
                        </div>
                    </template>
                </div>

                <div class="text-center pt-3 font-mono">
                    <p class="text-[10px] text-slate-500 font-sans leading-relaxed">Terima kasih atas pembelian merchandise resmi SIPA Festival!</p>
                    <p class="text-[10px] text-slate-400 font-sans mt-0.5">Instagram: @sipafestival</p>
                </div>

            </div>

            <!-- Action Buttons for Mini Invoice -->
            <div class="w-full mt-3 flex flex-col gap-2">
                <!-- Custom WhatsApp Phone Input if not provided during checkout -->
                <template x-if="!completedOrder?.customer_phone">
                    <div class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 space-y-1">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kirim Struk ke No. WhatsApp:</span>
                        <div class="flex gap-1.5">
                            <input type="tel" 
                                   x-model="customWaPhone" 
                                   placeholder="08xxxxxxxxxx" 
                                   class="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono text-slate-900 focus:outline-none focus:border-emerald-500">
                            <button type="button" 
                                    @click="openWhatsAppFlow(customWaPhone)"
                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg whitespace-nowrap shadow-2xs cursor-pointer">
                                <span>Kirim WA</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Primary: Copy Image for 1-Click Paste into WhatsApp -->
                <button type="button" 
                        @click="copyMiniInvoiceImage()"
                        :disabled="isCopyingImg"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-all active:scale-[0.99] cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span x-text="copiedImg ? '✓ Gambar Struk Tersalin! Paste (Ctrl+V) di WA' : 'Salin Gambar Struk (Paste di WA)'">Salin Gambar Struk (Paste di WA)</span>
                </button>

                <!-- Secondary: Open WhatsApp with Formatted Text -->
                <button type="button" 
                        @click="openWhatsAppFlow()"
                        class="w-full py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>Kirim Teks Struk via WhatsApp</span>
                </button>

                <!-- Secondary Actions: PDF & PNG Download -->
                <div class="grid grid-cols-2 gap-2">
                    <!-- Download PDF Button -->
                    <button type="button" 
                            @click="downloadMiniInvoicePdf()"
                            :disabled="isExportingPdf"
                            class="py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition-colors disabled:opacity-50 cursor-pointer">
                        <svg x-show="!isExportingPdf" class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <svg x-cloak x-show="isExportingPdf" class="w-3.5 h-3.5 animate-spin text-rose-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isExportingPdf ? 'Memproses PDF...' : 'Download PDF'">Download PDF</span>
                    </button>

                    <!-- Download PNG Image Button -->
                    <button type="button" 
                            @click="downloadMiniInvoiceImage()"
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
                            @click="printMiniInvoice()"
                            class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Cetak Struk</span>
                    </button>

                    <!-- Copy Text Button -->
                    <button type="button" 
                            @click="copyMiniInvoiceText()"
                            class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        <span x-text="copied ? '✓ Tersalin' : 'Salin Teks'">Salin Teks</span>
                    </button>
                </div>

                <!-- + Transaksi Baru Button -->
                <button type="button" 
                        @click="closeInvoiceAndReset()"
                        class="w-full py-2.5 mt-1 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white font-bold text-xs flex items-center justify-center gap-1 shadow-md shadow-[#e63946]/20 transition-all active:scale-[0.99] cursor-pointer">
                    <span>+ Transaksi Baru</span>
                </button>

                <!-- Dedicated Invoice Page Link -->
                <a :href="'/orders/' + completedOrder?.id + '/invoice'" 
                   target="_blank"
                   class="text-[11px] text-center text-slate-400 hover:text-slate-600 transition-colors pt-0.5">
                    Buka Halaman Invoice Penuh &rarr;
                </a>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function posApp() {
    return {
        channel: 'ots', // 'ots' or 'po'
        search: '',
        category: 'all',
        products: @json($products),
        cart: [],
        
        customer: {
            name: '',
            phone: '',
            notes: '',
            pickup_date: ''
        },
        
        payment: {
            method: 'cash',
            status: 'paid',
            amount_paid: 0
        },

        mobileDrawerOpen: false,
        variantModalOpen: false,
        checkoutModalOpen: false,
        miniInvoiceOpen: false,
        
        selectedProductForVariant: null,
        selectedVariant: 'L',
        
        isSubmitting: false,
        completedOrder: null,
        completedWaUrl: '#',
        completedWaText: '',
        customWaPhone: '',
        isExportingPdf: false,
        isExportingImg: false,
        isCopyingImg: false,
        copied: false,
        copiedImg: false,

        init() {
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.variantModalOpen = false;
                    this.checkoutModalOpen = false;
                    this.miniInvoiceOpen = false;
                    this.mobileDrawerOpen = false;
                }
            });
        },

        setChannel(type) {
            this.channel = type;
            if (type === 'ots') {
                this.payment.status = 'paid';
            }
        },

        get filteredProducts() {
            return this.products.filter(item => {
                const matchesCategory = this.category === 'all' || item.category === this.category;
                const matchesSearch = !this.search || 
                    item.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    item.category.toLowerCase().includes(this.search.toLowerCase());
                return matchesCategory && matchesSearch;
            });
        },

        get totalItems() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        get totalPrice() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get changeAmount() {
            return (this.payment.amount_paid || 0) - this.totalPrice;
        },

        handleProductClick(product) {
            if (product.has_variants) {
                this.selectedProductForVariant = product;
                this.selectedVariant = (product.variants && product.variants.length > 0) ? product.variants[0] : 'L';
                this.variantModalOpen = true;
            } else {
                this.addToCart(product);
            }
        },

        confirmAddWithVariant() {
            if (!this.selectedProductForVariant) return;
            this.addToCart(this.selectedProductForVariant, this.selectedVariant);
            this.variantModalOpen = false;
        },

        addToCart(product, variant = null) {
            const existingIndex = this.cart.findIndex(i => i.id === product.id && i.variant === variant);
            if (existingIndex > -1) {
                this.cart[existingIndex].quantity += 1;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.selling_price,
                    cost_price: product.cost_price,
                    quantity: 1,
                    variant: variant,
                    badge_text: product.badge_text,
                    image_url: product.image_url,
                    icon: product.icon
                });
            }

            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message: `${product.name} ${variant ? `(${variant})` : ''} added to transaction`, type: 'success' }
            }));
        },

        increaseQty(index) {
            this.cart[index].quantity += 1;
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity -= 1;
            } else {
                this.removeFromCart(index);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            if (confirm('Reset current order?')) {
                this.cart = [];
            }
        },

        setPaymentMethod(method) {
            this.payment.method = method;
            if (method !== 'cash') {
                this.payment.amount_paid = this.totalPrice;
            }
        },

        openCheckoutModal() {
            if (this.cart.length === 0) return;
            this.payment.amount_paid = this.totalPrice;
            this.checkoutModalOpen = true;
        },

        async submitCheckout() {
            if (this.cart.length === 0) return;
            
            if (this.channel === 'po' && !this.customer.name.trim()) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: 'Nama pemesan wajib diisi untuk Pre-Order!', type: 'error' }
                }));
                return;
            }

            this.isSubmitting = true;

            const payload = {
                channel: this.channel,
                items: this.cart.map(i => ({
                    id: i.id,
                    quantity: i.quantity,
                    variant: i.variant
                })),
                customer_name: this.customer.name || null,
                customer_phone: this.customer.phone || null,
                customer_notes: this.customer.notes || null,
                payment_method: this.payment.method,
                payment_status: this.payment.status,
                amount_paid: this.payment.amount_paid || this.totalPrice,
                pickup_date: this.customer.pickup_date || null
            };

            try {
                const response = await fetch('{{ route('pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    this.completedOrder = data.order;
                    this.completedWaUrl = data.wa_url;
                    this.completedWaText = data.wa_text || '';
                    this.checkoutModalOpen = false;
                    this.miniInvoiceOpen = true;

                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Transaksi berhasil disimpan!', type: 'success' }
                    }));
                } else {
                    alert(data.message || 'Gagal menyimpan transaksi.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memproses pesanan.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async downloadMiniInvoicePdf() {
            if (!this.completedOrder) return;
            this.isExportingPdf = true;
            try {
                if (typeof window.downloadReceiptAsPdf === 'function') {
                    await window.downloadReceiptAsPdf('mini-invoice-card', 'struk-' + this.completedOrder.order_number + '.pdf');
                } else {
                    this.printMiniInvoice();
                }
            } catch (e) {
                console.error('PDF error:', e);
                this.printMiniInvoice();
            } finally {
                this.isExportingPdf = false;
            }
        },

        async downloadMiniInvoiceImage() {
            if (!this.completedOrder) return;
            this.isExportingImg = true;
            try {
                if (typeof window.downloadReceiptAsImage === 'function') {
                    await window.downloadReceiptAsImage('mini-invoice-card', 'struk-' + this.completedOrder.order_number + '.png');
                }
            } catch (e) {
                console.error('Image error:', e);
            } finally {
                this.isExportingImg = false;
            }
        },

        printMiniInvoice() {
            document.body.classList.add('mini-invoice-printing');
            window.print();
            setTimeout(() => {
                document.body.classList.remove('mini-invoice-printing');
            }, 1000);
        },

        copyMiniInvoiceText() {
            const txt = this.completedWaText;
            if (!txt) return;
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
            const ta = document.createElement('textarea');
            ta.value = txt;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },

        async copyMiniInvoiceImage() {
            this.isCopyingImg = true;
            try {
                if (typeof window.copyReceiptAsImage === 'function') {
                    const success = await window.copyReceiptAsImage('mini-invoice-card');
                    if (success) {
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

        openWhatsAppFlow(phone = null) {
            // Auto copy receipt image to clipboard for easy pasting!
            if (typeof window.copyReceiptAsImage === 'function') {
                window.copyReceiptAsImage('mini-invoice-card');
            }

            let rawPhone = phone || this.customWaPhone || this.completedOrder?.customer_phone || '';
            let cleanPhone = rawPhone.replace(/[^0-9]/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }

            const text = encodeURIComponent(this.completedWaText);
            const url = cleanPhone
                ? `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${text}`
                : `https://api.whatsapp.com/send?text=${text}`;

            window.open(url, '_blank');
        },

        closeInvoiceAndReset() {
            this.miniInvoiceOpen = false;
            this.cart = [];
            this.customer = { name: '', phone: '', notes: '', pickup_date: '' };
            this.payment = { method: 'cash', status: 'paid', amount_paid: 0 };
            this.isExportingPdf = false;
            this.isExportingImg = false;
            this.isCopyingImg = false;
            this.copied = false;
            this.copiedImg = false;
            this.customWaPhone = '';
        },

        formatRupiah(number) {
            return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endpush
