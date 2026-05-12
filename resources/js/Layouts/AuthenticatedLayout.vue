<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const sidebarOpen = ref(false);

// Detectar rol actual
const isSuperAdmin = computed(() => page.props.auth?.roles?.includes('super_admin') ?? false);
const isEmpleado   = computed(() => page.props.auth?.roles?.includes('empleado') ?? false);
const isCliente    = computed(() => page.props.auth?.roles?.includes('cliente') ?? false);

const navLinks = computed(() => {
    const links = [];

    // Dashboard — super_admin y empleado
    if (isSuperAdmin.value || isEmpleado.value) {
        links.push({
            label: 'Dashboard',
            route: 'dashboard',
            match: 'dashboard',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>`,
        });
    }

    // Portal — cliente
    if (isCliente.value) {
        links.push({
            label: 'Mi Plan',
            route: 'portal.index',
            match: 'portal',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>`,
        });
    }

    // Afiliados — super_admin y empleado
    if (isSuperAdmin.value || isEmpleado.value) {
        links.push(
            {
                label: 'Afiliados',
                route: 'affiliates.index',
                match: 'affiliates',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>`,
            },
            {
                label: 'Nuevo Afiliado',
                route: 'affiliates.create',
                match: 'affiliates.create',
                icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>`,
            }
        );
    }

    return links;
});

// Sección admin — solo super_admin
const adminLinks = computed(() => {
    if (!isSuperAdmin.value) return [];
    return [
        {
            label: 'Usuarios',
            route: 'admin.users.index',
            match: 'admin.users',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>`,
        },
        {
            label: 'Roles y Permisos',
            route: 'admin.roles.index',
            match: 'admin.roles',
            icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>`,
        },
    ];
});

const isActive = (match) => {
    return route().current(match) || route().current(match + '.*');
};
</script>

<template>
    <div class="hz-shell">

        <!-- ═══════════ SIDEBAR ═══════════ -->
        <aside class="hz-sidebar" :class="{ 'hz-sidebar--open': sidebarOpen }">

            <!-- Brand -->
            <div class="hz-brand">
                <div class="hz-brand__logo">
                    <svg viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="22" r="11" fill="#F5C518"/>
                        <g stroke="#F5C518" stroke-width="2.5" stroke-linecap="round">
                            <line x1="32" y1="4"  x2="32" y2="9"/>
                            <line x1="50" y1="11" x2="46.5" y2="14.5"/>
                            <line x1="56" y1="22" x2="51" y2="22"/>
                            <line x1="14" y1="11" x2="17.5" y2="14.5"/>
                            <line x1="8"  y1="22" x2="13" y2="22"/>
                            <line x1="46" y1="33" x2="43" y2="30"/>
                            <line x1="18" y1="33" x2="21" y2="30"/>
                        </g>
                        <ellipse cx="16" cy="50" rx="22" ry="13" fill="#1a6b2a"/>
                        <ellipse cx="48" cy="52" rx="22" ry="12" fill="#228b35"/>
                        <ellipse cx="32" cy="56" rx="34" ry="12" fill="#2eaa45"/>
                    </svg>
                </div>
                <div class="hz-brand__text">
                    <span class="hz-brand__name">HORIZONTE</span>
                    <span class="hz-brand__sub">S.A.S · Pre Exequiales</span>
                </div>
            </div>

            <div class="hz-sep"></div>

            <!-- Nav principal -->
            <nav class="hz-nav">
                <p class="hz-nav__section">MENÚ PRINCIPAL</p>
                <Link
                    v-for="link in navLinks"
                    :key="link.route"
                    :href="route(link.route)"
                    class="hz-nav__link"
                    :class="{ 'hz-nav__link--active': isActive(link.match) }"
                    @click="sidebarOpen = false"
                >
                    <svg class="hz-nav__icon" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" v-html="link.icon"/>
                    <span>{{ link.label }}</span>
                    <span class="hz-nav__dot"/>
                </Link>
            </nav>

            <!-- Nav administración (solo super_admin) -->
            <nav v-if="adminLinks.length" class="hz-nav hz-nav--admin">
                <p class="hz-nav__section">ADMINISTRACIÓN</p>
                <Link
                    v-for="link in adminLinks"
                    :key="link.route"
                    :href="route(link.route)"
                    class="hz-nav__link"
                    :class="{ 'hz-nav__link--active': isActive(link.match) }"
                    @click="sidebarOpen = false"
                >
                    <svg class="hz-nav__icon" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" v-html="link.icon"/>
                    <span>{{ link.label }}</span>
                    <span class="hz-nav__dot"/>
                </Link>
            </nav>

            <!-- Footer sidebar — usuario -->
            <div class="hz-sidebar-footer">
                <Link :href="route('profile.edit')" class="hz-user" @click="sidebarOpen = false">
                    <div class="hz-user__av">{{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}</div>
                    <div class="hz-user__info">
                        <span class="hz-user__name">{{ user?.name ?? 'Usuario' }}</span>
                        <span class="hz-user__email">{{ user?.email ?? '' }}</span>
                    </div>
                </Link>
                <Link :href="route('logout')" method="post" as="button"
                    class="hz-user__logout" title="Cerrar sesión">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </Link>
            </div>
        </aside>

        <!-- Overlay móvil -->
        <div v-if="sidebarOpen" class="hz-overlay" @click="sidebarOpen = false"/>

        <!-- ═══════════ MAIN ═══════════ -->
        <div class="hz-main">

            <!-- Topbar -->
            <header class="hz-topbar">
                <button class="hz-burger" @click="sidebarOpen = !sidebarOpen">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="hz-topbar__brand">
                    <span class="hz-topbar__name">HORIZONTE</span>
                    <span class="hz-topbar__sub">Pre Exequiales</span>
                </div>
                <div class="hz-topbar__right">
                    <div class="hz-topbar__av">{{ user?.name?.charAt(0)?.toUpperCase() ?? '?' }}</div>
                </div>
            </header>

            <!-- Page Header slot -->
            <div v-if="$slots.header" class="hz-ph">
                <slot name="header"/>
            </div>

            <!-- Contenido -->
            <main class="hz-content">
                <!-- Flash messages globales -->
                <div v-if="$page.props.flash?.message"
                    class="hz-flash hz-flash--ok">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="hz-flash__icon">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash?.error"
                    class="hz-flash hz-flash--err">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="hz-flash__icon">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $page.props.flash.error }}
                </div>

                <slot/>
            </main>

            <!-- Footer -->
            <footer class="hz-footer">
                Horizonte S.A.S Pre Exequiales &copy; {{ new Date().getFullYear() }} · Sistema de Gestión de Afiliados
            </footer>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.hz-shell {
    display: flex; min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
    background: #eef2f7;
}

/* ── SIDEBAR ── */
.hz-sidebar {
    width: 252px;
    background: linear-gradient(180deg, #0a3d6b 0%, #0b2d52 55%, #071e38 100%);
    display: flex; flex-direction: column;
    position: fixed; inset: 0 auto 0 0;
    z-index: 300;
    box-shadow: 4px 0 32px rgba(0,0,0,0.28);
    transition: transform 0.28s cubic-bezier(.4,0,.2,1);
    overflow-y: auto;
}

.hz-brand {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 18px 18px;
    flex-shrink: 0;
}

.hz-brand__logo svg { width: 50px; height: 50px; flex-shrink: 0; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.35)); }
.hz-brand__text { display: flex; flex-direction: column; }
.hz-brand__name { font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; color:#fff; letter-spacing:0.06em; line-height:1; }
.hz-brand__sub  { font-size:0.6rem; color:#ffe066; letter-spacing:0.08em; text-transform:uppercase; margin-top:3px; }

.hz-sep { height:1.5px; margin:0 18px 16px; background:linear-gradient(90deg,#2eaa45,#2e8bc0,#f5a623,transparent); opacity:0.5; border-radius:2px; flex-shrink:0; }

.hz-nav { padding: 0 12px; display:flex; flex-direction:column; gap:3px; }
.hz-nav--admin { margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.06); }

.hz-nav__section { font-size:0.6rem; font-weight:700; letter-spacing:0.13em; color:rgba(255,255,255,0.3); padding:0 8px; margin-bottom:8px; margin-top: 4px; }

.hz-nav__link {
    display:flex; align-items:center; gap:10px;
    padding:10px 12px; border-radius:10px;
    text-decoration:none; color:rgba(255,255,255,0.6);
    font-size:0.875rem; font-weight:500;
    transition:background 0.18s, color 0.18s;
    position:relative;
}

.hz-nav__icon { width:18px; height:18px; flex-shrink:0; }

.hz-nav__dot {
    margin-left:auto; width:6px; height:6px;
    border-radius:50%; background:#f5a623;
    opacity:0; transition:opacity 0.18s;
}

.hz-nav__link:hover { background:rgba(255,255,255,0.07); color:#fff; }

.hz-nav__link--active {
    background:linear-gradient(135deg, rgba(46,170,69,0.22), rgba(21,101,168,0.18));
    color:#fff; font-weight:600;
    border:1px solid rgba(46,170,69,0.28);
}

.hz-nav__link--active .hz-nav__dot { opacity:1; }

/* Sidebar footer */
.hz-sidebar-footer {
    margin-top: auto;
    border-top:1px solid rgba(255,255,255,0.08);
    padding:14px 16px;
    background:rgba(0,0,0,0.18);
    display:flex; align-items:center; gap:9px;
    flex-shrink: 0;
}

.hz-user { display:flex; align-items:center; gap:9px; flex:1; text-decoration:none; min-width:0; }

.hz-user__av {
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,#2eaa45,#2e8bc0);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:0.95rem; color:#fff; flex-shrink:0;
}

.hz-user__info { flex:1; min-width:0; }
.hz-user__name  { display:block; font-size:0.82rem; font-weight:600; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hz-user__email { display:block; font-size:0.68rem; color:rgba(255,255,255,0.38); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

.hz-user__logout {
    background:none; border:none; cursor:pointer;
    color:rgba(255,255,255,0.35); padding:6px; border-radius:8px;
    display:flex; align-items:center;
    transition:color 0.18s, background 0.18s; flex-shrink:0;
}
.hz-user__logout svg { width:17px; height:17px; }
.hz-user__logout:hover { color:#ff6b6b; background:rgba(255,107,107,0.12); }

/* ── OVERLAY ── */
.hz-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; backdrop-filter:blur(2px); }

/* ── MAIN ── */
.hz-main { margin-left:252px; flex:1; display:flex; flex-direction:column; min-height:100vh; }

.hz-topbar {
    height:60px; background:#fff;
    border-bottom:1px solid #e2e8f0;
    display:flex; align-items:center;
    padding:0 24px; gap:14px;
    position:sticky; top:0; z-index:100;
    box-shadow:0 1px 8px rgba(0,0,0,0.06);
}

.hz-burger { display:none; background:none; border:none; cursor:pointer; color:#0a3d6b; padding:4px; border-radius:8px; flex-shrink:0; }
.hz-burger svg { width:22px; height:22px; }

.hz-topbar__brand { display:none; flex-direction:column; flex:1; }
.hz-topbar__name { font-family:'Playfair Display',serif; font-size:1rem; font-weight:700; color:#0a3d6b; letter-spacing:0.05em; line-height:1; }
.hz-topbar__sub  { font-size:0.6rem; color:#2e8bc0; letter-spacing:0.08em; text-transform:uppercase; }

.hz-topbar__right { margin-left:auto; display:flex; align-items:center; gap:10px; }

.hz-topbar__av {
    width:34px; height:34px; border-radius:50%;
    background:linear-gradient(135deg,#0a3d6b,#1565a8);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:0.85rem; color:#fff;
}

/* Page header */
.hz-ph { padding:24px 28px 0; }
.hz-ph :deep(h2) { font-family:'Playfair Display',serif; font-size:1.55rem; color:#0a3d6b; font-weight:700; }
.hz-ph :deep(p)  { font-size:0.85rem; color:#64748b; margin-top:3px; }

/* Flash messages */
.hz-flash {
    display:flex; align-items:center; gap:10px;
    padding:12px 16px; border-radius:10px;
    font-size:0.88rem; font-weight:500;
    margin-bottom:16px;
}
.hz-flash__icon { width:18px; height:18px; flex-shrink:0; }
.hz-flash--ok  { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; }
.hz-flash--err { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; }

/* Contenido y footer */
.hz-content { flex:1; padding:22px 28px 28px; }
.hz-footer { text-align:center; font-size:0.72rem; color:#94a3b8; padding:16px 24px; border-top:1px solid #e2e8f0; background:#fff; }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
    .hz-sidebar { transform:translateX(-252px); }
    .hz-sidebar--open { transform:translateX(0); }
    .hz-main { margin-left:0; }
    .hz-burger { display:flex; }
    .hz-topbar__brand { display:flex; }
    .hz-ph { padding:16px 16px 0; }
    .hz-content { padding:16px 16px 24px; }
}
</style>