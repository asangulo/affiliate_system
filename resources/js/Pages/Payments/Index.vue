<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    payments: Object,
    filters:  Object,
    stats:    Object,
    years:    Array,
});

const search = ref(props.filters.search ?? '');
const year   = ref(props.filters.year ?? '');
const month  = ref(props.filters.month ?? '');

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
];

let timer = null;
watch([search, year, month], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('payments.index'), {
            search: search.value, year: year.value, month: month.value,
        }, { preserveState: true, replace: true });
    }, 350);
});

const money = (n) => new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP', maximumFractionDigits: 0,
}).format(n ?? 0);

const fecha = (d) => d
    ? new Date(d).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' })
    : '—';
</script>

<template>
    <Head title="Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="pi-head">Pagos recibidos</h2>
        </template>

        <div class="pi-page">

            <div class="pi-stats">
                <div class="pi-stat">
                    <span class="pi-stat__num">{{ money(stats.month_total) }}</span>
                    <span class="pi-stat__label">Recaudado en {{ stats.period }}</span>
                </div>
                <div class="pi-stat">
                    <span class="pi-stat__num">{{ stats.month_count }}</span>
                    <span class="pi-stat__label">Pagos del mes</span>
                </div>
                <div class="pi-stat">
                    <span class="pi-stat__num">{{ money(stats.year_total) }}</span>
                    <span class="pi-stat__label">Acumulado del año</span>
                </div>
            </div>

            <div class="pi-bar">
                <input v-model="search" type="search" class="pi-input pi-input--grow"
                       placeholder="Buscar afiliado por nombre, cédula o contrato" />
                <select v-model="month" class="pi-input">
                    <option value="">Todo mes</option>
                    <option v-for="(m, i) in meses" :key="i" :value="i + 1">{{ m }}</option>
                </select>
                <select v-model="year" class="pi-input">
                    <option value="">Todo año</option>
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
                <Link :href="route('payments.create')" class="pi-btn">Registrar pago</Link>
            </div>

            <div v-if="payments.data.length" class="pi-table-wrap">
                <table class="pi-table">
                    <thead>
                        <tr>
                            <th>Afiliado</th>
                            <th>Cuota</th>
                            <th>Fecha</th>
                            <th>Forma</th>
                            <th>Registró</th>
                            <th class="pi-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in payments.data" :key="p.id">
                            <td>
                                <Link :href="route('payments.affiliate', p.affiliate.id)" class="pi-name">
                                    {{ p.affiliate.full_name }}
                                </Link>
                                <span class="pi-meta">CC {{ p.affiliate.id_number }}</span>
                            </td>
                            <td class="pi-strong">{{ p.period_label }}</td>
                            <td>{{ fecha(p.paid_at) }}</td>
                            <td class="pi-cap">{{ p.method }}</td>
                            <td class="pi-soft">{{ p.registered_by?.name ?? 'Sistema' }}</td>
                            <td class="pi-right pi-amount">{{ money(p.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="pi-empty">
                <p class="pi-empty__title">Sin pagos registrados</p>
                <p class="pi-empty__text">Ajusta los filtros o registra el primer pago.</p>
            </div>

            <nav v-if="payments.last_page > 1" class="pi-pager">
                <Link
                    v-for="link in payments.links"
                    :key="link.label"
                    :href="link.url ?? ''"
                    class="pi-pager__item"
                    :class="{
                        'pi-pager__item--on': link.active,
                        'pi-pager__item--off': !link.url,
                    }"
                    v-html="link.label"
                />
            </nav>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.pi-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.pi-page { max-width: 1080px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 18px; }

.pi-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.pi-stat { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #2eaa45; border-radius: 14px; padding: 16px 20px; }
.pi-stat__num { display: block; font-size: 1.4rem; font-weight: 700; color: #0f172a; line-height: 1.15; }
.pi-stat__label { font-size: .77rem; color: #64748b; }

.pi-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.pi-input { padding: 9px 13px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .85rem; font-family: inherit; background: #f8fafc; outline: none; }
.pi-input:focus { border-color: #1565a8; background: #fff; }
.pi-input--grow { flex: 1; min-width: 220px; }
.pi-btn { margin-left: auto; padding: 10px 20px; border-radius: 10px; background: linear-gradient(135deg,#0a3d6b,#1565a8); color: #fff; font-size: .85rem; font-weight: 600; text-decoration: none; }

.pi-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow-x: auto; }
.pi-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.pi-table th { text-align: left; padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: .73rem; color: #64748b; font-weight: 600; }
.pi-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; }
.pi-table tr:last-child td { border-bottom: none; }
.pi-right { text-align: right; }
.pi-name { display: block; font-weight: 600; color: #0f172a; text-decoration: none; }
.pi-name:hover { color: #1565a8; text-decoration: underline; }
.pi-meta { display: block; font-size: .72rem; color: #94a3b8; }
.pi-strong { font-weight: 600; }
.pi-soft { color: #94a3b8; }
.pi-cap { text-transform: capitalize; }
.pi-amount { font-weight: 700; color: #166534; }

.pi-empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 44px 24px; text-align: center; }
.pi-empty__title { font-size: 1rem; font-weight: 700; color: #334155; }
.pi-empty__text { font-size: .85rem; color: #64748b; margin-top: 4px; }

.pi-pager { display: flex; gap: 5px; justify-content: center; flex-wrap: wrap; }
.pi-pager__item { padding: 7px 12px; border-radius: 8px; font-size: .82rem; color: #475569; background: #fff; border: 1px solid #e2e8f0; text-decoration: none; }
.pi-pager__item--on { background: #0a3d6b; color: #fff; border-color: #0a3d6b; }
.pi-pager__item--off { opacity: .4; pointer-events: none; }

@media (max-width: 780px) {
    .pi-stats { grid-template-columns: 1fr; }
    .pi-btn { margin-left: 0; width: 100%; text-align: center; }
}
</style>
