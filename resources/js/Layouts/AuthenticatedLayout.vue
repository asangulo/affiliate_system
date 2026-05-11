<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const showMobileMenu = ref(false);

const navLinks = [
    {
        label: 'Dashboard',
        route: 'dashboard',
        icon: `<path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>`
    },
    {
        label: 'Afiliados',
        route: 'affiliates.index',
        icon: `<path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>`
    },
];
</script>

<template>
    <div class="horizonte-app">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <!-- Logo marca -->
            <div class="sidebar-brand">
                <div class="sidebar-logo">
                    <svg viewBox="0 0 60 60" fill="none">
                        <circle cx="30" cy="20" r="9" fill="#F5C518"/>
                        <line x1="30" y1="4"  x2="30" y2="8"  stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="45" y1="10" x2="42" y2="13" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="50" y1="20" x2="46" y2="20" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="15" y1="10" x2="18" y2="13" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="10" y1="20" x2="14" y2="20" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                        <ellipse cx="14" cy="44" rx="18" ry="10" fill="#1a6b2a"/>
                        <ellipse cx="46" cy="46" rx="18" ry="10" fill="#228b35"/>
                        <ellipse cx="30" cy="50" rx="30" ry="11" fill="#2eaa45"/>
                    </svg>
                </div>
                <div class="sidebar-brand-text">
                    <span class="sb-name">HORIZONTE</span>
                    <span class="sb-sub">Pre Exequiales</span>
                </div>
            </div>

            <!-- Nav links -->
            <nav class="sidebar-nav">
                <span class="nav-section-label">MENÚ PRINCIPAL</span>

                <Link
                    v-for="link in navLinks"
                    :key="link.route"
                    :href="route(link.route)"
                    class="nav-link"
                    :class="{ active: route().current(link.route) || route().current(link.route + '.*') }"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="link.icon"></svg>
                    <span>{{ link.label }}</span>
                    <span class="nav-indicator"></span>
                </Link>
            </nav>

            <!-- Usuario -->
            <div class="sidebar-user">
                <div class="user-avatar">
                    {{ user?.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ user?.name }}</span>
                    <span class="user-email">{{ user?.email }}</span>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="logout-btn" title="Cerrar sesión">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </Link>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="main-wrapper">
            <!-- Topbar móvil -->
            <header class="topbar">
                <button class="mobile-menu-btn" @click="showMobileMenu = !showMobileMenu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="topbar-brand">
                    <span class="tb-name">HORIZONTE</span>
                    <span class="tb-sub">Pre Exequiales</span>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="mobile-logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </Link>
            </header>

            <!-- Menú móvil overlay -->
            <Transition name="slide">
                <div v-if="showMobileMenu" class="mobile-overlay" @click="showMobileMenu = false">
                    <div class="mobile-nav" @click.stop>
                        <Link
                            v-for="link in navLinks"
                            :key="link.route"
                            :href="route(link.route)"
                            class="mobile-nav-link"
                            :class="{ active: route().current(link.route) || route().current(link.route + '.*') }"
                            @click="showMobileMenu = false"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="link.icon"></svg>
                            {{ link.label }}
                        </Link>
                    </div>
                </div>
            </Transition>

            <!-- Page Header slot -->
            <div v-if="$slots.header" class="page-header">
                <slot name="header" />
            </div>

            <!-- Contenido -->
            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --hz-navy:   #0a3d6b;
    --hz-blue:   #1565a8;
    --hz-sky:    #2e8bc0;
    --hz-green:  #2eaa45;
    --hz-dkgreen:#1a6b2a;
    --hz-gold:   #f5a623;
    --hz-gold-lt:#ffe066;
    --sidebar-w: 250px;
}

.horizonte-app {
    display: flex;
    min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
    background: #f0f4f8;
}

/* ── SIDEBAR ── */
.sidebar {
    width: var(--sidebar-w);
    background: linear-gradient(180deg, var(--hz-navy) 0%, #0d2f52 60%, #091e36 100%);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
    box-shadow: 4px 0 24px rgba(0,0,0,0.25);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 24px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-logo svg {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.sidebar-brand-text {
    display: flex;
    flex-direction: column;
}

.sb-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
    letter-spacing: 0.05em;
}

.sb-sub {
    font-size: 0.65rem;
    color: var(--hz-gold-lt);
    letter-spacing: 0.07em;
    text-transform: uppercase;
    font-weight: 400;
}

/* Nav */
.sidebar-nav {
    flex: 1;
    padding: 20px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow-y: auto;
}

.nav-section-label {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    color: rgba(255,255,255,0.35);
    padding: 0 8px;
    margin-bottom: 8px;
    display: block;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    text-decoration: none;
    color: rgba(255,255,255,0.65);
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}

.nav-link svg {
    width: 19px;
    height: 19px;
    flex-shrink: 0;
}

.nav-link .nav-indicator {
    position: absolute;
    right: 10px;
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--hz-gold);
    opacity: 0;
    transition: opacity 0.2s;
}

.nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
}

.nav-link.active {
    background: linear-gradient(135deg, rgba(46,170,69,0.25), rgba(21,101,168,0.2));
    color: #fff;
    font-weight: 600;
    border: 1px solid rgba(46,170,69,0.3);
}

.nav-link.active .nav-indicator { opacity: 1; }

/* Sidebar user */
.sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 16px;
    border-top: 1px solid rgba(255,255,255,0.08);
    background: rgba(0,0,0,0.2);
}

.user-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--hz-green), var(--hz-sky));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    display: block;
    font-size: 0.68rem;
    color: rgba(255,255,255,0.4);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.logout-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: rgba(255,255,255,0.4);
    padding: 6px;
    border-radius: 8px;
    transition: all 0.2s;
    display: flex;
}

.logout-btn svg { width: 18px; height: 18px; }

.logout-btn:hover {
    color: #ff6b6b;
    background: rgba(255,107,107,0.12);
}

/* ── MAIN WRAPPER ── */
.main-wrapper {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* TOPBAR (solo mobile) */
.topbar {
    display: none;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--hz-navy);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.mobile-menu-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #fff;
    padding: 4px;
    display: flex;
}

.mobile-menu-btn svg { width: 22px; height: 22px; }

.topbar-brand {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.tb-name {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.06em;
}

.tb-sub {
    font-size: 0.6rem;
    color: var(--hz-gold-lt);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.mobile-logout {
    background: none;
    border: none;
    cursor: pointer;
    color: rgba(255,255,255,0.6);
    padding: 4px;
    display: flex;
}

.mobile-logout svg { width: 20px; height: 20px; }

/* Mobile overlay */
.mobile-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 200;
}

.mobile-nav {
    width: 240px;
    background: var(--hz-navy);
    height: 100%;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
}

.mobile-nav-link svg { width: 20px; height: 20px; }
.mobile-nav-link.active { background: rgba(46,170,69,0.2); color: #fff; }
.mobile-nav-link:hover { background: rgba(255,255,255,0.08); color: #fff; }

.slide-enter-active, .slide-leave-active { transition: opacity 0.25s; }
.slide-enter-from, .slide-leave-to { opacity: 0; }

/* Page Header */
.page-header {
    padding: 22px 28px 0;
}

.page-header :deep(h2) {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: var(--hz-navy);
    font-weight: 600;
}

/* Page Content */
.page-content {
    flex: 1;
    padding: 20px 28px 32px;
}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .sidebar { display: none; }
    .main-wrapper { margin-left: 0; }
    .topbar { display: flex; }
    .page-header { padding: 16px 16px 0; }
    .page-content { padding: 16px 16px 24px; }
}
</style>