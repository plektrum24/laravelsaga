<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Sign In | SAGA TOKO APP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Check Setup Status and License
        async function checkSetupAndLicense() {
            try {
                // Check setup status first
                const setupRes = await fetch('/api/setup/status');
                const setupData = await setupRes.json();

                if (setupData.success && !setupData.data.setupComplete) {
                    // Not setup yet, redirect to first-run (assuming it exists in Laravel or is handled)
                    window.location.href = '/first-run';
                    return;
                }

                // Then check license
                const res = await fetch('/api/license/check');
                const data = await res.json();
                if (!data.valid) {
                    // window.location.href = '/activation'; // Uncomment if activation page exists
                }
            } catch (e) {
                console.error('Startup check failed', e);
            }
        }
        checkSetupAndLicense();
    </script>
</head>

<body x-data="{ 
      page: 'signin', 
      loaded: true, 
      darkMode: false,
      email: '',
      password: '',
      rememberMe: false,
      isLoading: false,
      errorMessage: '',
      async handleLogin() {
        this.isLoading = true;
        this.errorMessage = '';
        
        try {
          const response = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: this.email, password: this.password })
          });
          
          const data = await response.json();
          
          if (data.success) {
            localStorage.setItem('saga_token', data.data.token);
            localStorage.setItem('saga_user', JSON.stringify(data.data.user));
            if (data.data.tenant) {
              localStorage.setItem('saga_tenant', JSON.stringify(data.data.tenant));
            }
            window.location.href = data.data.redirectPath || '/';
          } else {
            // Handle subscription expired specially
            if (data.message === 'SUBSCRIPTION_EXPIRED') {
              this.errorMessage = 'Langganan Anda telah berakhir. Silakan hubungi administrator untuk memperpanjang.';
            } else {
              this.errorMessage = data.message || 'Login failed';
            }
          }
        } catch (error) {
          this.errorMessage = 'Cannot connect to server. Please try again.';
          console.error('Login error:', error);
        } finally {
          this.isLoading = false;
        }
      }
    }" x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
         // Check if already logged in
         if (localStorage.getItem('saga_token')) {
           const user = JSON.parse(localStorage.getItem('saga_user'));
           if (user) {
             // Redirect logic based on role, matching HTML
             // Assuming routes like /admin-dashboard or /pos exist, otherwise default to /
             if (user.role === 'super_admin') window.location.href = '/admin-dashboard';
             else if (user.role === 'cashier') window.location.href = '/pos';
             else window.location.href = '/';
           }
         }
    " :class="{'dark bg-gray-900': darkMode === true}">

    <!-- ===== Preloader Start ===== -->
    @include('partials.preloader')
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div class="relative flex flex-col justify-center w-full h-screen dark:bg-gray-900 sm:p-0 lg:flex-row">
            <!-- Form -->
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <span class="text-white font-bold text-2xl">S</span>
                                </div>
                                <div>
                                    <span class="text-2xl font-bold text-gray-800 dark:text-white">SAGA TOKO</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Point of Sale System</p>
                                </div>
                            </div>
                            <h1
                                class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                                Sign In
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Enter your email and password to access your account
                            </p>
                        </div>

                        <!-- Error Message -->
                        <div x-show="errorMessage" x-transition
                            class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                            <p class="text-sm text-red-600 dark:text-red-400" x-text="errorMessage"></p>
                        </div>

                        <form @submit.prevent="handleLogin()">
                            <div class="space-y-5">
                                <!-- Email -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Email<span class="text-error-500">*</span>
                                    </label>
                                    <input type="email" x-model="email" placeholder="admin@sagatoko.com" required
                                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>

                                <!-- Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Password<span class="text-error-500">*</span>
                                    </label>
                                    <div x-data="{ showPassword: false }" class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" x-model="password"
                                            placeholder="Enter your password" required
                                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                        <span @click="showPassword = !showPassword"
                                            class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-400">
                                            <svg x-show="!showPassword" class="fill-current" width="20" height="20"
                                                viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z"
                                                    fill="#98A2B3" />
                                            </svg>
                                            <svg x-show="showPassword" class="fill-current" width="20" height="20"
                                                viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709Z"
                                                    fill="#98A2B3" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <!-- Remember Me -->
                                <div class="flex items-center">
                                    <div x-data="{ checked: false }">
                                        <label
                                            class="flex items-center text-sm font-normal text-gray-700 cursor-pointer select-none dark:text-gray-400">
                                            <div class="relative">
                                                <input type="checkbox" x-model="rememberMe" class="sr-only" />
                                                <div :class="rememberMe ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                    class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                    <span :class="rememberMe ? '' : 'opacity-0'">
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7"
                                                                stroke="white" stroke-width="1.94437"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                            Keep me logged in
                                        </label>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div>
                                    <button type="submit" :disabled="isLoading"
                                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span x-text="isLoading ? 'Signing in...' : 'Sign In'">Sign In</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Development Bypass Button -->
                        <div class="mt-6 text-center">
                            <p class="text-xs text-gray-400 mb-2">Development Only</p>
                            <a href="/" class="text-sm font-medium text-brand-500 hover:text-brand-600 hover:underline">
                                &larr; Back to Dashboard (Bypass Login)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Branding -->
            <div class="relative items-center hidden w-full h-full bg-brand-950 dark:bg-white/5 lg:grid lg:w-1/2">
                <div class="flex items-center justify-center z-1">
                    <!-- Common Grid Shape Partial -->
                    <div class="absolute inset-0 z-0">
                        <!-- You might want to copy common-grid-shape.html content or create a partial -->
                        <svg class="absolute inset-0 h-full w-full stroke-white/10" aria-hidden="true">
                            <defs>
                                <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                                    <path d="M0 40L40 0H20L0 20M40 40V20L20 40" stroke-width="1"></path>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" stroke-width="0" fill="url(#grid-pattern)"></rect>
                        </svg>
                    </div>

                    <div class="flex flex-col items-center max-w-xs z-10">
                        <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">SAGA TOKO APP</h2>
                        <p class="text-center text-gray-400 dark:text-white/60">
                            Multi-tenant Point of Sale System for Modern Retail Business
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dark Mode Toggler -->
            <div class="fixed z-50 hidden bottom-6 right-6 sm:block">
                <button
                    class="inline-flex items-center justify-center text-white transition-colors rounded-full size-14 bg-brand-500 hover:bg-brand-600"
                    @click.prevent="darkMode = !darkMode">
                    <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415Z"
                            fill="" />
                    </svg>
                    <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97Z"
                            fill="" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <!-- ===== Page Wrapper End ===== -->
</body>

</html>