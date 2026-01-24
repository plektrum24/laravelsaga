/**
 * SAGA TOKO - Alert System
 * A beautiful, responsive alert system for confirmations, notifications, and dialogs
 * Usage: SagaAlert.success('Message'), SagaAlert.confirm('Are you sure?', callback)
 */

const SagaAlert = {
    // Container for toasts
    toastContainer: null,

    // Initialize the alert system
    init() {
        // Create toast container if not exists
        if (!this.toastContainer) {
            this.toastContainer = document.createElement('div');
            this.toastContainer.id = 'saga-toast-container';
            this.toastContainer.className = 'fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none';
            document.body.appendChild(this.toastContainer);
        }

        // Add modal container
        if (!document.getElementById('saga-modal-container')) {
            const modalContainer = document.createElement('div');
            modalContainer.id = 'saga-modal-container';
            document.body.appendChild(modalContainer);
        }
    },

    // Icons for different alert types
    icons: {
        success: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`,
        error: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`,
        warning: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>`,
        info: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`,
        confirm: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`,
        delete: `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>`,
        loading: `<svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>`
    },

    // Color schemes
    colors: {
        success: {
            bg: 'bg-green-50 dark:bg-green-900/30',
            border: 'border-green-200 dark:border-green-800',
            icon: 'text-green-500 dark:text-green-400',
            title: 'text-green-800 dark:text-green-200',
            text: 'text-green-700 dark:text-green-300',
            button: 'bg-green-500 hover:bg-green-600 text-white'
        },
        error: {
            bg: 'bg-red-50 dark:bg-red-900/30',
            border: 'border-red-200 dark:border-red-800',
            icon: 'text-red-500 dark:text-red-400',
            title: 'text-red-800 dark:text-red-200',
            text: 'text-red-700 dark:text-red-300',
            button: 'bg-red-500 hover:bg-red-600 text-white'
        },
        warning: {
            bg: 'bg-amber-50 dark:bg-amber-900/30',
            border: 'border-amber-200 dark:border-amber-800',
            icon: 'text-amber-500 dark:text-amber-400',
            title: 'text-amber-800 dark:text-amber-200',
            text: 'text-amber-700 dark:text-amber-300',
            button: 'bg-amber-500 hover:bg-amber-600 text-white'
        },
        info: {
            bg: 'bg-blue-50 dark:bg-blue-900/30',
            border: 'border-blue-200 dark:border-blue-800',
            icon: 'text-blue-500 dark:text-blue-400',
            title: 'text-blue-800 dark:text-blue-200',
            text: 'text-blue-700 dark:text-blue-300',
            button: 'bg-blue-500 hover:bg-blue-600 text-white'
        }
    },

    /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} type - success, error, warning, info
     * @param {number} duration - Duration in milliseconds (default: 3000)
     */
    toast(message, type = 'info', duration = 3000) {
        this.init();

        const colors = this.colors[type];
        const toast = document.createElement('div');
        toast.className = `
            pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border backdrop-blur-sm
            transform translate-x-full opacity-0 transition-all duration-300 ease-out
            ${colors.bg} ${colors.border} min-w-[300px] max-w-[400px]
        `;

        toast.innerHTML = `
            <div class="${colors.icon} flex-shrink-0">${this.icons[type]}</div>
            <p class="${colors.text} text-sm font-medium flex-1">${message}</p>
            <button onclick="this.parentElement.remove()" class="${colors.icon} hover:opacity-70 transition-opacity flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;

        this.toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        return toast;
    },

    // Shorthand methods
    success(message, duration) { return this.toast(message, 'success', duration); },
    error(message, duration) { return this.toast(message, 'error', duration); },
    warning(message, duration) { return this.toast(message, 'warning', duration); },
    info(message, duration) { return this.toast(message, 'info', duration); },

    /**
     * Show a confirmation dialog
     * @param {object} options - Configuration options
     * @returns {Promise<boolean>}
     */
    confirm(options = {}) {
        const {
            title = 'Konfirmasi',
            message = 'Apakah Anda yakin?',
            type = 'confirm', // confirm, delete, warning
            confirmText = 'Ya',
            cancelText = 'Batal',
            confirmClass = '',
            icon = null
        } = typeof options === 'string' ? { message: options } : options;

        return new Promise((resolve) => {
            this.init();

            const isDelete = type === 'delete';
            const isWarning = type === 'warning';

            const iconColor = isDelete ? 'text-red-500' : isWarning ? 'text-amber-500' : 'text-indigo-500';
            const confirmBtnClass = confirmClass || (isDelete ? 'bg-red-500 hover:bg-red-600' : isWarning ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-500 hover:bg-indigo-600');

            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-[99999] flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-200 opacity-0" id="saga-confirm-backdrop"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-200" id="saga-confirm-content">
                    <div class="p-6 text-center">
                        <div class="${iconColor} mx-auto mb-4">
                            ${icon || this.icons[type] || this.icons.confirm}
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${title}</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                        <div class="flex gap-3 justify-center">
                            <button id="saga-confirm-cancel" class="px-5 py-2.5 rounded-lg font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                ${cancelText}
                            </button>
                            <button id="saga-confirm-ok" class="px-5 py-2.5 rounded-lg font-medium text-white ${confirmBtnClass} transition-colors shadow-lg shadow-indigo-500/25">
                                ${confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            // Animate in
            requestAnimationFrame(() => {
                modal.querySelector('#saga-confirm-backdrop').classList.remove('opacity-0');
                modal.querySelector('#saga-confirm-content').classList.remove('scale-95', 'opacity-0');
                modal.querySelector('#saga-confirm-content').classList.add('scale-100', 'opacity-100');
            });

            const closeModal = (result) => {
                modal.querySelector('#saga-confirm-backdrop').classList.add('opacity-0');
                modal.querySelector('#saga-confirm-content').classList.add('scale-95', 'opacity-0');
                modal.querySelector('#saga-confirm-content').classList.remove('scale-100', 'opacity-100');
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = '';
                    resolve(result);
                }, 200);
            };

            modal.querySelector('#saga-confirm-ok').onclick = () => closeModal(true);
            modal.querySelector('#saga-confirm-cancel').onclick = () => closeModal(false);
            modal.querySelector('#saga-confirm-backdrop').onclick = () => closeModal(false);

            // ESC key to close
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    closeModal(false);
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        });
    },

    /**
     * Show a delete confirmation dialog
     */
    delete(message = 'Data yang dihapus tidak dapat dikembalikan!') {
        return this.confirm({
            title: 'Hapus Data?',
            message: message,
            type: 'delete',
            confirmText: 'Hapus',
            cancelText: 'Batal'
        });
    },

    /**
     * Show loading overlay
     * @param {string} message - Loading message
     * @returns {function} - Function to close the loading overlay
     */
    loading(message = 'Memproses...') {
        this.init();

        const modal = document.createElement('div');
        modal.id = 'saga-loading-overlay';
        modal.className = 'fixed inset-0 z-[99999] flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="relative">
                        <div class="w-16 h-16 border-4 border-indigo-200 dark:border-indigo-900 rounded-full"></div>
                        <div class="w-16 h-16 border-4 border-indigo-500 border-t-transparent rounded-full absolute top-0 left-0 animate-spin"></div>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium">${message}</p>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        return () => {
            modal.remove();
            document.body.style.overflow = '';
        };
    },

    /**
     * Show an input prompt dialog
     * @param {object} options - Configuration options
     * @returns {Promise<string|null>}
     */
    prompt(options = {}) {
        const {
            title = 'Input',
            message = '',
            placeholder = '',
            defaultValue = '',
            inputType = 'text',
            confirmText = 'OK',
            cancelText = 'Batal'
        } = typeof options === 'string' ? { title: options } : options;

        return new Promise((resolve) => {
            this.init();

            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-[99999] flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-200 opacity-0" id="saga-prompt-backdrop"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-200" id="saga-prompt-content">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${title}</h3>
                        ${message ? `<p class="text-gray-600 dark:text-gray-300 mb-4">${message}</p>` : ''}
                        <input type="${inputType}" id="saga-prompt-input" value="${defaultValue}" placeholder="${placeholder}"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all mb-4">
                        <div class="flex gap-3 justify-end">
                            <button id="saga-prompt-cancel" class="px-5 py-2.5 rounded-lg font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                ${cancelText}
                            </button>
                            <button id="saga-prompt-ok" class="px-5 py-2.5 rounded-lg font-medium text-white bg-indigo-500 hover:bg-indigo-600 transition-colors shadow-lg shadow-indigo-500/25">
                                ${confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            const input = modal.querySelector('#saga-prompt-input');

            // Animate in
            requestAnimationFrame(() => {
                modal.querySelector('#saga-prompt-backdrop').classList.remove('opacity-0');
                modal.querySelector('#saga-prompt-content').classList.remove('scale-95', 'opacity-0');
                modal.querySelector('#saga-prompt-content').classList.add('scale-100', 'opacity-100');
                input.focus();
                input.select();
            });

            const closeModal = (value) => {
                modal.querySelector('#saga-prompt-backdrop').classList.add('opacity-0');
                modal.querySelector('#saga-prompt-content').classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = '';
                    resolve(value);
                }, 200);
            };

            modal.querySelector('#saga-prompt-ok').onclick = () => closeModal(input.value);
            modal.querySelector('#saga-prompt-cancel').onclick = () => closeModal(null);
            modal.querySelector('#saga-prompt-backdrop').onclick = () => closeModal(null);

            // Enter key to submit
            input.onkeydown = (e) => {
                if (e.key === 'Enter') closeModal(input.value);
                if (e.key === 'Escape') closeModal(null);
            };
        });
    },

    /**
     * Show a custom modal alert
     * @param {object} options - Configuration options
     */
    alert(options = {}) {
        const {
            title = 'Informasi',
            message = '',
            type = 'info',
            buttonText = 'OK'
        } = typeof options === 'string' ? { message: options } : options;

        return new Promise((resolve) => {
            this.init();

            const colors = this.colors[type] || this.colors.info;

            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-[99999] flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-200 opacity-0" id="saga-alert-backdrop"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-200" id="saga-alert-content">
                    <div class="p-6 text-center">
                        <div class="${colors.icon} mx-auto mb-4">
                            ${this.icons[type]}
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${title}</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                        <button id="saga-alert-ok" class="px-6 py-2.5 rounded-lg font-medium text-white ${colors.button} transition-colors shadow-lg">
                            ${buttonText}
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            // Animate in
            requestAnimationFrame(() => {
                modal.querySelector('#saga-alert-backdrop').classList.remove('opacity-0');
                modal.querySelector('#saga-alert-content').classList.remove('scale-95', 'opacity-0');
                modal.querySelector('#saga-alert-content').classList.add('scale-100', 'opacity-100');
            });

            const closeModal = () => {
                modal.querySelector('#saga-alert-backdrop').classList.add('opacity-0');
                modal.querySelector('#saga-alert-content').classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = '';
                    resolve();
                }, 200);
            };

            modal.querySelector('#saga-alert-ok').onclick = closeModal;
            modal.querySelector('#saga-alert-backdrop').onclick = closeModal;

            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape' || e.key === 'Enter') {
                    closeModal();
                    document.removeEventListener('keydown', escHandler);
                }
            });
        });
    }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => SagaAlert.init());
} else {
    SagaAlert.init();
}

// Make it available globally
window.SagaAlert = SagaAlert;
