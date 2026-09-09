<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    filters:     Object,
    branches:    Array,
    overview:    Object,
    portfolio:   Object,
    byPlan:      Array,
    byBranch:    Array,
    income:      Array,
    growth:      Array,
    topAdvisors: Array,
    ageBuckets:  Array,
});

const from     = ref(props.filters.from);
const to       = ref(props.filters.to);
const branchId = ref(props.filters.branch_id ?? '');

let timer = null;
watch([from, to, branchId], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('reports.index'), {
            from: from.value, to: to.value, branch_id: branchId.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

const money = (n) => new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP', maximumFractionDigits: 0,
}).format(n ?? 0);

const num = (n) => new Intl.NumberFormat('es-CO').format(n ?? 0);

// Escala para las barras de recaudo
const maxIncome = computed(() =>
    Math.max(...props.income.map(i => i.total), 1));

const maxGrowth = computed(() =>
    Math.max(...props.growth.map(g => g.count), 1));

const maxPlan = computed(() =>
    Math.max(...props.byPlan.map(p => p.count), 1));

const maxAge = computed(() =>
    Math.max(...props.ageBuckets.map(a => a.count), 1));

const imprimir = () => window.print();
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="rp-head">Reportes</h2>
        </template>

        <div class="rp-page">

            <!-- Filtros -->
            <div class="rp-filters">
                <div class="rp-filter">
                    <label>Desde</label>
                    <input v-model="from" type="date" class="rp-input" />
                </div>
                <div class="rp-filter">
                    <label>Hasta</label>
                    <input v-model="to" type="date" class="rp-input" />
                </div>
                <div class="rp-filter">
                    <label>Sede</label>
                    <select v-model="branchId" class="rp-input">
                        <option value="">Todas</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
                <button class="rp-print" @click="imprimir">Imprimir</button>
            </div>

            <!-- Titulares -->
            <section class="rp-headline">
                <div class="rp-kpi rp-kpi--main">
                    <span class="rp-kpi__num">{{ num(overview.people_covered) }}</span>
                    <span class="rp-kpi__label">Personas cubiertas</span>
                    <span class="rp-kpi__sub">
                        {{ num(overview.affiliates_active) }} titulares +
                        {{ num(overview.beneficiaries) }} beneficiarios
                    </span>
                </div>

                <div class="rp-kpi-grid">
                    <div class="rp-kpi">
                        <span class="rp-kpi__num">{{ num(overview.affiliates_active) }}</span>
                        <span class="rp-kpi__label">Afiliados activos</span>
                    </div>
                    <div class="rp-kpi">
                        <span class="rp-kpi__num">{{ num(overview.new_this_month) }}</span>
                        <span class="rp-kpi__label">Nuevos este mes</span>
                    </div>
                    <div class="rp-kpi">
                        <span class="rp-kpi__num rp-kpi__num--money">{{ money(overview.income_potential) }}</span>
                        <span class="rp-kpi__label">Ingreso mensual esperado</span>
                    </div>
                    <div class="rp-kpi">
                        <span class="rp-kpi__num rp-kpi__num--money">{{ money(overview.income_month) }}</span>
                        <span class="rp-kpi__label">Recaudado en {{ overview.month_label }}</span>
                    </div>
                </div>
            </section>

            <!-- Cartera -->
            <section class="rp-card">
                <header class="rp-card__head">
                    <h3>Estado de cartera</h3>
                    <p>Comparación entre lo que debería haberse pagado y lo que se pagó</p>
                </header>
                <div class="rp-card__body">
                    <div class="rp-portfolio">
                        <div class="rp-gauge">
                            <div
                                class="rp-gauge__fill"
                                :style="{ width: overview.collection_rate + '%' }"
                            ></div>
                        </div>
                        <p class="rp-gauge__caption">
                            <strong>{{ overview.collection_rate }}%</strong>
                            de recaudo sobre lo esperado este mes
                        </p>
                    </div>

                    <div class="rp-split">
                        <div class="rp-split__item rp-split__item--ok">
                            <span class="rp-split__num">{{ num(portfolio.up_to_date) }}</span>
                            <span class="rp-split__label">Al día</span>
                        </div>
                        <div class="rp-split__item rp-split__item--late">
                            <span class="rp-split__num">{{ num(portfolio.overdue) }}</span>
                            <span class="rp-split__label">En mora</span>
                        </div>
                        <div class="rp-split__item rp-split__item--debt">
                            <span class="rp-split__num rp-split__num--sm">{{ money(portfolio.debt_total) }}</span>
                            <span class="rp-split__label">Deuda acumulada</span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="rp-cols">

                <!-- Recaudo mensual -->
                <section class="rp-card">
                    <header class="rp-card__head">
                        <h3>Recaudo mes a mes</h3>
                    </header>
                    <div class="rp-card__body">
                        <div v-if="income.length" class="rp-bars">
                            <div v-for="i in income" :key="i.label" class="rp-bar">
                                <div class="rp-bar__track">
                                    <div
                                        class="rp-bar__fill"
                                        :style="{ height: (i.total / maxIncome * 100) + '%' }"
                                        :title="money(i.total)"
                                    ></div>
                                </div>
                                <span class="rp-bar__label">{{ i.short }}</span>
                            </div>
                        </div>
                        <p v-else class="rp-none">Sin pagos en el periodo seleccionado.</p>
                    </div>
                </section>

                <!-- Afiliaciones nuevas -->
                <section class="rp-card">
                    <header class="rp-card__head">
                        <h3>Afiliaciones nuevas</h3>
                    </header>
                    <div class="rp-card__body">
                        <div v-if="growth.length" class="rp-bars">
                            <div v-for="g in growth" :key="g.label" class="rp-bar">
                                <div class="rp-bar__track">
                                    <div
                                        class="rp-bar__fill rp-bar__fill--green"
                                        :style="{ height: (g.count / maxGrowth * 100) + '%' }"
                                        :title="g.count + ' afiliados'"
                                    ></div>
                                </div>
                                <span class="rp-bar__label">{{ g.short }}</span>
                            </div>
                        </div>
                        <p v-else class="rp-none">Sin afiliaciones en el periodo.</p>
                    </div>
                </section>

            </div>

            <div class="rp-cols">

                <!-- Por plan -->
                <section class="rp-card">
                    <header class="rp-card__head">
                        <h3>Afiliados por plan</h3>
                        <p>Y cuánto aporta cada plan al mes</p>
                    </header>
                    <div class="rp-card__body">
                        <ul class="rp-rank">
                            <li v-for="p in byPlan" :key="p.name" class="rp-rank__item">
                                <div class="rp-rank__top">
                                    <span class="rp-rank__name">{{ p.name }}</span>
                                    <span class="rp-rank__value">{{ money(p.income) }}</span>
                                </div>
                                <div class="rp-rank__track">
                                    <div class="rp-rank__fill"
                                         :style="{ width: (p.count / maxPlan * 100) + '%' }"></div>
                                </div>
                                <span class="rp-rank__meta">
                                    {{ num(p.count) }} afiliados · cuota media {{ money(p.avg_fee) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Mayores deudores -->
                <section class="rp-card">
                    <header class="rp-card__head">
                        <h3>Mayores saldos pendientes</h3>
                        <p>A quién priorizar en la gestión de cobro</p>
                    </header>
                    <div class="rp-card__body">
                        <ul v-if="portfolio.top_debtors.length" class="rp-list">
                            <li v-for="d in portfolio.top_debtors" :key="d.id" class="rp-list__item">
                                <div>
                                    <span class="rp-list__name">{{ d.full_name }}</span>
                                    <span class="rp-list__meta">
                                        {{ d.months }} {{ d.months === 1 ? 'cuota' : 'cuotas' }}
                                    </span>
                                </div>
                                <span class="rp-list__amount">{{ money(d.amount) }}</span>
                            </li>
                        </ul>
                        <p v-else class="rp-none">Todos los afiliados están al día.</p>
                    </div>
                </section>

            </div>

            <div class="rp-cols">

                <!-- Por sede -->
                <section class="rp-card">
                    <header class="rp-card__head">
                        <h3>Distribución por sede</h3>
                    </header>
                    <div class="rp-card__body">
                        <table class="rp-table">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th class="rp-right">Afiliados</th>
                                    <th class="rp-right">Ingreso mensual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in byBranch" :key="b.name">
                                    <td>{{ b.name }}</td>
                                    <td class="rp-right">{{ num(b.count) }}</td>
                                    <td class="rp-right rp-strong">{{ money(b.income) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Asesores -->
                <section class="rp-card">
                    <header class="rp-card__head">
                        <h3>Asesores con más afiliaciones</h3>
                        <p>En el periodo seleccionado</p>
                    </header>
                    <div class="rp-card__body">
                        <ul v-if="topAdvisors.length" class="rp-list">
                            <li v-for="(a, i) in topAdvisors" :key="a.name" class="rp-list__item">
                                <div>
                                    <span class="rp-list__name">{{ a.name }}</span>
                                    <span class="rp-list__meta">{{ money(a.income) }} en cuotas</span>
                                </div>
                                <span class="rp-list__count">{{ num(a.count) }}</span>
                            </li>
                        </ul>
                        <p v-else class="rp-none">Sin datos de asesores en el periodo.</p>
                    </div>
                </section>

            </div>

            <!-- Edades -->
            <section class="rp-card">
                <header class="rp-card__head">
                    <h3>Edades de los beneficiarios</h3>
                    <p>Útil para dimensionar el riesgo del portafolio</p>
                </header>
                <div class="rp-card__body">
                    <div class="rp-ages">
                        <div v-for="a in ageBuckets" :key="a.range" class="rp-age">
                            <div class="rp-age__track">
                                <div class="rp-age__fill"
                                     :style="{ height: (a.count / maxAge * 100) + '%' }"></div>
                            </div>
                            <span class="rp-age__count">{{ num(a.count) }}</span>
                            <span class="rp-age__label">{{ a.range }}</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.rp-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.rp-page { max-width: 1180px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 18px; }

/* Filtros */
.rp-filters { display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 18px; }
.rp-filter { display: flex; flex-direction: column; gap: 4px; }
.rp-filter label { font-size: .72rem; color: #94a3b8; font-weight: 600; }
.rp-input { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .84rem; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; }
.rp-input:focus { border-color: #1565a8; background: #fff; }
.rp-print { margin-left: auto; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 9px 18px; font-family: inherit; font-size: .84rem; font-weight: 600; color: #475569; cursor: pointer; }
.rp-print:hover { background: #e2e8f0; }

/* Titulares */
.rp-headline { display: grid; grid-template-columns: 280px 1fr; gap: 16px; }
.rp-kpi { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 22px; display: flex; flex-direction: column; gap: 3px; }
.rp-kpi--main { background: linear-gradient(140deg, #0a3d6b, #1565a8); border: none; justify-content: center; }
.rp-kpi--main .rp-kpi__num { font-size: 2.6rem; color: #fff; }
.rp-kpi--main .rp-kpi__label { color: rgba(255,255,255,.85); font-size: .88rem; }
.rp-kpi--main .rp-kpi__sub { color: rgba(255,255,255,.6); font-size: .76rem; margin-top: 5px; }
.rp-kpi__num { font-size: 1.7rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
.rp-kpi__num--money { font-size: 1.25rem; }
.rp-kpi__label { font-size: .78rem; color: #64748b; }
.rp-kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }

/* Tarjetas */
.rp-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.rp-card__head { padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.rp-card__head h3 { font-size: .93rem; font-weight: 700; color: #0a3d6b; }
.rp-card__head p { font-size: .76rem; color: #64748b; margin-top: 2px; }
.rp-card__body { padding: 20px; }
.rp-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

/* Cartera */
.rp-portfolio { margin-bottom: 20px; }
.rp-gauge { height: 12px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.rp-gauge__fill { height: 100%; background: linear-gradient(90deg, #2eaa45, #4ade80); border-radius: 99px; transition: width .5s ease; }
.rp-gauge__caption { font-size: .82rem; color: #64748b; margin-top: 8px; }
.rp-gauge__caption strong { color: #0f172a; font-size: .95rem; }

.rp-split { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.rp-split__item { padding: 14px 16px; border-radius: 12px; display: flex; flex-direction: column; gap: 2px; }
.rp-split__item--ok   { background: #f0fdf4; }
.rp-split__item--late { background: #fef2f2; }
.rp-split__item--debt { background: #fffbeb; }
.rp-split__num { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
.rp-split__num--sm { font-size: 1.05rem; }
.rp-split__label { font-size: .75rem; color: #64748b; }

/* Barras verticales */
.rp-bars { display: flex; align-items: flex-end; gap: 8px; height: 160px; }
.rp-bar { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; }
.rp-bar__track { flex: 1; width: 100%; display: flex; align-items: flex-end; background: #f8fafc; border-radius: 6px 6px 0 0; }
.rp-bar__fill { width: 100%; background: linear-gradient(180deg, #1565a8, #0a3d6b); border-radius: 6px 6px 0 0; min-height: 3px; transition: height .5s ease; }
.rp-bar__fill--green { background: linear-gradient(180deg, #4ade80, #2eaa45); }
.rp-bar__label { font-size: .68rem; color: #94a3b8; }

/* Ranking */
.rp-rank { display: flex; flex-direction: column; gap: 14px; }
.rp-rank__top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; }
.rp-rank__name { font-size: .87rem; font-weight: 600; color: #0f172a; }
.rp-rank__value { font-size: .85rem; font-weight: 700; color: #0a3d6b; }
.rp-rank__track { height: 7px; background: #f1f5f9; border-radius: 99px; margin: 5px 0 3px; overflow: hidden; }
.rp-rank__fill { height: 100%; background: #1565a8; border-radius: 99px; transition: width .5s ease; }
.rp-rank__meta { font-size: .73rem; color: #94a3b8; }

/* Listas */
.rp-list { display: flex; flex-direction: column; }
.rp-list__item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.rp-list__item:last-child { border-bottom: none; }
.rp-list__name { display: block; font-size: .86rem; font-weight: 600; color: #0f172a; }
.rp-list__meta { display: block; font-size: .73rem; color: #94a3b8; margin-top: 1px; }
.rp-list__amount { font-size: .88rem; font-weight: 700; color: #b91c1c; white-space: nowrap; }
.rp-list__count { font-size: 1.1rem; font-weight: 700; color: #0a3d6b; }

/* Tabla */
.rp-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.rp-table th { text-align: left; padding: 8px 0; font-size: .72rem; color: #94a3b8; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.rp-table td { padding: 10px 0; border-bottom: 1px solid #f1f5f9; color: #334155; }
.rp-table tr:last-child td { border-bottom: none; }
.rp-right { text-align: right; }
.rp-strong { font-weight: 600; color: #0a3d6b; }

/* Edades */
.rp-ages { display: flex; align-items: flex-end; gap: 16px; height: 170px; }
.rp-age { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; height: 100%; }
.rp-age__track { flex: 1; width: 100%; display: flex; align-items: flex-end; background: #f8fafc; border-radius: 8px 8px 0 0; }
.rp-age__fill { width: 100%; background: linear-gradient(180deg, #93c5fd, #1565a8); border-radius: 8px 8px 0 0; min-height: 3px; transition: height .5s ease; }
.rp-age__count { font-size: .85rem; font-weight: 700; color: #0f172a; }
.rp-age__label { font-size: .7rem; color: #94a3b8; }

.rp-none { font-size: .85rem; color: #94a3b8; text-align: center; padding: 28px 0; }

@media (max-width: 900px) {
    .rp-headline { grid-template-columns: 1fr; }
    .rp-cols { grid-template-columns: 1fr; }
    .rp-split { grid-template-columns: 1fr; }
    .rp-print { margin-left: 0; width: 100%; }
}

@media print {
    .rp-filters { display: none; }
    .rp-card { break-inside: avoid; }
}
</style>
