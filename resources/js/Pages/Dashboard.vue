<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const hora = new Date().getHours();
const saludo = hora < 12 ? 'Buenos días' : hora < 18 ? 'Buenas tardes' : 'Buenas noches';

const accesos = [
    {
        label: 'Ver Afiliados',
        desc: 'Consultar y gestionar el registro',
        route: 'affiliates.index',
        cls: 'qa--blue',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>`,
    },
    {
        label: 'Nuevo Afiliado',
        desc: 'Registrar un nuevo beneficiario',
        route: 'affiliates.create',
        cls: 'qa--green',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>`,
    },
    {
        label: 'Importar Excel',
        desc: 'Carga masiva de afiliados',
        route: 'affiliates.index',
        cls: 'qa--gold',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>`,
    },
    {
        label: 'Mi Perfil',
        desc: 'Configurar tu cuenta',
        route: 'profile.edit',
        cls: 'qa--navy',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>`,
    },
];
</script>

<template>
    <Head title="Dashboard | Horizonte S.A.S" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Dashboard</h2>
            <p>Panel de control · Sistema de Gestión de Afiliados</p>
        </template>

        <div class="db">

            <!-- Banner de bienvenida -->
            <div class="db-banner">
                <div class="db-banner__bg">
                    <div class="db-banner__sky"></div>
                    <div class="db-banner__sun">
                        <svg viewBox="0 0 90 90" fill="none">
                            <circle cx="45" cy="45" r="20" fill="#F5C518"/>
                            <g stroke="#F5C518" stroke-width="3" stroke-linecap="round">
                                <line x1="45" y1="6"  x2="45" y2="14"/>
                                <line x1="45" y1="76" x2="45" y2="84"/>
                                <line x1="6"  y1="45" x2="14" y2="45"/>
                                <line x1="76" y1="45" x2="84" y2="45"/>
                                <line x1="18" y1="18" x2="24" y2="24"/>
                                <line x1="66" y1="66" x2="72" y2="72"/>
                                <line x1="72" y1="18" x2="66" y2="24"/>
                                <line x1="24" y1="66" x2="18" y2="72"/>
                            </g>
                        </svg>
                    </div>
                    <div class="db-banner__hills">
                        <div class="db-banner__hill db-banner__hill--1"></div>
                        <div class="db-banner__hill db-banner__hill--2"></div>
                        <div class="db-banner__hill db-banner__hill--3"></div>
                    </div>
                </div>
                <div class="db-banner__content">
                    <h3 class="db-banner__greeting">
                        {{ saludo }}, {{ user?.name?.split(' ')[0] }} 👋
                    </h3>
                    <p class="db-banner__sub">
                        Bienvenido al sistema de gestión de Horizonte S.A.S Pre Exequiales.
                        Hoy es {{ new Date().toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}.
                    </p>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="db-section-label">ACCESOS RÁPIDOS</div>
            <div class="db-quick">
                <Link
                    v-for="acc in accesos"
                    :key="acc.route"
                    :href="route(acc.route)"
                    class="qa"
                    :class="acc.cls"
                >
                    <svg class="qa__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" v-html="acc.icon"></svg>
                    <div class="qa__body">
                        <span class="qa__label">{{ acc.label }}</span>
                        <span class="qa__desc">{{ acc.desc }}</span>
                    </div>
                    <svg class="qa__arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </Link>
            </div>

            <!-- Información empresa -->
            <div class="db-empresa">
                <div class="db-empresa__logo">
                    <svg viewBox="0 0 72 72" fill="none">
                        <circle cx="36" cy="24" r="13" fill="#F5C518"/>
                        <g stroke="#F5C518" stroke-width="2.5" stroke-linecap="round">
                            <line x1="36" y1="5"  x2="36" y2="11"/>
                            <line x1="56" y1="13" x2="52" y2="17"/>
                            <line x1="63" y1="24" x2="57" y2="24"/>
                            <line x1="16" y1="13" x2="20" y2="17"/>
                            <line x1="9"  y1="24" x2="15" y2="24"/>
                            <line x1="54" y1="39" x2="51" y2="36"/>
                            <line x1="18" y1="39" x2="21" y2="36"/>
                        </g>
                        <ellipse cx="18"  cy="55" rx="26" ry="16" fill="#1a6b2a"/>
                        <ellipse cx="54"  cy="58" rx="26" ry="15" fill="#228b35"/>
                        <ellipse cx="36"  cy="64" rx="38" ry="14" fill="#2eaa45"/>
                    </svg>
                </div>
                <div class="db-empresa__body">
                    <h4>Horizonte S.A.S Pre Exequiales</h4>
                    <p>Con amor y compromiso, acompañando a las familias colombianas</p>
                </div>
                <div class="db-empresa__status">
                    <span class="db-empresa__dot"></span>
                    Sistema activo
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; }

.db {
    display: flex;
    flex-direction: column;
    gap: 22px;
    font-family: 'DM Sans', sans-serif;
}

/* ── Banner ── */
.db-banner {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    min-height: 140px;
    display: flex;
    align-items: flex-end;
    box-shadow: 0 8px 30px rgba(10,61,107,0.22);
}

.db-banner__bg {
    position: absolute; inset: 0;
}

.db-banner__sky {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, #062b4e 0%, #1262a8 45%, #1e7f33 100%);
}

.db-banner__sun {
    position: absolute;
    top: -10px; right: 40px;
    animation: spinSun 20s linear infinite;
    opacity: 0.9;
}

@keyframes spinSun { to { transform: rotate(360deg); } }

.db-banner__sun svg { width: 90px; height: 90px; }

.db-banner__hills {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 55%;
}

.db-banner__hill {
    position: absolute;
    bottom: 0;
    border-radius: 50% 50% 0 0;
}

.db-banner__hill--1 { left: -5%; right: 50%; height: 100%; background: rgba(21,88,36,0.5); }
.db-banner__hill--2 { left: 30%; right: 20%; height: 80%;  background: rgba(30,127,51,0.35); }
.db-banner__hill--3 { left: 55%; right: -5%; height: 90%;  background: rgba(30,127,51,0.25); }

.db-banner__content {
    position: relative; z-index: 2;
    padding: 28px 28px 22px;
}

.db-banner__greeting {
    font-family: 'Playfair Display', serif;
    font-size: 1.45rem;
    color: #fff;
    font-weight: 700;
    margin-bottom: 6px;
}

.db-banner__sub {
    font-size: 0.84rem;
    color: rgba(255,255,255,0.7);
    text-transform: capitalize;
}

/* ── Section label ── */
.db-section-label {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    color: #94a3b8;
}

/* ── Quick access ── */
.db-quick {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}

.qa {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 18px 16px;
    border-radius: 14px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
    position: relative;
    overflow: hidden;
}

.qa:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,0.18); }

.qa--blue  { background: linear-gradient(135deg, #1565a8, #2e8bc0); }
.qa--green { background: linear-gradient(135deg, #1a6b2a, #2eaa45); }
.qa--gold  { background: linear-gradient(135deg, #c77b00, #f5a623); }
.qa--navy  { background: linear-gradient(135deg, #071e38, #0a3d6b); }

.qa__icon { width: 28px; height: 28px; stroke: rgba(255,255,255,0.9); flex-shrink: 0; }

.qa__body { flex: 1; min-width: 0; }

.qa__label {
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.qa__desc {
    display: block;
    font-size: 0.73rem;
    color: rgba(255,255,255,0.62);
    margin-top: 1px;
}

.qa__arrow {
    width: 16px; height: 16px;
    fill: rgba(255,255,255,0.35);
    flex-shrink: 0;
    transition: fill 0.2s, transform 0.2s;
}

.qa:hover .qa__arrow {
    fill: rgba(255,255,255,0.75);
    transform: translateX(2px);
}

/* ── Empresa ── */
.db-empresa {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border-radius: 14px;
    padding: 18px 22px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.db-empresa__logo svg { width: 52px; height: 52px; }

.db-empresa__body { flex: 1; }

.db-empresa__body h4 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    color: #0a3d6b;
    font-weight: 700;
    margin-bottom: 3px;
}

.db-empresa__body p { font-size: 0.8rem; color: #64748b; }

.db-empresa__status {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #d1fae5;
    color: #065f46;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 99px;
    flex-shrink: 0;
}

.db-empresa__dot {
    width: 7px; height: 7px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.55; transform: scale(0.8); }
}

@media (max-width: 640px) {
    .db-quick { grid-template-columns: 1fr 1fr; }
    .db-empresa { flex-wrap: wrap; }
    .db-banner__sun { display: none; }
}
</style>