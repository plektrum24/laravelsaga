/**
 * SAGA TOKO - Global Auth Handler
 * Handles token expiration gracefully with auto-logout
 */

(function () {
    // Override fetch to intercept 401 responses
    const originalFetch = window.fetch;

    window.fetch = async function (...args) {
        try {
            const response = await originalFetch.apply(this, args);

            // Check for 401 Unauthorized (token expired)
            if (response.status === 401) {
                console.warn('[AUTH] Token expired or invalid - logging out');
                handleAutoLogout();
                return response;
            }

            return response;
        } catch (error) {
            throw error;
        }
    };

    function handleAutoLogout() {
        // Clear all auth data
        localStorage.removeItem('saga_token');
        localStorage.removeItem('saga_user');
        localStorage.removeItem('saga_tenant');
        localStorage.removeItem('saga_current_shift');

        // Show simple alert and redirect (no blocking modal)
        const currentPage = window.location.pathname;
        if (!currentPage.includes('signin.html') && !currentPage.includes('signup.html')) {
            // Use setTimeout to avoid blocking the current request
            setTimeout(() => {
                alert('Sesi Anda telah berakhir. Silakan login kembali.');
                window.location.href = '/signin.html';
            }, 100);
        }
    }

    // Also check token on page load
    function checkTokenOnLoad() {
        const token = localStorage.getItem('saga_token');
        const currentPage = window.location.pathname;

        // Skip check for login/signup pages
        if (currentPage.includes('signin.html') || currentPage.includes('signup.html')) {
            return;
        }

        // If no token, redirect to login
        if (!token) {
            window.location.href = '/signin.html';
            return;
        }

        // Try to decode and check expiry (basic check)
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const exp = payload.exp * 1000; // Convert to milliseconds

            if (Date.now() > exp) {
                console.warn('[AUTH] Token expired on page load');
                handleAutoLogout();
            }
        } catch (e) {
            console.error('[AUTH] Error decoding token:', e);
            // Don't auto-logout on decode error, let server validate
        }
    }

    // Run check on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkTokenOnLoad);
    } else {
        checkTokenOnLoad();
    }

    // Expose for manual use
    window.SagaAuth = {
        logout: handleAutoLogout,
        checkToken: checkTokenOnLoad
    };
})();
