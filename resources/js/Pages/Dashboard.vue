<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const hora = new Date().getHours();
const saludo = hora < 12 ? 'Buenos días' : hora < 18 ? 'Buenas tardes' : 'Buenas noches';
</script>

<template>
    <Head title="Dashboard | Horizonte S.A.S" />

    <AuthenticatedLayout>
        <template #header>
            <div class="page-title-bar">
                <div>
                    <h2>Dashboard</h2>
                    <p class="page-subtitle">Panel de control del sistema de afiliados</p>
                </div>
                <div class="page-date">
                    {{ new Date().toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </div>
            </div>
        </template>

        <div class="dashboard">

            <!-- Saludo -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <div class="welcome-sun">
                        <svg viewBox="0 0 80 80" fill="none">
                            <circle cx="40" cy="40" r="18" fill="#F5C518"/>
                            <g stroke="#F5C518" stroke-width="3" stroke-linecap="round">
                                <line x1="40" y1="6"  x2="40" y2="14"/>
                                <line x1="40" y1="66" x2="40" y2="74"/>
                                <line x1="6"  y1="40" x2="14" y2="40"/>
                                <line x1="66" y1="40" x2="74" y2="40"/>
                                <line x1="15" y1="15" x2="21" y2="21"/>
                                <line x1="59" y1="59" x2="65" y2="65"/>
                                <line x1="65" y1="15" x2="59" y2="21"/>
                                <line x1="21" y1="59" x2="15" y2="65"/>
                            </g>
                        </svg>
                    </div>
                    <div>
                        <h3>{{ saludo }}, {{ user?.name?.split(' ')[0] }} 👋</h3>
                        <p>Bienvenido al sistema de gestión de Horizonte S.A.S Pre Exequiales</p>
                    </div>
                </div>
                <div class="welcome-hills">
                    <div class="wh-hill wh-1"></div>
                    <div class="wh-hill wh-2"></div>
                    <div class="wh-hill wh-3"></div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="section-title">Accesos Rápidos</div>
            <div class="quick-access">
                <Link :href="route('affiliates.index')" class="qa-card qa-blue">
                    <div class="qa-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="qa-label">Ver Afiliados</div>
                    <div class="qa-desc">Gestionar registro de afiliados</div>
                    <svg class="qa-arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </Link>

                <Link :href="route('affiliates.create')" class="qa-card qa-green">
                    <div class="qa-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div class="qa-label">Nuevo Afiliado</div>
                    <div class="qa-desc">Registrar un nuevo afiliado</div>
                    <svg class="qa-arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </Link>

                <Link :href="route('affiliates.index')" class="qa-card qa-gold">
                    <div class="qa-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="qa-label">Importar Excel</div>
                    <div class="qa-desc">Carga masiva desde archivo</div>
                    <svg class="qa-arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </Link>

                <Link :href="route('profile.edit')" class="qa-card qa-navy">
                    <div class="qa-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="qa-label">Mi Perfil</div>
                    <div class="qa-desc">Configurar cuenta de usuario</div>
                    <svg class="qa-arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </Link>
            </div>

            <!-- Info empresa -->
            <div class="company-info">
                <div class="ci-logo">
                    <svg viewBox="0 0 80 80" fill="none">
                        <circle cx="40" cy="26" r="13" fill="#F5C518"/>
                        <g stroke="#F5C518" stroke-width="2.5" stroke-linecap="round">
                            <line x1="40" y1="5"  x2="40" y2="11"/>
                            <line x1="60" y1="14" x2="56" y2="18"/>
                            <line x1="67" y1="26" x2="61" y2="26"/>
                            <line x1="20" y1="14" x2="24" y2="18"/>
                            <line x1="13" y1="26" x2="19" y2="26"/>
                        </g>
                        <ellipse cx="20" cy="58" rx="24" ry="14" fill="#1a6b2a"/>
                        <ellipse cx="60" cy="61" rx="24" ry="13" fill="#228b35"/>
                        <ellipse cx="40" cy="66" rx="40" ry="15" fill="#2eaa45"/>
                    </svg>
                </div>
                <div class="ci-body">
                    <h4>Horizonte S.A.S Pre Exequiales</h4>
                    <p>Sistema de Gestión de Afiliados — Con amor y compromiso para nuestras familias</p>
                </div>
                <div class="ci-badge">
                    <span>Sistema Activo</span>
                    <span class="ci-dot"></span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

* { box-sizing: border-box; }

:root {
    --hz-navy:   #0a3d6b;
    --hz-blue:   #1565a8;
    --hz-green:  #2eaa45;
    --hz-gold:   #f5a623;
}

.dashboard {
    display: flex;
    flex-direction: column;
    gap: 24px;
    font-family: 'DM Sans', sans-serif;
}

/* Page title bar */
.page-title-bar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}

.page-subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 2px;
}

.page-date {
    font-size: 0.8rem;
    color: #94a3b8;
    text-transform: capitalize;
}

/* Welcome banner */
.welcome-banner {
    background: linear-gradient(135deg, #0a3d6b 0%, #1565a8 40%, #0d7c30 100%);
    border-radius: 18px;
    padding: 28px 28px 0;
    overflow: hidden;
    position: relative;
    box-shadow: 0 8px 30px rgba(10,61,107,0.25);
}

.welcome-content {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 2;
}

.welcome-sun {
    flex-shrink: 0;
    animation: spinSun 20s linear infinite;
}

@keyframes spinSun {
    to { transform: rotate(360deg); }
}

.welcome-sun svg { width: 72px; height: 72px; }

.welcome-content h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #fff;
    font-weight: 600;
    margin-bottom: 4px;
}

.welcome-content p {
    font-size: 0.87rem;
    color: rgba(255,255,255,0.7);
}

.welcome-hills {
    position: relative;
    height: 60px;
    margin-top: 20px;
}

.wh-hill {
    position: absolute;
    bottom: 0;
    border-radius: 50% 50% 0 0;
}

.wh-1 {
    left: -5%; right: 40%;
    height: 100%;
    background: rgba(46,170,69,0.35);
}

.wh-2 {
    left: 30%; right: 20%;
    height: 80%;
    background: rgba(46,170,69,0.25);
}

.wh-3 {
    left: 50%; right: -5%;
    height: 90%;
    background: rgba(46,170,69,0.2);
}

/* Section title */
.section-title {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    color: #94a3b8;
    text-transform: uppercase;
    margin-bottom: -8px;
}

/* Quick access */
.quick-access {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 16px;
}

.qa-card {
    border-radius: 16px;
    padding: 22px 20px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.qa-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.qa-blue  { background: linear-gradient(135deg, #1565a8, #2e8bc0); }
.qa-green { background: linear-gradient(135deg, #1a6b2a, #2eaa45); }
.qa-gold  { background: linear-gradient(135deg, #c77b00, #f5a623); }
.qa-navy  { background: linear-gradient(135deg, #091e36, #0a3d6b); }

.qa-icon svg {
    width: 32px; height: 32px;
    stroke: rgba(255,255,255,0.9);
}

.qa-label {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-top: 4px;
}

.qa-desc {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.65);
}

.qa-arrow {
    position: absolute;
    bottom: 16px; right: 16px;
    width: 18px; height: 18px;
    fill: rgba(255,255,255,0.4);
    transition: transform 0.2s, fill 0.2s;
}

.qa-card:hover .qa-arrow {
    transform: translateX(3px);
    fill: rgba(255,255,255,0.8);
}

/* Company info */
.company-info {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border-radius: 14px;
    padding: 18px 22px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}

.ci-logo svg { width: 56px; height: 56px; }

.ci-body { flex: 1; }

.ci-body h4 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    color: #0a3d6b;
    font-weight: 700;
    margin-bottom: 3px;
}

.ci-body p {
    font-size: 0.8rem;
    color: #64748b;
}

.ci-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #d1fae5;
    color: #065f46;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 99px;
}

.ci-dot {
    width: 7px; height: 7px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(0.8); }
}

@media (max-width: 640px) {
    .welcome-content h3 { font-size: 1.2rem; }
    .quick-access { grid-template-columns: 1fr 1fr; }
    .company-info { flex-direction: column; text-align: center; }
    .page-title-bar { flex-direction: column; align-items: flex-start; }
}
</style>