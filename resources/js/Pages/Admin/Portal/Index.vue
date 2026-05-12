<!-- ═══════════════════════════════════════════════════════════
     Portal/Index.vue
     Coloca en: resources/js/Pages/Portal/Index.vue
     ═══════════════════════════════════════════════════════════ -->
<script setup>
// Portal del cliente — próxima versión con módulo de pagos
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
 
const page = usePage();
const user = computed(() => page.props.auth?.user);
const affiliate = computed(() => user.value?.affiliate);
</script>
 
<template>
    <Head title="Mi Plan"/>
    <AuthenticatedLayout>
        <template #header>
            <h2>Mi Plan</h2>
            <p>Consulta tu estado de afiliación</p>
        </template>
 
        <div class="portal">
            <!-- Sin afiliado vinculado -->
            <div v-if="!affiliate" class="portal-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3>Tu cuenta aún no está vinculada a un plan</h3>
                <p>Comunícate con Horizonte S.A.S para que un asesor vincule tu afiliación a esta cuenta.</p>
                <div class="portal-empty__contact">
                    <p>📞 +57 (8) 000-0000</p>
                    <p>📧 info@horizonte.com.co</p>
                </div>
            </div>
 
            <!-- Con afiliado vinculado -->
            <template v-else>
                <!-- Banner bienvenida -->
                <div class="portal-banner">
                    <div class="portal-banner__bg">
                        <div class="portal-banner__sky"></div>
                        <div class="portal-banner__hills">
                            <div class="pb-hill pb-hill--1"></div>
                            <div class="pb-hill pb-hill--2"></div>
                        </div>
                    </div>
                    <div class="portal-banner__content">
                        <h3>Bienvenido, {{ user?.name?.split(' ')[0] }} 👋</h3>
                        <p>Plan activo: <strong>{{ affiliate.plan?.name }}</strong></p>
                    </div>
                </div>
 
                <!-- Info del plan -->
                <div class="portal-cards">
                    <div class="portal-card">
                        <p class="portal-card__label">Plan contratado</p>
                        <p class="portal-card__val portal-card__val--big">{{ affiliate.plan?.name }}</p>
                    </div>
                    <div class="portal-card">
                        <p class="portal-card__label">Valor de la cuota</p>
                        <p class="portal-card__val portal-card__val--big">
                            ${{ Number(affiliate.fee_amount).toLocaleString('es-CO') }}
                        </p>
                    </div>
                    <div class="portal-card">
                        <p class="portal-card__label">Sede</p>
                        <p class="portal-card__val">{{ affiliate.branch?.name }}</p>
                    </div>
                    <div class="portal-card">
                        <p class="portal-card__label">Fecha de afiliación</p>
                        <p class="portal-card__val">
                            {{ affiliate.affiliation_date
                                ? new Date(affiliate.affiliation_date).toLocaleDateString('es-CO', { year:'numeric', month:'long', day:'numeric' })
                                : 'N/A' }}
                        </p>
                    </div>
                    <div class="portal-card">
                        <p class="portal-card__label">N° Contrato</p>
                        <p class="portal-card__val">#{{ affiliate.contract_number || 'N/A' }}</p>
                    </div>
                    <div class="portal-card">
                        <p class="portal-card__label">Estado</p>
                        <span class="portal-status" :class="affiliate.is_active ? 'portal-status--active' : 'portal-status--inactive'">
                            <span class="portal-status__dot"/>
                            {{ affiliate.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
 
                <!-- Grupo familiar -->
                <div class="portal-section">
                    <h4 class="portal-section__title">Grupo Familiar</h4>
                    <div v-if="affiliate.beneficiaries?.length" class="portal-ben-list">
                        <div v-for="ben in affiliate.beneficiaries.filter(b => b.is_active)"
                            :key="ben.id" class="portal-ben-item">
                            <div class="portal-ben-item__info">
                                <span class="portal-ben-item__name">{{ ben.full_name }}</span>
                                <span class="portal-ben-item__rel">{{ ben.relationship }}</span>
                            </div>
                            <div class="portal-ben-item__meta">
                                <span>Edad: {{ ben.age || 'N/A' }}</span>
                                <span>Auxilio: {{ ben.auxilio || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="portal-empty-text">No hay beneficiarios registrados.</p>
                </div>
 
                <!-- Próxima versión -->
                <div class="portal-coming">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    <div>
                        <p class="portal-coming__title">Módulo de pagos — Próximamente</p>
                        <p class="portal-coming__sub">Pronto podrás consultar tu historial de pagos, fechas de vencimiento y estado de paz y salvo.</p>
                    </div>
                </div>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
 
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing:border-box; }
 
.portal { display:flex; flex-direction:column; gap:20px; font-family:'DM Sans',sans-serif; }
 
/* Empty */
.portal-empty { background:#fff; border-radius:16px; border:1px solid #e2e8f0; padding:48px 32px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:12px; }
.portal-empty svg { width:52px; height:52px; color:#cbd5e1; }
.portal-empty h3 { font-family:'Playfair Display',serif; font-size:1.2rem; color:#0a3d6b; font-weight:700; }
.portal-empty p { font-size:0.88rem; color:#64748b; max-width:400px; }
.portal-empty__contact { margin-top:8px; display:flex; flex-direction:column; gap:4px; font-size:0.9rem; color:#475569; font-weight:500; }
 
/* Banner */
.portal-banner { position:relative; border-radius:16px; overflow:hidden; min-height:120px; display:flex; align-items:flex-end; box-shadow:0 8px 24px rgba(10,61,107,0.2); }
.portal-banner__bg { position:absolute; inset:0; }
.portal-banner__sky { position:absolute; inset:0; background:linear-gradient(135deg,#062b4e 0%,#1262a8 45%,#1e7f33 100%); }
.portal-banner__hills { position:absolute; bottom:0; left:0; right:0; height:50%; }
.pb-hill { position:absolute; bottom:0; border-radius:50% 50% 0 0; }
.pb-hill--1 { left:-5%; right:45%; height:100%; background:rgba(21,88,36,0.5); }
.pb-hill--2 { left:40%; right:-5%; height:80%; background:rgba(30,127,51,0.35); }
.portal-banner__content { position:relative; z-index:2; padding:24px 28px; }
.portal-banner__content h3 { font-family:'Playfair Display',serif; font-size:1.35rem; color:#fff; font-weight:700; margin-bottom:4px; }
.portal-banner__content p { font-size:0.85rem; color:rgba(255,255,255,0.75); }
.portal-banner__content strong { color:#ffe066; }
 
/* Cards info */
.portal-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; }
.portal-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
.portal-card__label { font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px; }
.portal-card__val { font-size:0.95rem; color:#1e293b; font-weight:600; }
.portal-card__val--big { font-family:'Playfair Display',serif; font-size:1.3rem; color:#0a3d6b; }
 
/* Status */
.portal-status { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:0.75rem; font-weight:600; }
.portal-status__dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
.portal-status--active   { background:#d1fae5; color:#065f46; }
.portal-status--inactive { background:#f1f5f9; color:#475569; }
 
/* Sección grupo familiar */
.portal-section { background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:20px 22px; }
.portal-section__title { font-family:'Playfair Display',serif; font-size:1rem; color:#0a3d6b; font-weight:700; margin-bottom:14px; }
 
.portal-ben-list { display:flex; flex-direction:column; gap:8px; }
.portal-ben-item { display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:9px; padding:10px 14px; flex-wrap:wrap; gap:6px; }
.portal-ben-item__info { display:flex; flex-direction:column; gap:2px; }
.portal-ben-item__name { font-size:0.9rem; font-weight:600; color:#1e293b; }
.portal-ben-item__rel  { font-size:0.75rem; color:#64748b; }
.portal-ben-item__meta { display:flex; gap:12px; font-size:0.78rem; color:#94a3b8; }
.portal-empty-text { font-size:0.84rem; color:#94a3b8; font-style:italic; }
 
/* Próximamente */
.portal-coming { display:flex; align-items:flex-start; gap:12px; background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:16px 18px; }
.portal-coming svg { width:20px; height:20px; color:#d97706; flex-shrink:0; margin-top:2px; }
.portal-coming__title { font-size:0.88rem; font-weight:700; color:#92400e; margin-bottom:3px; }
.portal-coming__sub   { font-size:0.8rem; color:#b45309; line-height:1.5; }
 
@media (max-width:640px) {
    .portal-cards { grid-template-columns:1fr 1fr; }
}
</style>