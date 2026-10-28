<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    plans:   Array,
    filters: Object,
    stats:   Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

let timer = null;
watch([search, status], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('plans.index'),
            { search: search.value, status: status.value },
            { preserveState: true, replace: true });
    }, 350);
});

const money = (v) => '$' + Number(v ?? 0).toLocaleString('es-CO');

// ── Modal crear / editar ───────────────────────────────────
const showModal = ref(false);
const editing   = ref(null);
const form = useForm({ name: '', base_price: '', description: '', is_active: true });

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (plan) => {
    editing.value = plan;
    form.clearErrors();
    form.name        = plan.name;
    form.base_price  = Number(plan.base_price);
    form.description = plan.description ?? '';
    form.is_active   = !!plan.is_active;
    showModal.value  = true;
};

const closeModal = () => { showModal.value = false; };

const submit = () => {
    const opciones = { preserveScroll: true, onSuccess: closeModal };

    if (editing.value) {
        form.put(route('plans.update', editing.value.id), opciones);
    } else {
        form.post(route('plans.store'), opciones);
    }
};

const toggle = (plan) => {
    router.patch(route('plans.toggle', plan.id), {}, { preserveScroll: true });
};

const destroy = (plan) => {
    if (!confirm(`¿Eliminar el plan "${plan.name}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route('plans.destroy', plan.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Planes" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Planes</h2>
            <p>Administra los planes que se ofrecen a los afiliados</p>
        </template>

        <div class="pl-page">
            <!-- Resumen -->
            <div class="pl-stats">
                <div class="pl-stat">
                    <span class="pl-stat__num">{{ stats.total }}</span>
                    <span class="pl-stat__label">Planes registrados</span>
                </div>
                <div class="pl-stat pl-stat--ok">
                    <span class="pl-stat__num">{{ stats.active }}</span>
                    <span class="pl-stat__label">Activos</span>
                </div>
                <div class="pl-stat pl-stat--off">
                    <span class="pl-stat__num">{{ stats.inactive }}</span>
                    <span class="pl-stat__label">Inactivos</span>
                </div>
            </div>

            <!-- Barra -->
            <div class="pl-bar">
                <div class="pl-bar__filters">
                    <input v-model="search" type="search" class="pl-input" placeholder="Buscar por nombre o descripción"/>
                    <select v-model="status" class="pl-input">
                        <option value="">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
                <button type="button" class="pl-btn pl-btn--primary" @click="openCreate">Nuevo plan</button>
            </div>

            <p class="pl-note">
                El valor base se sugiere al registrar un afiliado nuevo. Cambiarlo no modifica la cuota de los afiliados que ya existen.
            </p>

            <!-- Listado -->
            <div v-if="plans.length" class="pl-table-wrap">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Valor base</th>
                            <th>Afiliados</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="plan in plans" :key="plan.id" :class="{ 'pl-row--off': !plan.is_active }">
                            <td>
                                <p class="pl-name">{{ plan.name }}</p>
                                <p v-if="plan.description" class="pl-desc">{{ plan.description }}</p>
                            </td>
                            <td>{{ money(plan.base_price) }}</td>
                            <td>{{ plan.affiliates_count }}</td>
                            <td>
                                <span class="pl-pill" :class="plan.is_active ? 'pl-pill--ok' : 'pl-pill--off'">
                                    {{ plan.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="pl-actions">
                                    <button class="pl-link" @click="openEdit(plan)">Editar</button>
                                    <button class="pl-link" @click="toggle(plan)">
                                        {{ plan.is_active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                    <button v-if="plan.affiliates_count === 0" class="pl-link pl-link--danger" @click="destroy(plan)">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="pl-empty">No se encontraron planes.</p>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="pl-overlay" @click.self="closeModal">
                <form class="pl-modal" @submit.prevent="submit">
                    <div class="pl-modal__head">
                        <h3>{{ editing ? 'Editar plan' : 'Nuevo plan' }}</h3>
                        <button type="button" class="pl-modal__close" @click="closeModal">✕</button>
                    </div>

                    <div class="pl-modal__body">
                        <div class="pl-field">
                            <label class="pl-label">Nombre <span class="pl-req">*</span></label>
                            <input v-model="form.name" type="text" maxlength="120" class="pl-input" required/>
                            <InputError :message="form.errors.name"/>
                        </div>
                        <div class="pl-field">
                            <label class="pl-label">Valor base de la cuota <span class="pl-req">*</span></label>
                            <input v-model="form.base_price" type="number" min="0" step="100" class="pl-input" required/>
                            <InputError :message="form.errors.base_price"/>
                        </div>
                        <div class="pl-field">
                            <label class="pl-label">Descripción</label>
                            <textarea v-model="form.description" rows="3" maxlength="1000" class="pl-input"
                                placeholder="Qué incluye este plan"/>
                            <InputError :message="form.errors.description"/>
                        </div>
                        <label class="pl-check">
                            <input v-model="form.is_active" type="checkbox"/>
                            Plan activo (se ofrece al registrar afiliados)
                        </label>
                    </div>

                    <div class="pl-modal__foot">
                        <button type="button" class="pl-btn" @click="closeModal">Cancelar</button>
                        <button type="submit" class="pl-btn pl-btn--primary" :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : (editing ? 'Guardar cambios' : 'Crear plan') }}
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.pl-page { display:flex; flex-direction:column; gap:18px; font-family:'DM Sans',sans-serif; }

.pl-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.pl-stat { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:16px 18px; display:flex; flex-direction:column; gap:2px; }
.pl-stat__num { font-size:1.6rem; font-weight:700; color:#0a3d6b; }
.pl-stat__label { font-size:0.78rem; color:#64748b; }
.pl-stat--ok .pl-stat__num { color:#15803d; }
.pl-stat--off .pl-stat__num { color:#64748b; }

.pl-bar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
.pl-bar__filters { display:flex; gap:10px; flex:1; min-width:260px; }
.pl-bar__filters .pl-input:first-child { flex:1; }
.pl-note { font-size:0.8rem; color:#64748b; }

.pl-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.88rem; font-family:inherit; color:#1e293b; background:#f8fafc; outline:none; width:100%; }
.pl-input:focus { border-color:#1565a8; background:#fff; box-shadow:0 0 0 3px rgba(21,101,168,0.1); }
.pl-bar__filters select.pl-input { width:auto; }

.pl-btn { padding:9px 18px; border-radius:9px; border:1.5px solid #e2e8f0; background:#f1f5f9; color:#475569; font-size:0.85rem; font-weight:600; font-family:inherit; cursor:pointer; }
.pl-btn--primary { background:linear-gradient(135deg,#0a3d6b,#1565a8); color:#fff; border-color:transparent; }
.pl-btn:disabled { opacity:0.65; cursor:not-allowed; }

.pl-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow-x:auto; }
.pl-table { width:100%; border-collapse:collapse; font-size:0.86rem; }
.pl-table th { text-align:left; padding:12px 16px; background:#f8fafc; color:#64748b; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em; border-bottom:1px solid #e2e8f0; }
.pl-table td { padding:12px 16px; border-bottom:1px solid #f1f5f9; color:#1e293b; vertical-align:top; }
.pl-row--off td { opacity:0.6; }
.pl-name { font-weight:600; color:#0a3d6b; }
.pl-desc { font-size:0.78rem; color:#64748b; margin-top:2px; max-width:420px; }

.pl-pill { display:inline-block; padding:3px 10px; border-radius:99px; font-size:0.72rem; font-weight:600; }
.pl-pill--ok { background:#d1fae5; color:#065f46; }
.pl-pill--off { background:#f1f5f9; color:#475569; }

.pl-actions { display:flex; gap:12px; flex-wrap:wrap; }
.pl-link { background:none; border:none; padding:0; font-size:0.8rem; font-weight:600; color:#1565a8; cursor:pointer; font-family:inherit; }
.pl-link:hover { text-decoration:underline; }
.pl-link--danger { color:#dc2626; }
.pl-empty { text-align:center; padding:40px; color:#94a3b8; font-size:0.88rem; }

.pl-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); z-index:500; display:flex; align-items:center; justify-content:center; padding:20px; }
.pl-modal { background:#fff; border-radius:18px; width:100%; max-width:520px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(0,0,0,0.3); overflow:hidden; font-family:'DM Sans',sans-serif; }
.pl-modal__head { display:flex; justify-content:space-between; align-items:center; padding:16px 22px; border-bottom:1px solid #f0f4f8; background:#f8fafc; }
.pl-modal__head h3 { font-size:1.05rem; font-weight:700; color:#0a3d6b; }
.pl-modal__close { background:none; border:none; cursor:pointer; font-size:1.1rem; color:#94a3b8; }
.pl-modal__body { padding:20px 22px; display:flex; flex-direction:column; gap:14px; overflow-y:auto; }
.pl-modal__foot { display:flex; justify-content:flex-end; gap:10px; padding:14px 22px; border-top:1px solid #f0f4f8; }
.pl-field { display:flex; flex-direction:column; gap:5px; }
.pl-label { font-size:0.78rem; font-weight:600; color:#374151; }
.pl-req { color:#ef4444; }
.pl-check { display:flex; align-items:center; gap:8px; font-size:0.85rem; color:#475569; }

@media (max-width:640px) {
    .pl-stats { grid-template-columns:1fr; }
}
</style>
