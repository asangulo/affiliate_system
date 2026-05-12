<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    affiliates: Object,
    filters:    Object,
    branches:   Array,
    plans:      Array,
});

const search        = ref(props.filters.search        || '');
const perPage       = ref(Number(props.filters.per_page || 10));
const sortBy        = ref(props.filters.sort_by        || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const branchId      = ref(props.filters.branch_id     || '');
const planId        = ref(props.filters.plan_id       || '');
const statusFilter  = ref(props.filters.status        || 'active');

const buildParams = (overrides = {}) => ({
    search:         search.value        || undefined,
    per_page:       perPage.value,
    sort_by:        sortBy.value,
    sort_direction: sortDirection.value,
    branch_id:      branchId.value      || undefined,
    plan_id:        planId.value        || undefined,
    status:         statusFilter.value  || undefined,
    ...overrides,
});

const refresh = (overrides = {}) => {
    router.get(route('affiliates.index'), buildParams(overrides), {
        preserveState: true, replace: true, preserveScroll: true,
    });
};

watch(search,       debounce(() => refresh({ page: 1 }), 300));
watch(perPage,      () => refresh({ page: 1 }));
watch(branchId,     () => refresh({ page: 1 }));
watch(planId,       () => refresh({ page: 1 }));
watch(statusFilter, () => refresh({ page: 1 }));

const toggleSort = (col) => {
    if (sortBy.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = col;
        sortDirection.value = 'asc';
    }
    refresh({ page: 1 });
};

const sortIcon = (col) => {
    if (sortBy.value !== col) return '↕';
    return sortDirection.value === 'asc' ? '↑' : '↓';
};

const hasFilters = computed(() =>
    !!search.value || !!branchId.value || !!planId.value ||
    perPage.value !== 10 || statusFilter.value !== 'active'
);

const clearFilters = () => {
    search.value = ''; perPage.value = 10;
    sortBy.value = 'created_at'; sortDirection.value = 'desc';
    branchId.value = ''; planId.value = '';
    statusFilter.value = 'active';
    refresh({ page: 1 });
};

// ── Modal detalle ──────────────────────────────────────────
const showModal      = ref(false);
const selectedAff    = ref(null);
const holderNote     = computed(() =>
    selectedAff.value?.beneficiaries?.find(b => b.holder_note)?.holder_note ?? null
);

const openDetail = (aff) => { selectedAff.value = JSON.parse(JSON.stringify(aff)); showModal.value = true; };
const closeModal = () => { showModal.value = false; selectedAff.value = null; };

// ── Import Excel ───────────────────────────────────────────
const importForm = useForm({ excel_file: null });
const showImport = ref(false);

const handleFile = (e) => { importForm.excel_file = e.target.files[0]; };

const doImport = () => {
    if (!importForm.excel_file) return;
    importForm.post(route('affiliates.import'), {
        forceFormData: true,
        onSuccess: () => { importForm.reset(); showImport.value = false; },
    });
};

// ── Toggle estado afiliado ─────────────────────────────────
const toggleForm = useForm({});
const toggleStatus = (id) => {
    toggleForm.patch(route('affiliates.toggleStatus', id));
};

const paginationLinks = computed(() => props.affiliates.links || []);
</script>

<template>
    <Head title="Afiliados"/>

    <AuthenticatedLayout>
        <template #header>
            <h2>Afiliados</h2>
            <p>Gestión y control de afiliaciones — {{ affiliates.total }} registros</p>
        </template>

        <div class="af">

            <!-- ── Barra de herramientas ── -->
            <div class="af-toolbar">
                <div class="af-toolbar__filters">
                    <!-- Búsqueda -->
                    <div class="af-search-wrap">
                        <svg class="af-search-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <input v-model="search" type="text"
                            placeholder="Buscar por nombre, cédula o contrato..."
                            class="af-input af-input--search"/>
                    </div>

                    <select v-model="branchId" class="af-input af-select">
                        <option value="">Todas las sedes</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>

                    <select v-model="planId" class="af-input af-select">
                        <option value="">Todos los planes</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>

                    <select v-model="statusFilter" class="af-input af-select">
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                        <option value="all">Todos</option>
                    </select>

                    <button v-if="hasFilters" @click="clearFilters" class="af-btn-clear">
                        ✕ Limpiar
                    </button>
                </div>

                <div class="af-toolbar__actions">
                    <button @click="showImport = !showImport" class="af-btn af-btn--import">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        Importar Excel
                    </button>
                    <Link :href="route('affiliates.create')" class="af-btn af-btn--new">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Nuevo Afiliado
                    </Link>
                </div>
            </div>

            <!-- ── Import panel ── -->
            <div v-if="showImport" class="af-import-panel">
                <p class="af-import-panel__label">Selecciona el archivo Excel (.xlsx)</p>
                <div class="af-import-panel__row">
                    <input type="file" accept=".xlsx,.xls" @change="handleFile" class="af-input"/>
                    <button @click="doImport" :disabled="!importForm.excel_file || importForm.processing"
                        class="af-btn af-btn--new">
                        {{ importForm.processing ? 'Importando...' : 'Cargar archivo' }}
                    </button>
                </div>
            </div>

            <!-- ── Tabla ── -->
            <div class="af-card">
                <div class="af-table-wrap">
                    <table class="af-table">
                        <thead>
                            <tr>
                                <th @click="toggleSort('full_name')" class="af-th--sort">
                                    Nombre {{ sortIcon('full_name') }}
                                </th>
                                <th @click="toggleSort('id_number')" class="af-th--sort">
                                    Cédula {{ sortIcon('id_number') }}
                                </th>
                                <th @click="toggleSort('contract_number')" class="af-th--sort">
                                    Contrato {{ sortIcon('contract_number') }}
                                </th>
                                <th @click="toggleSort('affiliation_date')" class="af-th--sort">
                                    Afiliación {{ sortIcon('affiliation_date') }}
                                </th>
                                <th>Sede</th>
                                <th>Plan</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="aff in affiliates.data" :key="aff.id"
                                :class="{ 'af-row--inactive': !aff.is_active }">
                                <td class="af-td--name">{{ aff.full_name }}</td>
                                <td>{{ aff.id_number }}</td>
                                <td>
                                    <span class="af-badge af-badge--contract">
                                        #{{ aff.contract_number || 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    {{ aff.affiliation_date
                                        ? new Date(aff.affiliation_date).toLocaleDateString('es-CO')
                                        : 'N/A' }}
                                </td>
                                <td>
                                    <span class="af-badge af-badge--branch">{{ aff.branch?.name }}</span>
                                </td>
                                <td>
                                    <span class="af-badge af-badge--plan">{{ aff.plan?.name }}</span>
                                </td>
                                <td>
                                    <span class="af-status" :class="aff.is_active ? 'af-status--active' : 'af-status--inactive'">
                                        <span class="af-status__dot"/>
                                        {{ aff.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="af-actions">
                                        <button @click="openDetail(aff)" class="af-action af-action--view">
                                            Ver
                                        </button>
                                        <Link :href="route('affiliates.edit', aff.id)"
                                            class="af-action af-action--edit">
                                            Editar
                                        </Link>
                                        <button @click="toggleStatus(aff.id)"
                                            class="af-action"
                                            :class="aff.is_active ? 'af-action--deactivate' : 'af-action--activate'">
                                            {{ aff.is_active ? 'Inactivar' : 'Activar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!affiliates.data.length">
                                <td colspan="8" class="af-empty">No se encontraron afiliados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="af-pagination">
                    <div class="af-pagination__left">
                        <p class="af-pagination__info">
                            Mostrando {{ affiliates.from || 0 }}–{{ affiliates.to || 0 }}
                            de {{ affiliates.total || 0 }} afiliados
                        </p>
                        <select v-model="perPage" class="af-input af-select af-select--sm">
                            <option :value="10">10 / pág</option>
                            <option :value="25">25 / pág</option>
                            <option :value="50">50 / pág</option>
                            <option :value="100">100 / pág</option>
                        </select>
                    </div>
                    <div class="af-pagination__links">
                        <Link v-for="link in paginationLinks"
                            :key="link.label + (link.url || 'x')"
                            :href="link.url || '#'"
                            class="af-page-btn"
                            :class="{
                                'af-page-btn--active':   link.active,
                                'af-page-btn--disabled': !link.url,
                            }"
                            preserve-state preserve-scroll>
                            <span v-html="link.label"/>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MODAL DETALLE ── -->
        <Teleport to="body">
            <div v-if="showModal"
                class="af-modal-overlay"
                @click.self="closeModal">
                <div class="af-modal">
                    <div class="af-modal__header">
                        <h3>Detalle del Afiliado</h3>
                        <button @click="closeModal" class="af-modal__close">✕</button>
                    </div>
                    <div class="af-modal__body" v-if="selectedAff">
                        <div class="af-modal__grid">
                            <div class="af-detail">
                                <p class="af-detail__label">Nombre</p>
                                <p class="af-detail__val">{{ selectedAff.full_name }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Cédula</p>
                                <p class="af-detail__val">{{ selectedAff.id_number }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Contrato</p>
                                <p class="af-detail__val">#{{ selectedAff.contract_number || 'N/A' }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Fecha afiliación</p>
                                <p class="af-detail__val">
                                    {{ selectedAff.affiliation_date
                                        ? new Date(selectedAff.affiliation_date).toLocaleDateString('es-CO')
                                        : 'N/A' }}
                                </p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Plan</p>
                                <span class="af-badge af-badge--plan">{{ selectedAff.plan?.name }}</span>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Sede</p>
                                <p class="af-detail__val">{{ selectedAff.branch?.name }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Teléfono</p>
                                <p class="af-detail__val">{{ selectedAff.phone || 'N/A' }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Correo</p>
                                <p class="af-detail__val">{{ selectedAff.email || 'N/A' }}</p>
                            </div>
                            <div class="af-detail af-detail--full">
                                <p class="af-detail__label">Dirección</p>
                                <p class="af-detail__val">{{ selectedAff.address || 'N/A' }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Asesor</p>
                                <p class="af-detail__val">{{ selectedAff.advisor_name || 'N/A' }}</p>
                            </div>
                            <div class="af-detail">
                                <p class="af-detail__label">Estado</p>
                                <span class="af-status" :class="selectedAff.is_active ? 'af-status--active' : 'af-status--inactive'">
                                    <span class="af-status__dot"/>
                                    {{ selectedAff.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>

                        <!-- Beneficiarios -->
                        <div class="af-modal__ben">
                            <h4 class="af-modal__ben-title">Grupo Familiar</h4>

                            <div v-if="holderNote" class="af-holder-note">
                                <p class="af-detail__label">Observación de importación</p>
                                <p>{{ holderNote }}</p>
                            </div>

                            <div v-if="selectedAff.beneficiaries?.length" class="af-ben-list">
                                <div v-for="ben in selectedAff.beneficiaries" :key="ben.id"
                                    class="af-ben-item">
                                    <div>
                                        <span class="af-ben-item__name">{{ ben.full_name }}</span>
                                        <span class="af-ben-item__rel"> · {{ ben.relationship }}</span>
                                    </div>
                                    <div class="af-ben-item__meta">
                                        Edad: {{ ben.age || 'N/A' }} &nbsp;|&nbsp; Aux: {{ ben.auxilio || 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <p v-else class="af-empty-ben">No tiene beneficiarios registrados.</p>
                        </div>
                    </div>
                    <div class="af-modal__footer">
                        <Link :href="route('affiliates.edit', selectedAff?.id)"
                            class="af-btn af-btn--new" @click="closeModal">
                            Editar afiliado
                        </Link>
                        <button @click="closeModal" class="af-btn af-btn--cancel">Cerrar</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; }

.af { display:flex; flex-direction:column; gap:18px; font-family:'DM Sans',sans-serif; }

/* ── Toolbar ── */
.af-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.af-toolbar__filters { display:flex; align-items:center; gap:8px; flex-wrap:wrap; flex:1; }
.af-toolbar__actions { display:flex; gap:8px; flex-shrink:0; }

.af-search-wrap { position:relative; flex:1; min-width:220px; }
.af-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#94a3b8; pointer-events:none; }

.af-input {
    padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:9px;
    font-size:0.85rem; font-family:'DM Sans',sans-serif; color:#1e293b;
    background:#fff; outline:none;
    transition:border-color 0.18s, box-shadow 0.18s;
    width:100%;
}
.af-input:focus { border-color:#1565a8; box-shadow:0 0 0 3px rgba(21,101,168,0.1); }
.af-input--search { padding-left:34px; }
.af-select { cursor:pointer; }
.af-select--sm { width:auto; }

.af-btn-clear { padding:8px 12px; background:none; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.82rem; font-family:'DM Sans',sans-serif; color:#64748b; cursor:pointer; transition:all 0.18s; }
.af-btn-clear:hover { background:#fef2f2; border-color:#fca5a5; color:#991b1b; }

.af-btn { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:9px; font-family:'DM Sans',sans-serif; font-size:0.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:all 0.18s; white-space:nowrap; }
.af-btn--new    { background:linear-gradient(135deg,#0a3d6b,#1565a8); color:#fff; box-shadow:0 3px 12px rgba(10,61,107,0.25); }
.af-btn--new:hover { opacity:0.9; transform:translateY(-1px); }
.af-btn--import { background:#f0f4f8; color:#475569; border:1.5px solid #e2e8f0; }
.af-btn--import:hover { background:#e2e8f0; }
.af-btn--cancel { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.af-btn svg { width:15px; height:15px; }

/* ── Import panel ── */
.af-import-panel { background:#fff; border:1.5px dashed #2eaa45; border-radius:12px; padding:18px 20px; }
.af-import-panel__label { font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:10px; }
.af-import-panel__row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }

/* ── Card tabla ── */
.af-card { background:#fff; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
.af-table-wrap { overflow-x:auto; }

.af-table { width:100%; border-collapse:collapse; font-size:0.85rem; }
.af-table thead tr { background:#f8fafc; border-bottom:1.5px solid #e2e8f0; }
.af-table th { padding:11px 14px; text-align:left; font-size:0.7rem; font-weight:700; color:#64748b; letter-spacing:0.08em; text-transform:uppercase; white-space:nowrap; }
.af-th--sort { cursor:pointer; user-select:none; }
.af-th--sort:hover { color:#0a3d6b; }
.af-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background 0.15s; }
.af-table tbody tr:hover { background:#f8fafc; }
.af-row--inactive { opacity:0.55; }
.af-table td { padding:11px 14px; vertical-align:middle; }
.af-td--name { font-weight:600; color:#1e293b; }

/* Badges */
.af-badge { display:inline-block; padding:3px 8px; border-radius:99px; font-size:0.72rem; font-weight:600; }
.af-badge--contract { background:#fefce8; color:#854d0e; }
.af-badge--branch   { background:#f1f5f9; color:#475569; }
.af-badge--plan     { background:#eff6ff; color:#1d4ed8; }

/* Status */
.af-status { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:99px; font-size:0.72rem; font-weight:600; }
.af-status__dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
.af-status--active   { background:#d1fae5; color:#065f46; }
.af-status--inactive { background:#f1f5f9; color:#475569; }

/* Actions */
.af-actions { display:flex; gap:5px; flex-wrap:wrap; }
.af-action { display:inline-flex; align-items:center; padding:4px 9px; border-radius:6px; font-size:0.73rem; font-weight:600; font-family:'DM Sans',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all 0.18s; }
.af-action--view       { background:#eff6ff; color:#1d4ed8; }
.af-action--view:hover { background:#dbeafe; }
.af-action--edit       { background:#f0fdf4; color:#166534; }
.af-action--edit:hover { background:#dcfce7; }
.af-action--deactivate       { background:#fff7ed; color:#c2410c; }
.af-action--deactivate:hover { background:#ffedd5; }
.af-action--activate         { background:#f0fdf4; color:#15803d; }
.af-action--activate:hover   { background:#dcfce7; }

/* Vacío */
.af-empty { text-align:center; padding:40px; color:#94a3b8; font-size:0.88rem; }

/* Paginación */
.af-pagination { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-top:1px solid #f0f4f8; flex-wrap:wrap; gap:10px; }
.af-pagination__left { display:flex; align-items:center; gap:12px; }
.af-pagination__info { font-size:0.78rem; color:#64748b; }
.af-pagination__links { display:flex; gap:4px; flex-wrap:wrap; }
.af-page-btn { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 7px; border-radius:7px; font-size:0.8rem; font-weight:500; text-decoration:none; color:#475569; background:#f8fafc; border:1px solid #e2e8f0; transition:all 0.18s; }
.af-page-btn--active   { background:#0a3d6b; color:#fff; border-color:#0a3d6b; }
.af-page-btn--disabled { opacity:0.4; pointer-events:none; }
.af-page-btn:not(.af-page-btn--active):not(.af-page-btn--disabled):hover { background:#e2e8f0; }

/* ── MODAL ── */
.af-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); z-index:500; display:flex; align-items:center; justify-content:center; padding:20px; }
.af-modal { background:#fff; border-radius:18px; width:100%; max-width:700px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(0,0,0,0.3); overflow:hidden; }
.af-modal__header { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; border-bottom:1px solid #f0f4f8; background:#f8fafc; }
.af-modal__header h3 { font-family:'Playfair Display',serif; font-size:1.15rem; color:#0a3d6b; font-weight:700; }
.af-modal__close { background:none; border:none; cursor:pointer; font-size:1.2rem; color:#94a3b8; padding:4px 8px; border-radius:6px; transition:all 0.18s; }
.af-modal__close:hover { background:#fef2f2; color:#ef4444; }
.af-modal__body { flex:1; overflow-y:auto; padding:22px 24px; }
.af-modal__footer { display:flex; justify-content:flex-end; gap:10px; padding:14px 24px; border-top:1px solid #f0f4f8; }

.af-modal__grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
.af-detail { display:flex; flex-direction:column; gap:3px; }
.af-detail--full { grid-column:1/-1; }
.af-detail__label { font-size:0.68rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.06em; }
.af-detail__val   { font-size:0.9rem; color:#1e293b; font-weight:500; }

.af-modal__ben { border-top:1.5px solid #f0f4f8; padding-top:16px; }
.af-modal__ben-title { font-family:'Playfair Display',serif; font-size:1rem; color:#0a3d6b; font-weight:700; margin-bottom:12px; }

.af-holder-note { background:#fefce8; border:1px solid #fde68a; border-radius:8px; padding:12px 14px; margin-bottom:12px; font-size:0.84rem; color:#78350f; }

.af-ben-list { display:flex; flex-direction:column; gap:8px; }
.af-ben-item { display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:9px; padding:10px 14px; font-size:0.84rem; flex-wrap:wrap; gap:4px; }
.af-ben-item__name { font-weight:600; color:#1e293b; }
.af-ben-item__rel  { color:#64748b; }
.af-ben-item__meta { font-size:0.78rem; color:#94a3b8; }
.af-empty-ben { font-size:0.84rem; color:#94a3b8; font-style:italic; }

@media (max-width:640px) {
    .af-modal__grid { grid-template-columns:1fr; }
    .af-toolbar__filters { flex-direction:column; align-items:stretch; }
    .af-search-wrap { min-width:unset; }
}
</style>