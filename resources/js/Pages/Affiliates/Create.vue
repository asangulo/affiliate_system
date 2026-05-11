<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    branches: Array,
    plans: Array,
});

const form = useForm({
    full_name:        '',
    id_number:        '',
    birth_date:       '',
    contract_number:  '',
    branch_id:        '',
    plan_id:          '',
    fee_amount:       '',
    email:            '',
    phone:            '',
    address:          '',
    neighborhood:     '',
    advisor_name:     '',
    affiliation_date: '',
    beneficiaries:    [],
});

// ── Beneficiarios ──────────────────────────────────────────
const addBeneficiary = () => {
    form.beneficiaries.push({
        full_name: '', relationship: '', age: '', auxilio: '',
    });
};

const removeBeneficiary = (index) => {
    form.beneficiaries.splice(index, 1);
};

const relationships = [
    'ESPOSO/A', 'HIJO/A', 'PADRE', 'MADRE', 'HERMANO/A',
    'ABUELO/A', 'NIETO/A', 'SUEGRO/A', 'YERNO', 'NUERA',
    'CUÑADO/A', 'SOBRINO/A', 'PRIMO/A', 'OTRO',
];

const submit = () => {
    form.post(route('affiliates.store'));
};
</script>

<template>
    <Head title="Nuevo Afiliado" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Nuevo Afiliado</h2>
            <p>Registra un nuevo afiliado con su grupo familiar</p>
        </template>

        <div class="af-form-wrap">

            <!-- Alerta de error general -->
            <div v-if="$page.props.flash?.error" class="af-alert af-alert--error">
                {{ $page.props.flash.error }}
            </div>

            <form @submit.prevent="submit" class="af-form">

                <!-- ══ SECCIÓN 1: DATOS PERSONALES ══ -->
                <div class="af-section">
                    <div class="af-section__head">
                        <div class="af-section__num">1</div>
                        <div>
                            <h3 class="af-section__title">Datos Personales</h3>
                            <p class="af-section__sub">Información básica del afiliado titular</p>
                        </div>
                    </div>

                    <div class="af-grid">
                        <div class="af-field af-field--full">
                            <label class="af-label">Nombre Completo <span class="af-req">*</span></label>
                            <input v-model="form.full_name" type="text"
                                class="af-input" :class="{ 'af-input--error': form.errors.full_name }"
                                placeholder="Ej: Juan Carlos Pérez Gómez"/>
                            <InputError :message="form.errors.full_name"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Cédula / Documento <span class="af-req">*</span></label>
                            <input v-model="form.id_number" type="text"
                                class="af-input" :class="{ 'af-input--error': form.errors.id_number }"
                                placeholder="Ej: 1075123456"/>
                            <InputError :message="form.errors.id_number"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Fecha de Nacimiento</label>
                            <input v-model="form.birth_date" type="date" class="af-input"/>
                            <InputError :message="form.errors.birth_date"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Teléfono</label>
                            <input v-model="form.phone" type="tel"
                                class="af-input" placeholder="Ej: 3001234567"/>
                            <InputError :message="form.errors.phone"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Correo Electrónico</label>
                            <input v-model="form.email" type="email"
                                class="af-input" placeholder="correo@ejemplo.com"/>
                            <InputError :message="form.errors.email"/>
                        </div>

                        <div class="af-field af-field--full">
                            <label class="af-label">Dirección</label>
                            <input v-model="form.address" type="text"
                                class="af-input" placeholder="Calle, carrera, número..."/>
                            <InputError :message="form.errors.address"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Barrio</label>
                            <input v-model="form.neighborhood" type="text"
                                class="af-input" placeholder="Nombre del barrio"/>
                            <InputError :message="form.errors.neighborhood"/>
                        </div>
                    </div>
                </div>

                <!-- ══ SECCIÓN 2: DATOS DEL PLAN ══ -->
                <div class="af-section">
                    <div class="af-section__head">
                        <div class="af-section__num">2</div>
                        <div>
                            <h3 class="af-section__title">Plan de Afiliación</h3>
                            <p class="af-section__sub">Datos del contrato y plan seleccionado</p>
                        </div>
                    </div>

                    <div class="af-grid">
                        <div class="af-field">
                            <label class="af-label">Sede <span class="af-req">*</span></label>
                            <select v-model="form.branch_id" class="af-input af-select"
                                :class="{ 'af-input--error': form.errors.branch_id }">
                                <option value="">Seleccione una sede</option>
                                <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                            <InputError :message="form.errors.branch_id"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Plan <span class="af-req">*</span></label>
                            <select v-model="form.plan_id" class="af-input af-select"
                                :class="{ 'af-input--error': form.errors.plan_id }">
                                <option value="">Seleccione un plan</option>
                                <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <InputError :message="form.errors.plan_id"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Valor Cuota <span class="af-req">*</span></label>
                            <div class="af-money-wrap">
                                <span class="af-money-prefix">$</span>
                                <input v-model="form.fee_amount" type="number" min="0" step="100"
                                    class="af-input af-input--money" placeholder="0"/>
                            </div>
                            <InputError :message="form.errors.fee_amount"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">N° Contrato</label>
                            <input v-model="form.contract_number" type="text"
                                class="af-input" placeholder="Ej: HZ-2024-001"/>
                            <InputError :message="form.errors.contract_number"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Fecha de Afiliación</label>
                            <input v-model="form.affiliation_date" type="date" class="af-input"/>
                            <InputError :message="form.errors.affiliation_date"/>
                        </div>

                        <div class="af-field">
                            <label class="af-label">Asesor</label>
                            <input v-model="form.advisor_name" type="text"
                                class="af-input" placeholder="Nombre del asesor"/>
                            <InputError :message="form.errors.advisor_name"/>
                        </div>
                    </div>
                </div>

                <!-- ══ SECCIÓN 3: GRUPO FAMILIAR ══ -->
                <div class="af-section">
                    <div class="af-section__head">
                        <div class="af-section__num">3</div>
                        <div>
                            <h3 class="af-section__title">Grupo Familiar / Beneficiarios</h3>
                            <p class="af-section__sub">Personas cubiertas por el plan (opcional)</p>
                        </div>
                    </div>

                    <!-- Lista de beneficiarios -->
                    <div v-if="form.beneficiaries.length" class="af-ben-list">
                        <div
                            v-for="(ben, index) in form.beneficiaries"
                            :key="index"
                            class="af-ben-card"
                        >
                            <div class="af-ben-card__header">
                                <span class="af-ben-card__num">Beneficiario {{ index + 1 }}</span>
                                <button type="button" @click="removeBeneficiary(index)" class="af-ben-card__remove">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    Quitar
                                </button>
                            </div>
                            <div class="af-grid">
                                <div class="af-field af-field--half">
                                    <label class="af-label">Nombre <span class="af-req">*</span></label>
                                    <input v-model="ben.full_name" type="text"
                                        class="af-input" placeholder="Nombre completo"/>
                                    <InputError :message="form.errors[`beneficiaries.${index}.full_name`]"/>
                                </div>
                                <div class="af-field af-field--half">
                                    <label class="af-label">Parentesco</label>
                                    <select v-model="ben.relationship" class="af-input af-select">
                                        <option value="">Seleccionar...</option>
                                        <option v-for="r in relationships" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                </div>
                                <div class="af-field">
                                    <label class="af-label">Edad</label>
                                    <input v-model="ben.age" type="text" class="af-input" placeholder="Ej: 35"/>
                                </div>
                                <div class="af-field">
                                    <label class="af-label">Auxilio</label>
                                    <input v-model="ben.auxilio" type="text" class="af-input" placeholder="Ej: $1.500.000"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder vacío -->
                    <div v-else class="af-ben-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p>Aún no hay beneficiarios agregados</p>
                    </div>

                    <button type="button" @click="addBeneficiary" class="af-btn-add-ben">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Agregar Beneficiario
                    </button>
                </div>

                <!-- ══ ACCIONES ══ -->
                <div class="af-actions">
                    <Link :href="route('affiliates.index')" class="af-btn af-btn--cancel">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing" class="af-btn af-btn--save">
                        <svg v-if="form.processing" class="af-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
                            <path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Registrar Afiliado' }}
                    </button>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; }

.af-form-wrap {
    max-width: 860px;
    margin: 0 auto;
    font-family: 'DM Sans', sans-serif;
}

.af-alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.88rem;
    margin-bottom: 20px;
    font-weight: 500;
}
.af-alert--error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

.af-form { display: flex; flex-direction: column; gap: 24px; }

/* ── Sección ── */
.af-section {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.af-section__head {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px 24px;
    border-bottom: 1px solid #f0f4f8;
    background: #f8fafc;
}

.af-section__num {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0a3d6b, #2eaa45);
    color: #fff;
    font-weight: 700;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.af-section__title {
    font-size: 1rem;
    font-weight: 700;
    color: #0a3d6b;
    margin-bottom: 2px;
}

.af-section__sub { font-size: 0.8rem; color: #64748b; }

/* ── Grid ── */
.af-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    padding: 22px 24px;
}

.af-field { display: flex; flex-direction: column; gap: 5px; }
.af-field--full { grid-column: 1 / -1; }
.af-field--half { grid-column: span 1; }

.af-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    letter-spacing: 0.03em;
}

.af-req { color: #ef4444; }

.af-input {
    padding: 9px 13px;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    font-size: 0.88rem;
    font-family: 'DM Sans', sans-serif;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    width: 100%;
}

.af-input:focus {
    border-color: #1565a8;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(21,101,168,0.1);
}

.af-input--error { border-color: #ef4444; }
.af-input::placeholder { color: #cbd5e1; }
.af-select { cursor: pointer; }

.af-money-wrap { position: relative; }
.af-money-prefix {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 0.88rem;
    color: #64748b;
    font-weight: 600;
    pointer-events: none;
}
.af-input--money { padding-left: 24px; }

/* ── Beneficiarios ── */
.af-ben-list { display: flex; flex-direction: column; gap: 14px; padding: 20px 24px 0; }

.af-ben-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.af-ben-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f0f4f8;
    border-bottom: 1px solid #e2e8f0;
}

.af-ben-card__num {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0a3d6b;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.af-ben-card__remove {
    display: flex;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
    color: #ef4444;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background 0.18s;
    font-family: 'DM Sans', sans-serif;
}

.af-ben-card__remove:hover { background: #fef2f2; }
.af-ben-card__remove svg { width: 14px; height: 14px; }

.af-ben-card .af-grid { padding: 16px; }

.af-ben-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 32px;
    color: #94a3b8;
    font-size: 0.85rem;
}

.af-ben-empty svg { width: 40px; height: 40px; opacity: 0.4; }

.af-btn-add-ben {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 16px 24px 20px;
    padding: 9px 18px;
    border: 1.5px dashed #2eaa45;
    border-radius: 9px;
    background: #f0fdf4;
    color: #166534;
    font-size: 0.85rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s;
}

.af-btn-add-ben:hover { background: #dcfce7; }
.af-btn-add-ben svg { width: 16px; height: 16px; }

/* ── Acciones ── */
.af-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding: 4px 0 8px;
}

.af-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 11px 24px;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.2s;
}

.af-btn--cancel {
    background: #f1f5f9;
    color: #475569;
    border: 1.5px solid #e2e8f0;
}
.af-btn--cancel:hover { background: #e2e8f0; }

.af-btn--save {
    background: linear-gradient(135deg, #0a3d6b, #1565a8);
    color: #fff;
    box-shadow: 0 4px 16px rgba(10,61,107,0.28);
}
.af-btn--save:hover:not(:disabled) { opacity: 0.91; transform: translateY(-1px); }
.af-btn--save:disabled { opacity: 0.65; cursor: not-allowed; }

.af-spinner {
    width: 16px; height: 16px;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 640px) {
    .af-grid { grid-template-columns: 1fr; }
    .af-field--full { grid-column: 1; }
    .af-actions { flex-direction: column-reverse; }
    .af-btn { width: 100%; justify-content: center; }
}
</style>