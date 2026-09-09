<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    affiliate: Object,
    summary:   Object,
    payments:  Array,
    methods:   Object,
});

const money = (n) => new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP', maximumFractionDigits: 0,
}).format(n ?? 0);

const fecha = (d) => d
    ? new Date(d).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' })
    : '—';

const anular = (pago) => {
    if (!confirm(`¿Anular el pago de ${pago.period_label}? Volverá a quedar como cuota pendiente.`)) return;
    router.delete(route('payments.destroy', pago.id), { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Pagos de ${affiliate.full_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="pa-head">Pagos del afiliado</h2>
        </template>

        <div class="pa-page">

            <header class="pa-hero" :class="summary.up_to_date ? 'pa-hero--ok' : 'pa-hero--late'">
                <div>
                    <h3 class="pa-hero__name">{{ affiliate.full_name }}</h3>
                    <p class="pa-hero__meta">
                        CC {{ affiliate.id_number }} ·
                        Contrato {{ affiliate.contract_number ?? '—' }} ·
                        {{ affiliate.plan?.name }} ·
                        {{ affiliate.branch?.name }}
                    </p>
                </div>
                <Link :href="route('payments.create', { affiliate_id: affiliate.id })" class="pa-btn">
                    Registrar pago
                </Link>
            </header>

            <section class="pa-stats">
                <div class="pa-stat">
                    <span class="pa-stat__num">{{ money(affiliate.fee_amount) }}</span>
                    <span class="pa-stat__label">Cuota mensual</span>
                </div>
                <div class="pa-stat">
                    <span class="pa-stat__num">{{ summary.paid }} / {{ summary.expected }}</span>
                    <span class="pa-stat__label">Cuotas pagadas</span>
                </div>
                <div class="pa-stat" :class="{ 'pa-stat--late': summary.overdue > 0 }">
                    <span class="pa-stat__num">{{ summary.overdue }}</span>
                    <span class="pa-stat__label">En mora</span>
                </div>
                <div class="pa-stat" :class="{ 'pa-stat--late': summary.overdue_amount > 0 }">
                    <span class="pa-stat__num pa-stat__num--sm">{{ money(summary.overdue_amount) }}</span>
                    <span class="pa-stat__label">Saldo pendiente</span>
                </div>
            </section>

            <section v-if="summary.next_due" class="pa-next">
                <span class="pa-next__label">Siguiente cuota por cobrar</span>
                <strong class="pa-next__value">{{ summary.next_due.label }}</strong>
            </section>

            <section class="pa-history">
                <header class="pa-history__head">
                    <h3>Pagos registrados</h3>
                </header>

                <table v-if="payments.length" class="pa-table">
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th>Fecha de pago</th>
                            <th>Forma</th>
                            <th>Referencia</th>
                            <th>Registró</th>
                            <th class="pa-right">Valor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in payments" :key="p.id">
                            <td class="pa-strong">{{ p.period_label }}</td>
                            <td>{{ fecha(p.paid_at) }}</td>
                            <td class="pa-cap">{{ methods[p.method] ?? p.method }}</td>
                            <td class="pa-soft">{{ p.reference || '—' }}</td>
                            <td class="pa-soft">{{ p.registered_by?.name ?? 'Sistema' }}</td>
                            <td class="pa-right pa-amount">{{ money(p.amount) }}</td>
                            <td class="pa-right">
                                <button class="pa-link" @click="anular(p)">Anular</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-else class="pa-none">
                    Este afiliado todavía no tiene pagos registrados.
                </p>
            </section>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.pa-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.pa-page { max-width: 980px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 18px; }

.pa-hero { border-radius: 16px; padding: 22px 24px; display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap; }
.pa-hero--ok   { background: #f0fdf4; border: 1px solid #bbf7d0; }
.pa-hero--late { background: #fef2f2; border: 1px solid #fecaca; }
.pa-hero__name { font-size: 1.15rem; font-weight: 700; color: #0f172a; }
.pa-hero__meta { font-size: .8rem; color: #64748b; margin-top: 3px; }
.pa-btn { display: inline-flex; align-items: center; padding: 10px 20px; border-radius: 10px; background: linear-gradient(135deg,#0a3d6b,#1565a8); color: #fff; font-size: .85rem; font-weight: 600; text-decoration: none; box-shadow: 0 4px 16px rgba(10,61,107,.25); }

.pa-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.pa-stat { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #0a3d6b; border-radius: 14px; padding: 15px 18px; display: flex; flex-direction: column; gap: 2px; }
.pa-stat--late { border-left-color: #dc2626; }
.pa-stat__num { font-size: 1.35rem; font-weight: 700; color: #0f172a; line-height: 1.15; }
.pa-stat__num--sm { font-size: 1.05rem; }
.pa-stat__label { font-size: .75rem; color: #64748b; }

.pa-next { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 15px 20px; display: flex; align-items: baseline; gap: 12px; }
.pa-next__label { font-size: .78rem; color: #94a3b8; }
.pa-next__value { font-size: 1rem; color: #0a3d6b; }

.pa-history { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.pa-history__head { padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.pa-history__head h3 { font-size: .93rem; font-weight: 700; color: #0a3d6b; }

.pa-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.pa-table th { text-align: left; padding: 11px 18px; font-size: .72rem; color: #94a3b8; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.pa-table td { padding: 12px 18px; border-bottom: 1px solid #f1f5f9; color: #334155; }
.pa-table tr:last-child td { border-bottom: none; }
.pa-right { text-align: right; }
.pa-strong { font-weight: 600; color: #0f172a; }
.pa-soft { color: #94a3b8; }
.pa-cap { text-transform: capitalize; }
.pa-amount { font-weight: 700; color: #166534; }
.pa-link { background: none; border: none; font-family: inherit; font-size: .8rem; font-weight: 600; color: #dc2626; cursor: pointer; }
.pa-link:hover { text-decoration: underline; }
.pa-none { padding: 36px 20px; text-align: center; font-size: .86rem; color: #64748b; }

@media (max-width: 820px) {
    .pa-stats { grid-template-columns: repeat(2, 1fr); }
    .pa-btn { width: 100%; justify-content: center; }
}
</style>
