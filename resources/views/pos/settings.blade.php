@extends('layouts.pos')

@section('title', 'SIPA Merch POS - Pengaturan Produk & Stok')

@section('content')
<div x-data="settingsApp()" class="flex-1 flex flex-col h-full overflow-hidden bg-[#f4f5f7]">
    
    <!-- Top Header Bar -->
    <header class="bg-white border-b border-slate-200/80 px-5 sm:px-8 py-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#e63946]/10 text-[#e63946] flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan & Stok Merchandise</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola kuantitas stok fisik, upload foto produk resmi, dan atur harga SIPA Merch.</p>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('pos.index') }}" 
                   class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition-colors shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Kasir POS</span>
                </a>
                
                <button type="button" 
                        @click="openAddModal()"
                        class="px-4 py-2 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-md shadow-[#e63946]/20 transition-all active:scale-[0.99]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Tambah Produk</span>
                </button>
            </div>

        </div>
    </header>

    <!-- Scrollable Content Canvas -->
    <div class="flex-1 overflow-y-auto px-5 sm:px-8 py-6 space-y-6">
        
        <!-- Flash Alert Success -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold px-2 py-1">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl space-y-1 shadow-2xs">
                <div class="font-bold text-xs sm:text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Terjadi kesalahan saat memproses data:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 pl-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Total Produk -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Katalog Produk</span>
                    <span class="w-2 h-2 rounded-full bg-[#e63946]"></span>
                </div>
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900">
                    {{ $products->count() }} <span class="text-xs font-sans font-bold text-slate-400">SKU</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Tersedia di Kasir POS
                </div>
            </div>

            <!-- Total Stok Fisik -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Stok Fisik</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="font-mono font-black text-xl sm:text-2xl text-slate-900">
                    {{ number_format($totalStock, 0, ',', '.') }} <span class="text-xs font-sans font-bold text-slate-400">pcs</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Akumulasi seluruh merchandise
                </div>
            </div>

            <!-- Stok Menipis -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Stok Menipis</span>
                    <span class="w-2 h-2 rounded-full {{ $lowStockCount > 0 ? 'bg-amber-500 animate-pulse' : 'bg-slate-300' }}"></span>
                </div>
                <div class="font-mono font-black text-xl sm:text-2xl {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                    {{ $lowStockCount }} <span class="text-xs font-sans font-bold text-slate-400">item</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Sisa stok &le; 10 pcs
                </div>
            </div>

            <!-- Estimasi Nilai Jual -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Estimasi Nilai Stok</span>
                    <span class="w-2 h-2 rounded-full bg-[#d4af37]"></span>
                </div>
                <div class="font-mono font-black text-lg sm:text-xl text-slate-900 truncate">
                    Rp {{ number_format($products->sum(fn($p) => $p->stock * $p->selling_price), 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Berdasarkan harga jual resmi
                </div>
            </div>

        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            
            <!-- Category Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 no-scrollbar">
                <button type="button" 
                        @click="category = 'all'" 
                        :class="category === 'all' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-[#f4f5f7] text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Semua Kategori
                </button>
                <button type="button" 
                        @click="category = 'Apparel'" 
                        :class="category === 'Apparel' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-[#f4f5f7] text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Apparel
                </button>
                <button type="button" 
                        @click="category = 'Aksesoris'" 
                        :class="category === 'Aksesoris' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-[#f4f5f7] text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Aksesoris
                </button>
                <button type="button" 
                        @click="category = 'Merchandise'" 
                        :class="category === 'Merchandise' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-[#f4f5f7] text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Pin & Stiker
                </button>
                <button type="button" 
                        @click="category = 'Paket Bundling'" 
                        :class="category === 'Paket Bundling' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-[#f4f5f7] text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Paket Bundling
                </button>
                <button type="button" 
                        @click="category = 'Limited Drop'" 
                        :class="category === 'Limited Drop' ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-[#f4f5f7] text-slate-600 hover:text-slate-900 font-medium'"
                        class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all">
                    Limited Drop
                </button>
            </div>

            <!-- Search Box & Low Stock Toggle -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="lowStockOnly = !lowStockOnly"
                        :class="lowStockOnly ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 text-slate-600 hover:text-slate-900'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-colors flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full" :class="lowStockOnly ? 'bg-slate-950' : 'bg-amber-500'"></span>
                    <span>Stok &le; 10</span>
                </button>

                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari nama merchandise..." 
                           class="w-full bg-[#f4f5f7] border border-slate-200/80 rounded-xl pl-9 pr-3.5 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-400 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

        </div>

        <!-- Product Table / Cards List -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-[#f8f9fa] border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4">Produk</th>
                            <th class="py-3 px-4">Kategori & Tipe</th>
                            <th class="py-3 px-4 text-center">Stok Fisik</th>
                            <th class="py-3 px-4 text-right">Harga Pokok (HPP)</th>
                            <th class="py-3 px-4 text-right">Harga Jual</th>
                            <th class="py-3 px-4 text-center">Status Foto</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                
                                <!-- Foto & Info Nama -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Thumbnail Container -->
                                        <div class="w-14 h-14 rounded-xl bg-[#f4f5f7] border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 relative group/thumb">
                                            <template x-if="product.image_url">
                                                <img :src="product.image_url" :alt="product.name" class="w-full h-full object-contain p-1">
                                            </template>
                                            <template x-if="!product.image_url">
                                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                    <template x-if="product.category === 'Apparel'">
                                                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.47a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.47a2 2 0 00-1.34-2.23z"/></svg>
                                                    </template>
                                                    <template x-if="product.category !== 'Apparel'">
                                                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/></svg>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Name & Badge -->
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-bold text-sm text-slate-900" x-text="product.name"></span>
                                                <template x-if="product.badge_text">
                                                    <span class="bg-[#d4af37]/20 border border-[#d4af37]/40 text-amber-900 text-[9px] font-bold px-1.5 py-0.2 rounded" x-text="product.badge_text"></span>
                                                </template>
                                            </div>
                                            <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5" x-text="product.description || 'Tanpa deskripsi'"></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kategori & Tipe -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 block w-fit" x-text="product.category"></span>
                                    <span class="text-[10px] font-mono text-slate-400 block mt-1 uppercase" x-text="product.type"></span>
                                </td>

                                <!-- Stok Fisik Counter -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-mono text-sm font-black"
                                         :class="product.stock <= 10 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-100 text-slate-900'">
                                        <span x-text="product.stock"></span>
                                        <span class="text-[10px] font-sans font-normal text-slate-500">pcs</span>
                                    </div>
                                    <template x-if="product.stock <= 10">
                                        <span class="block text-[9px] font-bold text-amber-600 mt-0.5">Menipis!</span>
                                    </template>
                                </td>

                                <!-- HPP -->
                                <td class="py-3.5 px-4 text-right font-mono text-slate-500 whitespace-nowrap">
                                    <span x-text="formatRupiah(product.cost_price)"></span>
                                </td>

                                <!-- Harga Jual -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900" x-text="formatRupiah(product.selling_price)"></span>
                                    <span class="block text-[9px] font-mono text-emerald-600 font-bold" x-text="'+' + formatRupiah(product.selling_price - product.cost_price)"></span>
                                </td>

                                <!-- Status Foto -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <template x-if="product.image_url">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                            <span>●</span> Ada Foto
                                        </span>
                                    </template>
                                    <template x-if="!product.image_url">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500 inline-flex items-center gap-1">
                                            Belum Ada
                                        </span>
                                    </template>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <button type="button" 
                                            @click="openEditModal(product)"
                                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-900 text-slate-700 hover:text-white border border-slate-200 text-xs font-bold transition-all shadow-2xs inline-flex items-center gap-1.5 active:scale-95">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        <span>Edit Stok & Foto</span>
                                    </button>
                                </td>

                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="filteredProducts.length === 0" class="p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="font-bold text-slate-700 text-sm">Tidak ada produk ditemukan</p>
                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau kategori filter.</p>
            </div>

        </div>

    </div>

    <!-- MODAL 1: EDIT STOK, FOTO & HARGA PRODUK -->
    <div x-show="editModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @click.self="editModalOpen = false">
        
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden my-8"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-[#f8f9fa] border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-[#e63946]/10 text-[#e63946] flex items-center justify-center font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Edit Stok & Foto Produk</h3>
                        <p class="text-[11px] text-slate-400" x-text="selectedProduct ? selectedProduct.name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg leading-none p-1">✕</button>
            </div>

            <!-- Form Edit -->
            <form x-show="selectedProduct" 
                  :action="'/settings/products/' + (selectedProduct ? selectedProduct.id : '')" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <!-- 1. FOTO PRODUK UPLOAD & PREVIEW -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700">Foto Merchandise</label>
                    
                    <div class="flex items-center gap-4 p-3 bg-[#f8f9fa] rounded-xl border border-dashed border-slate-300">
                        <!-- Preview Box -->
                        <div class="w-20 h-20 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center relative shrink-0">
                            <template x-if="imagePreviewUrl">
                                <img :src="imagePreviewUrl" class="w-full h-full object-contain p-1" alt="Preview">
                            </template>
                            <template x-if="!imagePreviewUrl && selectedProduct && selectedProduct.image_url">
                                <img :src="selectedProduct.image_url" class="w-full h-full object-contain p-1" alt="Current Image">
                            </template>
                            <template x-if="!imagePreviewUrl && (!selectedProduct || !selectedProduct.image_url)">
                                <div class="text-slate-300 flex flex-col items-center">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-[9px]">Belum ada</span>
                                </div>
                            </template>
                        </div>

                        <!-- Upload Controls -->
                        <div class="flex-1 space-y-2">
                            <div class="text-xs text-slate-500">
                                <span class="font-semibold text-slate-800">Upload gambar baru</span> (JPG, PNG, WebP, Maks 3MB)
                            </div>
                            <input type="file" 
                                   name="image" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   @change="handleFileChange($event)" 
                                   class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#e63946]/10 file:text-[#e63946] hover:file:bg-[#e63946]/20 cursor-pointer">
                            
                            <template x-if="selectedProduct && selectedProduct.image_url">
                                <label class="flex items-center gap-1.5 text-[11px] text-rose-600 cursor-pointer pt-1">
                                    <input type="checkbox" name="remove_image" value="1" class="rounded text-rose-600 focus:ring-0">
                                    <span>Hapus foto saat ini</span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- 2. STOK FISIK DENGAN QUICK ADD BUTTONS -->
                <div class="space-y-1.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-800">Jumlah Stok Fisik (Pcs) *</label>
                        <span class="text-[11px] font-mono text-slate-500">Stok riil di booth</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="number" 
                               name="stock" 
                               x-model.number="editingStock" 
                               min="0" 
                               required 
                               class="w-32 bg-white border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-500 shadow-2xs">
                        
                        <!-- Quick Add Buttons -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" @click="editingStock += 5" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-100">+5</button>
                            <button type="button" @click="editingStock += 10" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-100">+10</button>
                            <button type="button" @click="editingStock += 25" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-100">+25</button>
                            <button type="button" @click="editingStock += 50" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-100">+50</button>
                            <button type="button" @click="editingStock += 100" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 hover:bg-slate-100">+100</button>
                            <button type="button" @click="editingStock = 0" class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold hover:bg-rose-100">0</button>
                        </div>
                    </div>
                </div>

                <!-- 3. NAMA & BADGE -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk *</label>
                        <input type="text" 
                               name="name" 
                               :value="selectedProduct ? selectedProduct.name : ''" 
                               required 
                               class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Teks (Opsional)</label>
                        <input type="text" 
                               name="badge_text" 
                               :value="selectedProduct ? selectedProduct.badge_text : ''" 
                               placeholder="Contoh: Official Tee / Hemat Rp 3.000" 
                               class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <!-- 4. HARGA JUAL & HPP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Resmi (Rp) *</label>
                        <input type="number" 
                               name="selling_price" 
                               :value="selectedProduct ? selectedProduct.selling_price : ''" 
                               min="0" 
                               required 
                               class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Pokok / HPP (Rp) *</label>
                        <input type="number" 
                               name="cost_price" 
                               :value="selectedProduct ? selectedProduct.cost_price : ''" 
                               min="0" 
                               required 
                               class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <!-- 5. DESKRIPSI -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea name="description" 
                              rows="2" 
                              :value="selectedProduct ? selectedProduct.description : ''" 
                              placeholder="Deskripsi bahan, spesifikasi merchandise..." 
                              class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="editModalOpen = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white text-xs font-bold shadow-md shadow-[#e63946]/20 transition-all">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- MODAL 2: TAMBAH PRODUK BARU -->
    <div x-show="addModalOpen" 
         x-transition.opacity
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
         @click.self="addModalOpen = false">
        
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-200 overflow-hidden my-8"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-[#f8f9fa] border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-[#e63946] text-white flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Tambah Merchandise Baru</h3>
                        <p class="text-[11px] text-slate-400">Masukkan data produk merchandise SIPA Festival</p>
                    </div>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg leading-none p-1">✕</button>
            </div>

            <!-- Form Tambah -->
            <form action="{{ route('pos.settings.products.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  class="p-6 space-y-4">
                @csrf

                <!-- Nama & Kategori -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Produk *</label>
                        <input type="text" name="name" required placeholder="Contoh: Tote Bag SIPA 2026" class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori *</label>
                        <select name="category" required class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                            <option value="Apparel">Apparel</option>
                            <option value="Aksesoris">Aksesoris</option>
                            <option value="Merchandise">Pin & Stiker</option>
                            <option value="Paket Bundling">Paket Bundling</option>
                            <option value="Limited Drop">Limited Drop</option>
                        </select>
                    </div>
                </div>

                <!-- Tipe & Stok Awal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Produk *</label>
                        <select name="type" required class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                            <option value="satuan">Produk Satuan</option>
                            <option value="bundling">Paket Bundling</option>
                            <option value="limited">Limited Edition</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Fisik Awal (Pcs) *</label>
                        <input type="number" name="stock" min="0" value="50" required class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <!-- Harga Jual & HPP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual Resmi (Rp) *</label>
                        <input type="number" name="selling_price" min="0" required placeholder="Contoh: 85000" class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Pokok / HPP (Rp) *</label>
                        <input type="number" name="cost_price" min="0" required placeholder="Contoh: 45000" class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-400">
                    </div>
                </div>

                <!-- Foto Upload -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Merchandise (Opsional)</label>
                    <input type="file" 
                           name="image" 
                           accept="image/png,image/jpeg,image/webp" 
                           class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#e63946]/10 file:text-[#e63946] hover:file:bg-[#e63946]/20 cursor-pointer">
                </div>

                <!-- Badge & Deskripsi -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Teks (Opsional)</label>
                    <input type="text" name="badge_text" placeholder="Contoh: Edisi Spesial / Limited 2026" class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Produk</label>
                    <textarea name="description" rows="2" placeholder="Detail spesifikasi merchandise..." class="w-full bg-[#f8f9fa] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-slate-400"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="addModalOpen = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-[#e63946] hover:bg-[#d62828] text-white text-xs font-bold shadow-md shadow-[#e63946]/20 transition-all">
                        + Tambah Produk
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function settingsApp() {
    return {
        products: @json($products),
        search: '',
        category: 'all',
        lowStockOnly: false,
        
        editModalOpen: false,
        addModalOpen: false,
        selectedProduct: null,
        editingStock: 0,
        imagePreviewUrl: null,

        get filteredProducts() {
            return this.products.filter(item => {
                const matchesCategory = this.category === 'all' || item.category === this.category;
                const matchesSearch = !this.search || 
                    item.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    item.category.toLowerCase().includes(this.search.toLowerCase());
                const matchesLowStock = !this.lowStockOnly || item.stock <= 10;
                return matchesCategory && matchesSearch && matchesLowStock;
            });
        },

        openEditModal(product) {
            this.selectedProduct = product;
            this.editingStock = product.stock;
            this.imagePreviewUrl = null;
            this.editModalOpen = true;
        },

        openAddModal() {
            this.addModalOpen = true;
        },

        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreviewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        formatRupiah(number) {
            return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
