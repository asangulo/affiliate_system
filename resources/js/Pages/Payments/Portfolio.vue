<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    rows:     Array,
    filters:  Object,
    branches: Array,
    totals:   Object,
});

const search   = ref(props.filters.search ?? '');
const branchId = ref(props.filters.branch_id ?? '');
const status   = ref(props.filters.status ?? '');
const sort     = ref(props.filters.sort ?? '');

let timer = null;
watch([search, branchId, status, sort], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('payments.portfolio'), {
            search: search.value,
            branch_id: branchId.value,
            status: status.value,
            sort: sort.value,
        }, { preserveState: true, replace: true });
    }, 350);
});

const money = (n) => new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP', maximumFractionDigits: 0,
}).format(n ?? 0);
</script>

<template>
    <Head title="Cartera" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="pf-head">Cartera</h2>
        </template>

        <div class="pf-page">

            <!-- Cifras -->
            <div class="pf-stats">
                <div class="pf-stat">
                    <span class="pf-stat__num">{{ totals.affiliates }}</span>
                    <span class="pf-stat__label">Afiliados activos</span>
                </div>
                <div class="pf-stat pf-stat--ok">
                    <span class="pf-stat__num">{{ totals.current }}</span>
                    <span class="pf-stat__label">Al día</span>
                </div>
                <div class="pf-stat pf-stat--late">
                    <span class="pf-stat__num">{{ totals.overdue }}</span>
                    <span class="pf-stat__label">En mora</span>
                </div>
                <div class="pf-stat pf-stat--money">
                    <span class="pf-stat__num pf-stat__num--sm">{{ money(totals.debt_amount) }}</span>
                    <span class="pf-stat__label">Por recaudar</span>
                </div>
            </div>

            <!-- Filtros -->
            <div class="pf-bar">
                <input v-model="search" type="search" class="pf-input pf-input--grow"
                       placeholder="Buscar por nombre, cédula o contrato" />
                <select v-model="branchId" class="pf-input">
                    <option value="">Todas las sedes</option>
                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                <select v-model="status" class="pf-input">
                    <option value="">Todos</option>
                    <option value="overdue">Solo en mora</option>
                    <option value="current">Solo al día</option>
                </select>
                <select v-model="sort" class="pf-input">
                    <option value="">Por nombre</option>
                    <option value="debt">Mayor deuda</option>
                </select>
                <Link :href="route('payments.create')" class="pf-btn pf-btn--primary">
                    Registrar pago
                </Link>
            </div>

            <!-- Tabla -->
            <div v-if="rows.length" class="pf-table-wrap">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>Afiliado</th>
                            <th>Sede</th>
                            <th class="pf-num">Cuota</th>
                            <th class="pf-num">Pagadas</th>
                            <th class="pf-num">En mora</th>
                            <th class="pf-num">Debe</th>
                            <th>Próximo pago</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in rows" :key="row.id"
                            :class="{ 'pf-row--late': !row.up_to_date }">
                            <td>
                                <span class="pf-name">{{ row.full_name }}</span>
                                <span class="pf-meta">CC {{ row.id_number }}</span>
                            </td>
                            <td class="pf-soft">{{ row.branch ?? '—' }}</td>
                            <td class="pf-num">{{ money(row.fee_amount) }}</td>
                            <td class="pf-num">{{ row.paid }} / {{ row.expected }}</td>
                            <td class="pf-num">
                                <span v-if="row.overdue" class="pf-badge pf-badge--late">
                                    {{ row.overdue }}
                                </span>
                                <span v-else class="pf-badge pf-badge--ok">Al día</span>
                            </td>
                            <td class="pf-num pf-debt">
                                {{ row.overdue_amount > 0 ? money(row.overdue_amount) : '—' }}
                            </td>
                            <td class="pf-soft">
                                {{ row.next_due ? row.next_due.label : 'Sin pendientes' }}
                            </td>
                            <td class="pf-actions">
                                <Link :href="route('payments.affiliate', row.id)" class="pf-link">
                                    Ver
                                </Link>
                                <Link :href="route('payments.create', { affiliate_id: row.id })"
                                      class="pf-link pf-link--strong">
                                    Registrar
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="pf-empty">
                <p class="pf-empty__title">Sin resultados</p>
                <p class="pf-empty__text">Ningún afiliado coincide con estos filtros.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.pf-page { max-width: 1240px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 20px; }
.pf-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }

.pf-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.pf-stat { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #0a3d6b; border-radius: 14px; padding: 16px 20px; display: flex; flex-direction: column; gap: 2px; }
.pf-stat--ok    { border-left-color: #2eaa45; }
.pf-stat--late  { border-left-color: #dc2626; }
.pf-stat--money { border-left-color: #b45309; }
.pf-stat__num { font-size: 1.7rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
.pf-stat__num--sm { font-size: 1.15rem; }
.pf-stat__label { font-size: .78rem; color: #64748b; }

.pf-bar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.pf-input { padding: 9px 13px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .85rem; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; }
.pf-input:focus { border-color: #1565a8; background: #fff; box-shadow: 0 0 0 3px rgba(21,101,168,.1); }
.pf-input--grow { flex: 1; min-width: 220px; }

.pf-btn { display: inline-flex; align-items: center; padding: 10px 20px; border-radius: 10px; font-size: .85rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; font-family: inherit; margin-left: auto; }
.pf-btn--primary { background: linear-gradient(135deg,#0a3d6b,#1565a8); color: #fff; box-shadow: 0 4px 16px rgba(10,61,107,.25); }
.pf-btn--primary:hover { opacity: .92; }

.pf-table-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow-x: auto; }
.pf-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.pf-table thead th { text-align: left; padding: 13px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: .74rem; font-weight: 600; color: #64748b; white-space: nowrap; }
.pf-table tbody td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.pf-table tbody tr:last-child td { border-bottom: none; }
.pf-table tbody tr:hover { background: #f8fafc; }
.pf-row--late { background: #fffbfb; }

.pf-num { text-align: right; white-space: nowrap; }
.pf-name { display: block; font-weight: 600; color: #0f172a; }
.pf-meta { display: block; font-size: .72rem; color: #94a3b8; margin-top: 1px; }
.pf-soft { color: #64748b; }
.pf-debt { font-weight: 600; color: #b91c1c; }

.pf-badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: .72rem; font-weight: 700; }
.pf-badge--ok   { background: #dcfce7; color: #166534; }
.pf-badge--late { background: #fee2e2; color: #b91c1c; }

.pf-actions { text-align: right; white-space: nowrap; }
.pf-link { font-size: .8rem; font-weight: 600; color: #64748b; text-decoration: none; margin-left: 12px; }
.pf-link:hover { text-decoration: underline; }
.pf-link--strong { color: #1565a8; }

.pf-empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px 24px; text-align: center; }
.pf-empty__title { font-size: 1rem; font-weight: 700; color: #334155; }
.pf-empty__text { font-size: .85rem; color: #64748b; margin-top: 4px; }

@media (max-width: 900px) {
    .pf-stats { grid-template-columns: repeat(2, 1fr); }
    .pf-btn { margin-left: 0; width: 100%; justify-content: center; }
}
</style>
