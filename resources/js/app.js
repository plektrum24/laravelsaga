import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

// Services
import { api, auth, barcode, store } from './services/index.js';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

// Make services globally available
window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// Make services globally available
window.ApiService = api;
window.AuthService = auth;
window.BarcodeService = barcode;
window.SagaStore = store;

// Initialize Alpine.js
Alpine.start();

// Setup Alpine.js store integration
Alpine.store('app', {
    user: store.user,
    tenant: store.tenant,
    isAuthenticated: store.isAuthenticated(),
    darkMode: store.darkMode,
    sidebarCollapsed: store.sidebarCollapsed
});

// Initialize barcode scanner on page load
barcode.init();

// Hide preloader when Alpine is ready
Alpine.nextTick(() => {
    if (window.hidePreloader) {
        window.hidePreloader();
    }
});

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Hide preloader on DOMContentLoaded
    if (window.hidePreloader) {
        window.hidePreloader();
    }

    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
