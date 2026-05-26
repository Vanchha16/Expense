<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ExpenseTrack') — Personal Finance</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .modal-backdrop { backdrop-filter: blur(4px); }
    </style>
</head>
<body class="h-full bg-slate-50 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <!-- ===================== LAYOUT SHELL ===================== -->
    <div class="flex h-screen overflow-hidden">

        <!-- ---- Mobile Overlay ---- -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/60 z-30 lg:hidden modal-backdrop">
        </div>

        <!-- ---- Sidebar ---- -->
        <!-- Desktop: always visible via flex; Mobile: fixed overlay toggled by Alpine -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:flex-shrink-0 bg-slate-900">
            @include('partials.sidebar')
        </aside>

        <!-- Mobile sidebar (fixed overlay) -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition ease-in-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 flex flex-col lg:hidden">
            @include('partials.sidebar')
        </div>

        <!-- ---- Main Content ---- -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- Top Bar -->
            <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 h-14 flex-shrink-0">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-base font-semibold text-slate-800">@yield('title', 'Dashboard')</h1>
                <span class="hidden sm:inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    {{ now()->format('D, d M Y') }}
                </span>
            </header>

            <!-- Flash: Success -->
            @if(session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="fixed top-16 right-4 z-50 max-w-sm">
                <div class="flex items-center gap-3 bg-emerald-600 text-white px-4 py-3 rounded-xl shadow-lg text-sm font-medium">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                    <button @click="show = false" class="ml-auto opacity-70 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            <!-- Flash: Errors -->
            @if($errors->any())
            <div x-data="{ show: true }"
                 x-show="show"
                 class="fixed top-16 right-4 z-50 max-w-sm">
                <div class="flex items-start gap-3 bg-red-600 text-white px-4 py-3 rounded-xl shadow-lg text-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
                    <button @click="show = false" class="ml-auto opacity-70 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            <!-- Page Content -->
            <main class="flex-1 px-4 lg:px-6 py-6">
                @yield('content')
            </main>

            <footer class="px-6 py-3 border-t border-slate-200 text-center text-xs text-slate-400 flex-shrink-0">
                ExpenseTrack &mdash; Personal Finance Manager
            </footer>

        </div><!-- /main -->
    </div><!-- /layout shell -->

</body>
</html>
