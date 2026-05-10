<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce'; // Para no saturar el servidor al escribir
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    affiliates: Object,
    filters: Object,
    branches: Array,
    plans: Array,
});

// 1. Lógica de Búsqueda
const search = ref(props.filters.search || '');
const perPage = ref(Number(props.filters.per_page || 10));
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const branchId = ref(props.filters.branch_id || '');
const planId = ref(props.filters.plan_id || '');

const buildTableParams = (overrides = {}) => ({
    search: search.value || undefined,
    per_page: perPage.value,
    sort_by: sortBy.value,
    sort_direction: sortDirection.value,
    branch_id: branchId.value || undefined,
    plan_id: planId.value || undefined,
    ...overrides,
});

const refreshTable = (overrides = {}) => {
    router.get(route('affiliates.index'), buildTableParams(overrides), {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

watch(search, debounce((value) => {
    refreshTable({ search: value || undefined, page: 1 });
}, 300));

watch(perPage, (value) => {
    refreshTable({ per_page: value, page: 1 });
});

watch(branchId, (value) => {
    refreshTable({ branch_id: value || undefined, page: 1 });
});

watch(planId, (value) => {
    refreshTable({ plan_id: value || undefined, page: 1 });
});

// 2. Lógica de Importación
const form = useForm({
    excel_file: null,
});

const importExcel = () => {
    if (!form.excel_file) return;

    form.post(route('affiliates.import'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            alert('¡Importación de Bogotá completada con éxito!');
        },
        onError: (errors) => {
            console.error(errors);
            alert('Hubo un error en la importación. Revisa la consola.');
        }
    });
};

const showModal = ref(false);
const selectedAffiliate = ref(null);

const selectedHolderNote = computed(() => {
    return selectedAffiliate.value?.beneficiaries?.find((ben) => ben.holder_note)?.holder_note || null;
});

const paginationLinks = computed(() =>
    props.affiliates.links || []
);

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc';
    }

    refreshTable({ sort_by: sortBy.value, sort_direction: sortDirection.value, page: 1 });
};

const sortIndicator = (column) => {
    if (sortBy.value !== column) return '↕';

    return sortDirection.value === 'asc' ? '↑' : '↓';
};

const hasActiveFilters = computed(() =>
    !!search.value || !!branchId.value || !!planId.value || perPage.value !== 10 || sortBy.value !== 'created_at' || sortDirection.value !== 'desc'
);

const clearFilters = () => {
    search.value = '';
    perPage.value = 10;
    sortBy.value = 'created_at';
    sortDirection.value = 'desc';
    branchId.value = '';
    planId.value = '';

    refreshTable({
        search: undefined,
        branch_id: undefined,
        plan_id: undefined,
        per_page: 10,
        sort_by: 'created_at',
        sort_direction: 'desc',
        page: 1,
    });
};

const openDetail = (affiliate) => {
    selectedAffiliate.value = JSON.parse(JSON.stringify(affiliate));
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedAffiliate.value = null;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-10 px-6">
        <div class="max-w-7xl mx-auto">

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Sistema de Afiliados</h1>
                    <p class="text-gray-500 text-sm mt-1">Gestión y control de afiliaciones</p>
                </div>

                <a :href="route('affiliates.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow-sm font-medium transition">
                    + Nuevo Afiliado
                </a>
            </div>

            <!-- CARD PRINCIPAL -->
            <div class="bg-white rounded-xl shadow-md p-6">

                <!-- BARRA SUPERIOR -->
                <div class="flex flex-col gap-4 mb-6">

                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-5">
                        <input v-model="search" type="text" placeholder="Buscar por nombre, cédula o contrato..."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 lg:col-span-2" />

                        <select v-model="branchId"
                            class="border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas las sedes</option>
                            <option v-for="branch in branches" :key="branch.id" :value="String(branch.id)">
                                {{ branch.name }}
                            </option>
                        </select>

                        <select v-model="planId"
                            class="border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los planes</option>
                            <option v-for="plan in plans" :key="plan.id" :value="String(plan.id)">
                                {{ plan.name }}
                            </option>
                        </select>

                        <select v-model="perPage"
                            class="border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                            <option :value="10">10 por página</option>
                            <option :value="25">25 por página</option>
                            <option :value="50">50 por página</option>
                            <option :value="100">100 por página</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                v-if="hasActiveFilters"
                                @click="clearFilters"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition"
                            >
                                Limpiar filtros
                            </button>
                        </div>

                        <input type="file" id="excel-upload" class="hidden" accept=".xlsx,.xls"
                            @change="e => form.excel_file = e.target.files[0]" />

                        <label for="excel-upload"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg cursor-pointer transition">
                            {{ form.excel_file ? 'Archivo: ' + form.excel_file.name : 'Seleccionar Excel' }}
                        </label>

                        <button v-if="form.excel_file" @click="importExcel" :disabled="form.processing"
                            class="bg-green-800 hover:bg-green-900 text-white px-5 py-2 rounded-lg font-semibold transition disabled:opacity-50">
                            {{ form.processing ? 'Importando...' : 'Importar' }}
                        </button>
                    </div>
                </div>

                <!-- TABLA -->
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <button @click="toggleSort('full_name')" class="inline-flex items-center gap-1 font-semibold">
                                        Nombre <span>{{ sortIndicator('full_name') }}</span>
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button @click="toggleSort('id_number')" class="inline-flex items-center gap-1 font-semibold">
                                        Cédula <span>{{ sortIndicator('id_number') }}</span>
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button @click="toggleSort('contract_number')" class="inline-flex items-center gap-1 font-semibold">
                                        Contrato <span>{{ sortIndicator('contract_number') }}</span>
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button @click="toggleSort('affiliation_date')" class="inline-flex items-center gap-1 font-semibold">
                                        Fecha afiliación <span>{{ sortIndicator('affiliation_date') }}</span>
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left">Sede</th>
                                <th class="px-4 py-3 text-left">Plan</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="affiliate in affiliates.data" :key="affiliate.id"
                                class="border-t hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ affiliate.full_name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ affiliate.id_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded text-xs font-semibold">
                                        #{{ affiliate.contract_number || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ affiliate.affiliation_date ? new Date(affiliate.affiliation_date).toLocaleDateString('es-CO') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                        {{ affiliate.branch?.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                        {{ affiliate.plan?.name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="openDetail(affiliate)"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="affiliates.data.length === 0">
                                <td colspan="7" class="text-center py-8 text-gray-400">
                                    No hay afiliados registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACION -->
                <div class="mt-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-gray-500">
                        Mostrando {{ affiliates.from || 0 }} a {{ affiliates.to || 0 }} de {{ affiliates.total || 0 }} afiliados
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            v-for="link in paginationLinks"
                            :key="link.label + (link.url || 'disabled')"
                            :href="link.url || '#'"
                            preserve-state
                            preserve-scroll
                            class="rounded-lg border px-3 py-2 text-sm transition"
                            :class="[
                                link.active ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
                                !link.url ? 'pointer-events-none opacity-50' : '',
                            ]"
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>


        <!-- MODAL -->
        <div v-if="showModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">

            <div class="bg-white w-full max-w-3xl rounded-xl shadow-xl overflow-hidden">

                <!-- HEADER -->
                <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-xl font-bold text-gray-800">Detalle del Afiliado</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-red-500 text-2xl">&times;</button>
                </div>

                <!-- BODY -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Nombre</p>
                        <p class="font-semibold text-gray-800">{{ selectedAffiliate.full_name }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Cédula</p>
                        <p>{{ selectedAffiliate.id_number }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Contrato</p>
                        <p>#{{ selectedAffiliate.contract_number }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Fecha de afiliación</p>
                        <p>{{ selectedAffiliate.affiliation_date ? new Date(selectedAffiliate.affiliation_date).toLocaleDateString('es-CO') : 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Plan</p>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                            {{ selectedAffiliate.plan?.name }}
                        </span>
                    </div>

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Teléfono</p>
                        <p>{{ selectedAffiliate.phone || 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-gray-400 uppercase text-xs">Correo</p>
                        <p>{{ selectedAffiliate.email || 'N/A' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-gray-400 uppercase text-xs">Sede</p>
                        <p>{{ selectedAffiliate.branch?.name }}</p>
                    </div>

                    <!-- BENEFICIARIOS -->
                    <div class="md:col-span-2 mt-4">
                        <h4 class="font-bold text-indigo-700 mb-3 border-b pb-2">
                            Grupo Familiar
                        </h4>

                        <div v-if="selectedHolderNote" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <p class="text-gray-500 uppercase text-xs mb-1">Observación de la importación</p>
                            <p class="text-sm text-gray-800">{{ selectedHolderNote }}</p>
                        </div>

                        <div v-if="selectedAffiliate?.beneficiaries?.length" class="space-y-2">
                            <div v-for="ben in selectedAffiliate.beneficiaries" :key="ben.id"
                                class="flex flex-col gap-1 bg-gray-50 p-3 rounded-lg border text-sm md:flex-row md:items-center md:justify-between">
                                <span>
                                    <strong>{{ ben.full_name }}</strong> ({{ ben.relationship }})
                                </span>
                                <span class="text-gray-500">
                                    Edad: {{ ben.age }} | Aux: {{ ben.auxilio || 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <p v-else class="text-gray-400 italic">
                            No tiene beneficiarios registrados.
                        </p>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
                    <button @click="closeModal"
                        class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-2 rounded-lg transition">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>