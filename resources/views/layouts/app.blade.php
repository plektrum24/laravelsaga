<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SAGA POS - Inventory & Sales Management')</title>

    <!-- Html5 Qrcode Scanner for Mobile -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- SweetAlert2 (Fix for missing styles/invisible buttons) -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* SweetAlert2 Button Overrides for Brand Consistency */
        .swal2-confirm {
            background-color: #3b82f6 !important;
            /* Blue-500 */
            color: white !important;
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5) !important;
        }

        .swal2-cancel {
            background-color: #6b7280 !important;
            /* Gray-500 */
            color: white !important;
            border: none !important;
        }

        .swal2-confirm:hover {
            background-color: #2563eb !important;
            /* Blue-600 */
        }

        .swal2-cancel:hover {
            background-color: #4b5563 !important;
            /* Gray-600 */
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .scanner-active video {
            border-radius: 0.75rem;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function priceCheckerMixin() {
            return {
                showPriceCheckModal: false,
                priceCheckQuery: '',
                priceCheckResult: null,
                searchResults: [],
                isScanning: false,
                html5QrcodeScanner: null,

                openPriceChecker() {
                    this.showPriceCheckModal = true;
                    this.priceCheckQuery = '';
                    this.priceCheckResult = null;
                    this.searchResults = [];
                    this.isScanning = false;
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                },

                closePriceChecker() {
                    this.showPriceCheckModal = false;
                    this.stopScanner();
                },

                selectProduct(product) {
                    this.priceCheckResult = product;
                    this.searchResults = [];
                },

                async searchProduct(query) {
                    if (!query || query.length < 2) return;

                    this.isLoading = true;
                    this.priceCheckResult = null;
                    this.searchResults = [];
                    console.log('Searching for:', query);

                    try {
                        // Laravel API Route needed
                        const token = localStorage.getItem('saga_token') || '{{ csrf_token() }}'; // Fallback or use Cookie
                        // Note: In Laravel we typically use Cookies for auth, but user wants exact logic match.
                        // We will keep the fetch logic but might need adaption if API relies on Bearer token.
                        // For now we assume the API exists or will be built.

                        const response = await fetch(`/api/products?search=${encodeURIComponent(query)}&page=1&limit=50`, {
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('saga_token'),
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`API Error: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success && data.data && data.data.products && data.data.products.length > 0) {
                            const products = data.data.products;
                            if (products.length === 1) {
                                this.selectProduct(products[0]);
                            } else {
                                this.searchResults = products;
                            }
                        } else {
                            // Using native alert if Swal not loaded, or assuming Swal loaded in app.js
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Product Not Found',
                                    text: 'No product matched your search.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Search error detail:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                toggleScanner() {
                    if (this.isScanning) {
                        this.stopScanner();
                    } else {
                        this.startScanner();
                    }
                },

                startScanner() {
                    this.isScanning = true;
                    this.$nextTick(() => {
                        const element = document.getElementById("price-check-reader");
                        if (!element) return;

                        if (this.html5QrcodeScanner) {
                            try { this.html5QrcodeScanner.clear(); } catch (e) { }
                        }

                        this.html5QrcodeScanner = new Html5Qrcode("price-check-reader");
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        this.html5QrcodeScanner.start(
                            { facingMode: "environment" },
                            config,
                            (decodedText, decodedResult) => {
                                console.log(`Scan result: ${decodedText}`);
                                this.priceCheckQuery = decodedText;
                                this.searchProduct(decodedText);
                                this.stopScanner();
                                new Audio('assets/beep.mp3').play().catch(e => { });
                            },
                            (errorMessage) => { }
                        ).catch(err => {
                            console.error("Error starting scanner", err);
                            this.isScanning = false;
                        });
                    });
                },

                stopScanner() {
                    if (this.html5QrcodeScanner && this.isScanning) {
                        this.html5QrcodeScanner.stop().then(() => {
                            this.html5QrcodeScanner.clear();
                            this.isScanning = false;
                        }).catch(err => {
                            console.error("Failed to stop scanner", err);
                        });
                    } else {
                        this.isScanning = false;
                    }
                }
            };
        }

        // Dark Mode & Preloader Logic
        (function () {
            const darkMode = JSON.parse(localStorage.getItem('darkMode') || 'false');
            if (darkMode) {
                document.documentElement.classList.add('dark');
            }
            window.hidePreloader = function () {
                const preloader = document.getElementById('app-preloader');
                if (preloader) {
                    preloader.style.display = 'none';
                }
            };
            setTimeout(() => {
                window.hidePreloader?.();
        }, 5000);
        })();
    </script>
</head>

<body class="bg-gray-50 dark:bg-black" x-data="{ 
      page: '{{ Route::currentRouteName() }}', 
      loaded: true, 
      darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'), 
      stickyMenu: false, 
      sidebarToggle: false, 
      scrollTop: false,
      ...priceCheckerMixin(),
      
      init() {
         Alpine.store('sidebar', { 
            open: JSON.parse(localStorage.getItem('sidebarOpen')) || false, 
            toggle() { 
                this.open = !this.open;
                localStorage.setItem('sidebarOpen', this.open);
            } 
         });
         this.$watch('darkMode', value => {
            localStorage.setItem('darkMode', JSON.stringify(value));
            if(value) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
         });
      }
    }">

    <!-- Preloader Start -->
    <div id="app-preloader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white dark:bg-gray-900">
        <div class="relative h-12 w-12">
            <div class="absolute inset-0 rounded-full border-4 border-gray-200 dark:border-gray-700"></div>
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-brand-500 animate-spin">
            </div>
        </div>
    </div>
    <!-- Preloader End -->

    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('partials.sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Small Device Overlay -->
            <div @click="$store.sidebar.open = false" :class="$store.sidebar.open ? 'block lg:hidden' : 'hidden'"
                class="fixed w-full h-screen z-[9999] bg-gray-900/50"></div>

            <!-- ===== Header Start ===== -->
            @include('partials.header')
            <!-- ===== Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-black">
                <div class="mx-auto max-w-7xl p-4 sm:p-6 md:p-8">
                    @if (session('success'))
                        <x-alert.alert type="success" title="Success!" class="mb-6">
                            {{ session('success') }}
                        </x-alert.alert>
                    @endif

                    @if (session('error'))
                        <x-alert.alert type="error" title="Error!" class="mb-6">
                            {{ session('error') }}
                        </x-alert.alert>
                    @endif

                    @yield('content')
                </div>
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>

    <!-- Price Checker Modal -->
    <div x-show="showPriceCheckModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
            @click.away="closePriceChecker()">

            <!-- Modal Header -->
            <div
                class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Cek Harga Produk
                </h3>
                <button @click="closePriceChecker()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-4 overflow-y-auto flex-1">
                <!-- Search & Scan Controls -->
                <div class="mb-6">
                    <div class="flex gap-2 items-start">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" x-model="priceCheckQuery" @keyup.enter="searchProduct(priceCheckQuery)"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                                placeholder="Scan barcode or type name..." x-ref="searchInput">
                        </div>
                        <button @click="toggleScanner()"
                            class="px-4 py-2.5 bg-brand-500 text-white rounded-xl hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h.01M9 17h.01M5 11h2m-6-6v2a2 2 0 002 2h2v-4H3zm2 14v-2a2 2 0 00-2-2v4h4v-2H5zm14-14h-2v4h2a2 2 0 002-2v-2z">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">Scan</span>
                        </button>
                    </div>

                    <!-- Search Results Dropdown (Full Width) -->
                    <div x-show="searchResults.length > 0 && !priceCheckResult"
                        class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl mt-2 overflow-y-auto max-h-[60vh] divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="product in searchResults" :key="product.id">
                            <div @click="selectProduct(product)"
                                class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <div
                                    class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden mr-3 border border-gray-200 dark:border-gray-600">
                                    <img :src="product.image_url || 'assets/images/product/product-01.png'"
                                        class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                                        x-text="product.name"></p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500"
                                            x-text="product.barcode || product.sku"></span>
                                        <span
                                            class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-medium"
                                            x-text="'Stk: ' + (product.stock || 0)"></span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Scanner Viewport -->
                <div x-show="isScanning" class="mb-6 rounded-xl overflow-hidden bg-black relative scanner-active">
                    <div id="price-check-reader" class="w-full"></div>
                    <button @click="stopScanner()"
                        class="absolute top-2 right-2 bg-black/50 text-white rounded-full p-1 hover:bg-black/70">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <p
                        class="text-center text-white/70 text-sm py-2 absolute bottom-0 w-full bg-gradient-to-t from-black/80 to-transparent">
                        Point camera at barcode
                    </p>
                </div>

                <!-- Product Details Result -->
                <div x-show="priceCheckResult" class="animate-fade-in-up">
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <!-- Product Image -->
                        <div
                            class="w-full sm:w-1/3 aspect-square bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-600">
                            <template x-if="priceCheckResult?.image_url">
                                <img :src="priceCheckResult.image_url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!priceCheckResult?.image_url">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </template>
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1 space-y-3">
                            <div>
                                <h4 class="text-xl font-bold text-gray-800 dark:text-white"
                                    x-text="priceCheckResult?.name"></h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    SKU: <span class="font-mono text-gray-700 dark:text-gray-300"
                                        x-text="priceCheckResult?.sku"></span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Barcode: <span class="font-mono text-gray-700 dark:text-gray-300"
                                        x-text="priceCheckResult?.barcode || '-'"></span>
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Stock</p>
                                    <p class="text-lg font-bold text-blue-700 dark:text-blue-300"
                                        x-text="priceCheckResult?.stock || 0"></p>
                                </div>
                                <div
                                    class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-800">
                                    <p class="text-xs text-green-600 dark:text-green-400 font-medium uppercase">
                                        Category
                                    </p>
                                    <p class="text-sm font-bold text-green-700 dark:text-green-300 truncate"
                                        x-text="priceCheckResult?.category_name || '-'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts stack for pages to push to -->
    @stack('scripts')
</body>

</html>