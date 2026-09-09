<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    affiliate:  Object,
    affiliates: Array,
    methods:    Object,
    years:      Array,
});

const hoy = new Date().toISOString().substring(0, 10);
const ahora = new Date();

const form = useForm({
    affiliate_id: props.affiliate?.id ?? '',
    period_year:  ahora.getFullYear(),
    period_month: ahora.getMonth() + 1,
    amount:       props.affiliate?.fee_amount ?? '',
    paid_at:      hoy,
    method:       'efectivo',
    reference:    '',
    notes:        '',
});

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
];

const money = (n) => new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP', maximumFractionDigits: 0,
}).format(n ?? 0);

// Cambiar de afiliado recarga la página para traer su resumen
const cambiarAfiliado = (e) => {
    const id = e.target.value;
    if (id) router.get(route('payments.create', { affiliate_id: id }));
};

const submit = () => form.post(route('payments.store'));
</script>

<template>
    <Head title="Registrar pago" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="pc-head">Registrar pago</h2>
        </template>

        <div class="pc-page">

            <!-- Estado del afiliado -->
            <aside v-if="affiliate" class="pc-side">
                <h3 class="pc-side__name">{{ affiliate.full_name }}</h3>
                <p class="pc-side__meta">
                    CC {{ affiliate.id_number }} · {{ affiliate.plan?.name }}
                </p>

                <div class="pc-side__stats">
                    <div>
                        <span class="pc-side__num">{{ affiliate.summary.paid }}</span>
                        <span class="pc-side__label">Cuotas pagadas</span>
                    </div>
                    <div>
                        <span class="pc-side__num"
                              :class="{ 'pc-side__num--late': affiliate.summary.overdue > 0 }">
                            {{ affiliate.summary.overdue }}
                        </span>
                        <span class="pc-side__label">En mora</span>
                    </div>
                </div>

                <div v-if="affiliate.summary.overdue > 0" class="pc-alert">
                    Debe {{ money(affiliate.summary.overdue_amount) }}
                    en {{ affiliate.summary.overdue }}
                    {{ affiliate.summary.overdue === 1 ? 'cuota' : 'cuotas' }}.
                </div>
                <div v-else class="pc-alert pc-alert--ok">
                    Está al día con sus pagos.
                </div>

                <div v-if="affiliate.summary.next_due" class="pc-next">
                    <span class="pc-next__label">Siguiente cuota sin pagar</span>
                    <strong>{{ affiliate.summary.next_due.label }}</strong>
                </div>

                <div v-if="affiliate.summary.last_payment" class="pc-last">
                    <span class="pc-next__label">Último pago recibido</span>
                    <strong>{{ affiliate.summary.last_payment.period }}</strong>
                    <span class="pc-side__label">
                        {{ affiliate.summary.last_payment.paid_at }} ·
                        {{ money(affiliate.summary.last_payment.amount) }}
                    </span>
                </div>
            </aside>

            <!-- Formulario -->
            <form class="pc-form" @submit.prevent="submit">

                <section class="pc-block">
                    <header class="pc-block__head">
                        <h3>Afiliado</h3>
                    </header>
                    <div class="pc-body">
                        <div class="pc-field">
                            <label>¿Quién está pagando? <span class="pc-req">*</span></label>
                            <select
                                :value="form.affiliate_id"
                                class="pc-input"
                                required
                                @change="cambiarAfiliado"
                            >
                                <option value="">Selecciona el afiliado…</option>
                                <option v-for="a in affiliates" :key="a.id" :value="a.id">
                                    {{ a.full_name }} — CC {{ a.id_number }}
                                </option>
                            </select>
                            <InputError :message="form.errors.affiliate_id" />
                        </div>
                    </div>
                </section>

                <section class="pc-block">
                    <header class="pc-block__head">
                        <h3>Cuota que se cubre</h3>
                        <p>Cada pago corresponde a un mes de servicio</p>
                    </header>
                    <div class="pc-body pc-grid">
                        <div class="pc-field">
                            <label>Mes <span class="pc-req">*</span></label>
                            <select v-model="form.period_month" class="pc-input" required>
                                <option v-for="(m, i) in meses" :key="i" :value="i + 1">{{ m }}</option>
                            </select>
                            <InputError :message="form.errors.period_month" />
                        </div>
                        <div class="pc-field">
                            <label>Año <span class="pc-req">*</span></label>
                            <select v-model="form.period_year" class="pc-input" required>
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                            <InputError :message="form.errors.period_year" />
                        </div>
                    </div>
                </section>

                <section class="pc-block">
                    <header class="pc-block__head">
                        <h3>Detalle del pago</h3>
                    </header>
                    <div class="pc-body pc-grid">
                        <div class="pc-field">
                            <label>Valor recibido <span class="pc-req">*</span></label>
                            <input v-model="form.amount" type="number" step="100" min="0"
                                   class="pc-input" placeholder="20000" required />
                            <InputError :message="form.errors.amount" />
                        </div>
                        <div class="pc-field">
                            <label>Fecha del pago <span class="pc-req">*</span></label>
                            <input v-model="form.paid_at" type="date" class="pc-input" required />
                            <InputError :message="form.errors.paid_at" />
                        </div>
                        <div class="pc-field">
                            <label>Forma de pago <span class="pc-req">*</span></label>
                            <select v-model="form.method" class="pc-input" required>
                                <option v-for="(label, key) in methods" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.method" />
                        </div>
                        <div class="pc-field">
                            <label>Referencia o recibo</label>
                            <input v-model="form.reference" type="text" class="pc-input"
                                   placeholder="Nº de recibo o comprobante" />
                            <InputError :message="form.errors.reference" />
                        </div>
                        <div class="pc-field pc-field--wide">
                            <label>Observaciones</label>
                            <textarea v-model="form.notes" rows="2" class="pc-input"
                                      placeholder="Anotaciones sobre este pago"></textarea>
                            <InputError :message="form.errors.notes" />
                        </div>
                    </div>
                </section>

                <div class="pc-actions">
                    <Link :href="route('payments.portfolio')" class="pc-btn pc-btn--cancel">
                        Cancelar
                    </Link>
                    <button type="submit" class="pc-btn pc-btn--save" :disabled="form.processing">
                        {{ form.processing ? 'Guardando…' : 'Registrar pago' }}
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.pc-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.pc-page { max-width: 1080px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: grid; grid-template-columns: 300px 1fr; gap: 22px; align-items: start; }

/* Panel lateral */
.pc-side { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px; position: sticky; top: 24px; }
.pc-side__name { font-size: 1rem; font-weight: 700; color: #0a3d6b; line-height: 1.3; }
.pc-side__meta { font-size: .76rem; color: #94a3b8; margin-top: -10px; }
.pc-side__stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 14px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
.pc-side__stats > div { display: flex; flex-direction: column; }
.pc-side__num { font-size: 1.5rem; font-weight: 700; color: #0f172a; line-height: 1; }
.pc-side__num--late { color: #dc2626; }
.pc-side__label { font-size: .72rem; color: #94a3b8; margin-top: 3px; }

.pc-alert { font-size: .8rem; padding: 10px 13px; border-radius: 9px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-weight: 500; }
.pc-alert--ok { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

.pc-next, .pc-last { display: flex; flex-direction: column; gap: 1px; }
.pc-next__label { font-size: .72rem; color: #94a3b8; }
.pc-next strong, .pc-last strong { font-size: .9rem; color: #0f172a; }

/* Formulario */
.pc-form { display: flex; flex-direction: column; gap: 18px; }
.pc-block { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.pc-block__head { padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.pc-block__head h3 { font-size: .93rem; font-weight: 700; color: #0a3d6b; }
.pc-block__head p { font-size: .76rem; color: #64748b; margin-top: 2px; }

.pc-body { padding: 18px 20px; }
.pc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.pc-field { display: flex; flex-direction: column; gap: 5px; }
.pc-field--wide { grid-column: 1 / -1; }
.pc-field label { font-size: .78rem; font-weight: 600; color: #374151; }
.pc-req { color: #ef4444; }

.pc-input { padding: 9px 13px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .88rem; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; width: 100%; resize: vertical; }
.pc-input:focus { border-color: #1565a8; background: #fff; box-shadow: 0 0 0 3px rgba(21,101,168,.1); }

.pc-actions { display: flex; justify-content: flex-end; gap: 12px; }
.pc-btn { display: inline-flex; align-items: center; padding: 11px 24px; border-radius: 10px; font-family: inherit; font-size: .9rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; }
.pc-btn--cancel { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.pc-btn--save { background: linear-gradient(135deg,#0a3d6b,#1565a8); color: #fff; box-shadow: 0 4px 16px rgba(10,61,107,.28); }
.pc-btn--save:disabled { opacity: .6; cursor: not-allowed; }

@media (max-width: 880px) {
    .pc-page { grid-template-columns: 1fr; }
    .pc-side { position: static; }
    .pc-grid { grid-template-columns: 1fr; }
    .pc-actions { flex-direction: column-reverse; }
    .pc-btn { width: 100%; justify-content: center; }
}
</style>
