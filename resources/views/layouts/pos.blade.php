<!DOCTYPE html>
<html lang="id" class="h-full bg-[#13161b]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIPA Merch POS - Create Transaction')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-[#13161b] text-slate-900 font-sans antialiased flex flex-col lg:flex-row overflow-hidden">

    <!-- LEFT: Vertical Dark Sidebar (Exactly like Dribbble) -->
    <aside class="w-full lg:w-[76px] bg-[#13161b] flex lg:flex-col items-center justify-between p-3 sm:px-4 lg:py-6 shrink-0 border-b lg:border-b-0 lg:border-r border-slate-800/80 z-40 no-print">
        
        <!-- Brand Icon (Top with Official SIPA Logo) -->
        <div class="flex items-center gap-3">
            <a href="{{ route('pos.index') }}" class="w-12 h-12 rounded-2xl bg-white p-1.5 flex items-center justify-center shadow-md hover:scale-105 transition-transform" title="SIPA Merch">
                <img src="{{ asset('images/sipa-logo-mark.png') }}" alt="SIPA Official Logo" class="w-full h-full object-contain">
            </a>
            <div class="lg:hidden flex items-center gap-2">
                <span class="font-black text-white text-base tracking-tight">SIPA MERCH</span>
                <span class="text-[10px] font-bold bg-[#e63946] text-white px-1.5 py-0.5 rounded">POS</span>
            </div>
        </div>

        <!-- Middle Navigation Icons -->
        <nav class="flex lg:flex-col items-center gap-2 lg:gap-3 my-auto">
            
            <!-- Kasir POS (Terminal) -->
            <a href="{{ route('pos.index') }}" 
               title="Create Transaction"
               class="w-11 h-11 rounded-xl flex items-center justify-center transition-all {{ request()->routeIs('pos.index') ? 'bg-[#e63946] text-white font-bold shadow-md shadow-[#e63946]/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </a>

            <!-- Riwayat & Rekap Transaksi -->
            <a href="{{ route('pos.history') }}" 
               title="Detail History & Recap"
               class="w-11 h-11 rounded-xl flex items-center justify-center transition-all {{ request()->routeIs('pos.history') || request()->routeIs('pos.invoice') ? 'bg-[#e63946] text-white font-bold shadow-md shadow-[#e63946]/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </a>

            <!-- Pengaturan Stok & Produk (Settings) -->
            <a href="{{ route('pos.settings') }}" 
               title="Pengaturan Stok & Produk"
               class="w-11 h-11 rounded-xl flex items-center justify-center transition-all {{ request()->routeIs('pos.settings*') ? 'bg-[#e63946] text-white font-bold shadow-md shadow-[#e63946]/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </a>
        </nav>

        <!-- Bottom Icons (User Profile / Live Indicator) -->
        <div class="hidden lg:flex flex-col items-center gap-3">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse" title="Booth Online"></div>
            <a href="{{ route('pos.settings') }}" 
               class="w-9 h-9 rounded-full flex items-center justify-center transition-colors {{ request()->routeIs('pos.settings*') ? 'bg-[#e63946] text-white' : 'bg-slate-800 border border-slate-700 text-slate-400 hover:text-white' }}" 
               title="Pengaturan Stok & Foto Produk">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </a>
        </div>

    </aside>

    <!-- MAIN APP CANVAS (Clean Light Background like Dribbble) -->
    <div class="flex-1 bg-[#f4f5f7] flex flex-col min-w-0 h-full overflow-hidden">
        @yield('content')
    </div>

    <!-- Toast Notification Component -->
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
                 class="pointer-events-auto px-4 py-3 rounded-xl shadow-lg flex items-center gap-2.5 border text-xs sm:text-sm font-semibold"
                 :class="{
                     'bg-[#13161b] border-slate-700 text-white': toast.type === 'success',
                     'bg-rose-600 border-rose-700 text-white': toast.type === 'error',
                     'bg-amber-500 border-amber-600 text-slate-950': toast.type === 'warning'
                 }">
                 <svg x-show="toast.type === 'success'" class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                 <svg x-show="toast.type === 'error'" class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                 <span x-text="toast.msg"></span>
            </div>
        </template>
    </div>

    @stack('scripts')
</body>
</html>
