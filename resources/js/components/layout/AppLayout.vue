<template>
    <div class="layout-wrapper" :class="containerClass">
        <!-- Sidebar -->
        <aside class="layout-sidebar">
            <div class="sidebar-header">
                <a href="/dashboard" class="app-logo">
                    <i class="pi pi-book text-2xl"></i>
                    <span class="app-logo-text">Agenda<span class="text-primary">Sacramental</span></span>
                </a>
            </div>

            <div class="sidebar-menu">
                <ul class="menu-list">
                    <li class="menu-category">Principal</li>
                    <li>
                        <a href="/dashboard" :class="{ 'active': isActive('/dashboard') }">
                            <i class="pi pi-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="menu-category">Agendas</li>
                    <li>
                        <a href="/agenda/crear" :class="{ 'active': isActive('/agenda/crear') }">
                            <i class="pi pi-plus-circle"></i>
                            <span>Crear Agenda</span>
                        </a>
                    </li>
                    <li>
                        <a href="/agenda/historial" :class="{ 'active': isActive('/agenda/historial') }">
                            <i class="pi pi-calendar"></i>
                            <span>Historial</span>
                        </a>
                    </li>

                    <li class="menu-category">Administracion</li>
                    <li>
                        <a href="/miembros" :class="{ 'active': isActive('/miembros') }">
                            <i class="pi pi-users"></i>
                            <span>Miembros</span>
                        </a>
                    </li>
                    <li>
                        <a href="/whatsapp/config" :class="{ 'active': isActive('/whatsapp') }">
                            <i class="pi pi-whatsapp"></i>
                            <span>WhatsApp</span>
                            <Tag v-if="!whatsappConnected" value="!" severity="warn" class="ml-auto" />
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-footer">
                <a href="/pin/logout" class="lock-btn" title="Bloquear acceso">
                    <i class="pi pi-lock"></i>
                    <span>Bloquear</span>
                </a>
                <div class="text-xs text-gray-400 text-center mt-2">
                    <p>Agenda Sacramental</p>
                    <p>v1.0 - Vue + PrimeVue</p>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="layout-main-container">
            <!-- Topbar -->
            <header class="layout-topbar">
                <button class="topbar-menubutton" @click="toggleSidebar">
                    <i class="pi pi-bars"></i>
                </button>

                <div class="topbar-title">
                    <h1>{{ pageTitle }}</h1>
                </div>

                <div class="topbar-end">
                    <div class="topbar-items">
                        <!-- WhatsApp Status -->
                        <div class="topbar-item" :title="whatsappConnected ? 'WhatsApp conectado' : 'WhatsApp no configurado'">
                            <i class="pi pi-whatsapp" :class="whatsappConnected ? 'text-green-500' : 'text-gray-400'"></i>
                        </div>

                        <!-- Quick Actions -->
                        <a href="/agenda/crear" class="topbar-item" title="Crear agenda">
                            <i class="pi pi-plus"></i>
                        </a>

                        <!-- User Menu -->
                        <div class="topbar-item user-menu" @click="toggleUserMenu">
                            <div class="user-avatar">
                                <i class="pi pi-user"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="layout-main">
                <slot></slot>
            </main>

            <!-- Footer -->
            <footer class="layout-footer">
                <span>Agenda Sacramental &copy; {{ currentYear }}</span>
                <span class="ml-auto">Hecho con <i class="pi pi-heart text-red-500"></i> usando Vue + PrimeVue</span>
            </footer>
        </div>

        <!-- Overlay for mobile -->
        <div v-if="sidebarVisible" class="layout-mask" @click="toggleSidebar"></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    pageTitle: { type: String, default: 'Dashboard' }
});

const sidebarVisible = ref(false);
const whatsappConnected = ref(false);
const currentYear = new Date().getFullYear();

const containerClass = computed(() => ({
    'layout-sidebar-active': sidebarVisible.value
}));

function toggleSidebar() {
    sidebarVisible.value = !sidebarVisible.value;
}

function toggleUserMenu() {
    // TODO: implementar menu de usuario
}

function isActive(path) {
    return window.location.pathname.startsWith(path);
}

async function checkWhatsAppStatus() {
    try {
        const res = await fetch('/api/whatsapp/status');
        const data = await res.json();
        whatsappConnected.value = data.connected || false;
    } catch {
        whatsappConnected.value = false;
    }
}

onMounted(() => {
    checkWhatsAppStatus();
});
</script>

<style>
/* Layout Base */
.layout-wrapper {
    min-height: 100vh;
    display: flex;
    background: #f8fafc;
}

/* Sidebar */
.layout-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 280px;
    background: linear-gradient(180deg, #1e3a5f 0%, #0f172a 100%);
    display: flex;
    flex-direction: column;
    z-index: 999;
    transition: transform 0.3s ease;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.app-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: white;
    text-decoration: none;
    font-size: 1.25rem;
    font-weight: 700;
}

.app-logo i {
    color: #60a5fa;
}

.app-logo-text .text-primary {
    color: #60a5fa;
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.menu-category {
    padding: 1rem 1.5rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.05em;
}

.menu-list > li > a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.menu-list > li > a:hover {
    background: rgba(255,255,255,0.05);
    color: white;
}

.menu-list > li > a.active {
    background: rgba(96, 165, 250, 0.15);
    color: #60a5fa;
    border-left-color: #60a5fa;
}

.menu-list > li > a i {
    font-size: 1.1rem;
    width: 1.5rem;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.lock-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    color: rgba(255,255,255,0.5);
    font-size: 13px;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}

.lock-btn:hover {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.85);
}

/* Main Container */
.layout-main-container {
    margin-left: 280px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
}

/* Topbar */
.layout-topbar {
    position: sticky;
    top: 0;
    height: 60px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    gap: 1rem;
    z-index: 998;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.topbar-menubutton {
    display: none;
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.25rem;
    color: #64748b;
}

.topbar-menubutton:hover {
    background: #f1f5f9;
}

.topbar-title h1 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.topbar-end {
    margin-left: auto;
}

.topbar-items {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.topbar-item {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s;
    text-decoration: none;
}

.topbar-item:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* Main Content */
.layout-main {
    flex: 1;
    padding: 1.5rem;
}

/* Footer */
.layout-footer {
    padding: 1rem 1.5rem;
    background: white;
    border-top: 1px solid #e2e8f0;
    font-size: 0.875rem;
    color: #64748b;
    display: flex;
    align-items: center;
}

/* Overlay */
.layout-mask {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 998;
}

/* Responsive */
@media (max-width: 991px) {
    .layout-sidebar {
        transform: translateX(-100%);
    }

    .layout-sidebar-active .layout-sidebar {
        transform: translateX(0);
    }

    .layout-main-container {
        margin-left: 0;
    }

    .topbar-menubutton {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .layout-mask {
        display: block;
    }

    .layout-sidebar-active .layout-mask {
        display: block;
    }
}
</style>
