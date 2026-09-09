<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    affiliate: Object,
    summary:   Object,
    payments:  Array,
});

const money = (n) => new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP', maximumFractionDigits: 0,
}).format(n ?? 0);

const fecha = (d) => d
    ? new Date(d).toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' })
    : '—';
</script>

<template>
    <Head title="Mis pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="mp-head">Mis pagos</h2>
        </template>

        <div class="mp-page">

            <!-- Sin afiliación vinculada -->
            <div v-if="!affiliate" class="mp-empty">
                <p class="mp-empty__title">Todavía no hay una afiliación vinculada a tu cuenta</p>
                <p class="mp-empty__text">
                    Comunícate con la oficina para que asocien tu contrato a este usuario
                    y puedas consultar tus pagos aquí.
                </p>
            </div>

            <template v-else>

                <!-- Estado -->
                <section
                    class="mp-status"
                    :class="summary.up_to_date ? 'mp-status--ok' : 'mp-status--late'"
                >
                    <div class="mp-status__main">
                        <p class="mp-status__title">
                            {{ summary.up_to_date ? 'Estás al día' : 'Tienes cuotas pendientes' }}
                        </p>
                        <p v-if="!summary.up_to_date" class="mp-status__text">
                            Debes {{ money(summary.overdue_amount) }} correspondientes a
                            {{ summary.overdue }}
                            {{ summary.overdue === 1 ? 'cuota' : 'cuotas' }}.
                        </p>
                        <p v-else class="mp-status__text">
                            Gracias por mantener tu servicio activo.
                        </p>
                    </div>
                    <div v-if="summary.next_due" class="mp-status__next">
                        <span class="mp-label">Próxima cuota</span>
                        <strong>{{ summary.next_due.label }}</strong>
                    </div>
                </section>

                <!-- Datos del plan -->
                <section class="mp-plan">
                    <div>
                        <span class="mp-label">Contrato</span>
                        <strong>{{ affiliate.contract_number ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="mp-label">Plan</span>
                        <strong>{{ affiliate.plan?.name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="mp-label">Cuota mensual</span>
                        <strong>{{ money(affiliate.fee_amount) }}</strong>
                    </div>
                    <div>
                        <span class="mp-label">Sede</span>
                        <strong>{{ affiliate.branch?.name ?? '—' }}</strong>
                    </div>
                    <div>
                        <span class="mp-label">Cuotas pagadas</span>
                        <strong>{{ summary.paid }} de {{ summary.expected }}</strong>
                    </div>
                </section>

                <!-- Historial -->
                <section class="mp-history">
                    <h3 class="mp-history__title">Historial de pagos</h3>

                    <div v-if="payments.length" class="mp-list">
                        <article v-for="p in payments" :key="p.id" class="mp-item">
                            <div class="mp-item__period">
                                <strong>{{ p.period_label }}</strong>
                                <span class="mp-label">Pagado el {{ fecha(p.paid_at) }}</span>
                            </div>
                            <div class="mp-item__right">
                                <span class="mp-item__amount">{{ money(p.amount) }}</span>
                                <span class="mp-item__method">{{ p.method }}</span>
                            </div>
                        </article>
                    </div>

                    <p v-else class="mp-none">
                        Aún no hay pagos registrados en tu contrato.
                    </p>
                </section>

            </template>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.mp-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.mp-page { max-width: 780px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 20px; }

.mp-label { display: block; font-size: .72rem; color: #94a3b8; }

/* Estado */
.mp-status { border-radius: 16px; padding: 22px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.mp-status--ok   { background: #f0fdf4; border: 1px solid #bbf7d0; }
.mp-status--late { background: #fef2f2; border: 1px solid #fecaca; }
.mp-status__title { font-size: 1.15rem; font-weight: 700; }
.mp-status--ok .mp-status__title   { color: #166534; }
.mp-status--late .mp-status__title { color: #b91c1c; }
.mp-status__text { font-size: .88rem; color: #475569; margin-top: 3px; }
.mp-status__next { text-align: right; }
.mp-status__next strong { font-size: 1rem; color: #0f172a; }

/* Plan */
.mp-plan { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 18px; }
.mp-plan strong { font-size: .92rem; color: #0f172a; }

/* Historial */
.mp-history { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.mp-history__title { font-size: .93rem; font-weight: 700; color: #0a3d6b; padding: 16px 22px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.mp-list { display: flex; flex-direction: column; }
.mp-item { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 22px; border-bottom: 1px solid #f1f5f9; }
.mp-item:last-child { border-bottom: none; }
.mp-item__period strong { font-size: .92rem; color: #0f172a; display: block; }
.mp-item__right { text-align: right; }
.mp-item__amount { display: block; font-size: .95rem; font-weight: 700; color: #166534; }
.mp-item__method { font-size: .72rem; color: #94a3b8; text-transform: capitalize; }
.mp-none { padding: 34px 22px; text-align: center; font-size: .86rem; color: #64748b; }

/* Vacío */
.mp-empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px 28px; text-align: center; }
.mp-empty__title { font-size: 1rem; font-weight: 700; color: #334155; }
.mp-empty__text  { font-size: .86rem; color: #64748b; margin-top: 6px; max-width: 420px; margin-inline: auto; }

@media (max-width: 620px) {
    .mp-status { flex-direction: column; align-items: flex-start; }
    .mp-status__next { text-align: left; }
}
</style>
