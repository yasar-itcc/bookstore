import Alpine from 'alpinejs';

// Sidebar state for the admin layout: collapsed (desktop, remembered) and open (mobile drawer).
Alpine.data('adminShell', () => ({
    collapsed: false,
    hovering: false,
    mobileOpen: false,

    init() {
        try {
            this.collapsed = localStorage.getItem('admin.sidebarCollapsed') === '1';
        } catch (e) {}
    },

    toggleCollapsed() {
        this.collapsed = ! this.collapsed;
        try {
            localStorage.setItem('admin.sidebarCollapsed', this.collapsed ? '1' : '0');
        } catch (e) {}
    },

    // A collapsed sidebar shows only icons, and expands while hovered (like Vuexy).
    get compact() {
        return this.collapsed && ! this.hovering;
    },
}));

window.Alpine = Alpine;
Alpine.start();
