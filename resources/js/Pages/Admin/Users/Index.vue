<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users:   Object,
    roles:   Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const role   = ref(props.filters.role   || '');
const status = ref(props.filters.status || '');

const refresh = (overrides = {}) => {
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        role:   role.value   || undefined,
        status: status.value || undefined,
        ...overrides,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

watch(search, debounce(() => refresh({ page: 1 }), 300));
watch(role,   () => refresh({ page: 1 }));
watch(status, () => refresh({ page: 1 }));

// Toggle estado usuario
const toggleForm = useForm({});
const toggleUser = (userId) => {
    toggleForm.patch(route('admin.users.toggle', userId), {
        onSuccess: () => {},
    });
};

const roleColor = (roleName) => ({
    'super_admin': 'badge--navy',
    'empleado':    'badge--blue',
    'cliente':     'badge--green',
}[roleName] || 'badge--gray');

const roleLabel = (roleName) => ({
    'super_admin': 'Super Admin',
    'empleado':    'Empleado',
    'cliente':     'Cliente',
}[roleName] || roleName);
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Gestión de Usuarios</h2>
            <p>Administra las cuentas y roles del sistema</p>
        </template>

        <div class="adm">

            <!-- Flash -->
            <div v-if="$page.props.flash?.message" class="adm-flash adm-flash--ok">
                {{ $page.props.flash.message }}
            </div>
            <div v-if="$page.props.flash?.error" class="adm-flash adm-flash--err">
                {{ $page.props.flash.error }}
            </div>

            <div class="adm-card">

                <!-- Barra de herramientas -->
                <div class="adm-toolbar">
                    <div class="adm-toolbar__filters">
                        <input v-model="search" type="text"
                            placeholder="Buscar por nombre o correo..."
                            class="adm-input adm-input--search"/>

                        <select v-model="role" class="adm-input adm-select">
                            <option value="">Todos los roles</option>
                            <option v-for="r in roles" :key="r.id" :value="r.name">
                                {{ roleLabel(r.name) }}
                            </option>
                        </select>

                        <select v-model="status" class="adm-input adm-select">
                            <option value="">Todos los estados</option>
                            <option value="active">Activos</option>
                            <option value="inactive">Inactivos</option>
                        </select>
                    </div>

                    <Link :href="route('admin.users.create')" class="adm-btn adm-btn--primary">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Nuevo Usuario
                    </Link>
                </div>

                <!-- Tabla -->
                <div class="adm-table-wrap">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Afiliado Vinculado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <div class="adm-user-cell">
                                        <div class="adm-avatar">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="adm-user-name">{{ user.name }}</p>
                                            <p class="adm-user-email">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        v-for="r in user.roles"
                                        :key="r.id"
                                        class="badge"
                                        :class="roleColor(r.name)"
                                    >
                                        {{ roleLabel(r.name) }}
                                    </span>
                                    <span v-if="!user.roles.length" class="adm-nodata">Sin rol</span>
                                </td>
                                <td>
                                    <span v-if="user.affiliate" class="adm-affiliate-link">
                                        {{ user.affiliate?.full_name }}
                                        <small>{{ user.affiliate?.id_number }}</small>
                                    </span>
                                    <span v-else class="adm-nodata">—</span>
                                </td>
                                <td>
                                    <span class="status-pill" :class="user.is_active ? 'status-pill--active' : 'status-pill--inactive'">
                                        <span class="status-pill__dot"></span>
                                        {{ user.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="adm-actions">
                                        <Link :href="route('admin.users.edit', user.id)" class="adm-action-btn adm-action-btn--edit">
                                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                            Editar
                                        </Link>
                                        <button
                                            @click="toggleUser(user.id)"
                                            class="adm-action-btn"
                                            :class="user.is_active ? 'adm-action-btn--deactivate' : 'adm-action-btn--activate'"
                                        >
                                            <svg v-if="user.is_active" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524L13.477 14.89zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg>
                                            <svg v-else viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            {{ user.is_active ? 'Inactivar' : 'Activar' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data.length">
                                <td colspan="5" class="adm-empty">No se encontraron usuarios.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="adm-pagination">
                    <p class="adm-pagination__info">
                        Mostrando {{ users.from || 0 }}–{{ users.to || 0 }} de {{ users.total }} usuarios
                    </p>
                    <div class="adm-pagination__links">
                        <Link
                            v-for="link in users.links"
                            :key="link.label + link.url"
                            :href="link.url || '#'"
                            class="adm-page-btn"
                            :class="{
                                'adm-page-btn--active': link.active,
                                'adm-page-btn--disabled': !link.url,
                            }"
                            preserve-scroll preserve-state
                        >
                            <span v-html="link.label"/>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Leyenda de roles -->
            <div class="adm-legend">
                <p class="adm-legend__title">Descripción de roles</p>
                <div class="adm-legend__grid">
                    <div class="adm-legend__item">
                        <span class="badge badge--navy">Super Admin</span>
                        <p>Acceso total al sistema. Gestiona usuarios, roles y toda la información.</p>
                    </div>
                    <div class="adm-legend__item">
                        <span class="badge badge--blue">Empleado</span>
                        <p>Puede registrar, editar y gestionar afiliados e importar desde Excel.</p>
                    </div>
                    <div class="adm-legend__item">
                        <span class="badge badge--green">Cliente</span>
                        <p>Accede al portal personal para consultar su plan y estado de cuenta.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; }

.adm { display: flex; flex-direction: column; gap: 20px; font-family: 'DM Sans', sans-serif; }

/* Flash */
.adm-flash { padding: 12px 16px; border-radius: 10px; font-size: 0.88rem; font-weight: 500; }
.adm-flash--ok  { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
.adm-flash--err { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

/* Card */
.adm-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

/* Toolbar */
.adm-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 18px 22px;
    border-bottom: 1px solid #f0f4f8;
    flex-wrap: wrap;
}

.adm-toolbar__filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    flex: 1;
}

.adm-input {
    padding: 9px 13px;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    font-size: 0.86rem;
    font-family: 'DM Sans', sans-serif;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.adm-input:focus { border-color: #1565a8; box-shadow: 0 0 0 3px rgba(21,101,168,0.1); background: #fff; }
.adm-input--search { flex: 1; min-width: 200px; }
.adm-select { cursor: pointer; }

.adm-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.86rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    white-space: nowrap;
    transition: all 0.18s;
}

.adm-btn--primary {
    background: linear-gradient(135deg, #0a3d6b, #1565a8);
    color: #fff;
    box-shadow: 0 3px 12px rgba(10,61,107,0.25);
}

.adm-btn--primary svg { width: 15px; height: 15px; }
.adm-btn--primary:hover { opacity: 0.9; transform: translateY(-1px); }

/* Tabla */
.adm-table-wrap { overflow-x: auto; }

.adm-table { width: 100%; border-collapse: collapse; font-size: 0.86rem; }

.adm-table thead tr { background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; }

.adm-table th {
    padding: 11px 16px;
    text-align: left;
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.adm-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
.adm-table tbody tr:hover { background: #f8fafc; }
.adm-table td { padding: 12px 16px; vertical-align: middle; }

/* User cell */
.adm-user-cell { display: flex; align-items: center; gap: 10px; }

.adm-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0a3d6b, #2eaa45);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.88rem; color: #fff;
    flex-shrink: 0;
}

.adm-user-name { font-weight: 600; color: #1e293b; margin-bottom: 1px; }
.adm-user-email { font-size: 0.75rem; color: #64748b; }

/* Badges */
.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 99px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.badge--navy  { background: #dbeafe; color: #1e3a8a; }
.badge--blue  { background: #e0f2fe; color: #075985; }
.badge--green { background: #dcfce7; color: #166534; }
.badge--gray  { background: #f1f5f9; color: #475569; }

/* Status pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 99px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-pill--active   { background: #d1fae5; color: #065f46; }
.status-pill--inactive { background: #f1f5f9; color: #475569; }

.status-pill__dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* Affiliate cell */
.adm-affiliate-link { display: flex; flex-direction: column; }
.adm-affiliate-link small { font-size: 0.72rem; color: #94a3b8; }
.adm-nodata { color: #cbd5e1; font-size: 0.82rem; }

/* Actions */
.adm-actions { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.adm-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 7px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.18s;
}

.adm-action-btn svg { width: 13px; height: 13px; }

.adm-action-btn--edit       { background: #eff6ff; color: #1d4ed8; }
.adm-action-btn--edit:hover { background: #dbeafe; }

.adm-action-btn--deactivate       { background: #fff7ed; color: #c2410c; }
.adm-action-btn--deactivate:hover { background: #ffedd5; }

.adm-action-btn--activate       { background: #f0fdf4; color: #15803d; }
.adm-action-btn--activate:hover { background: #dcfce7; }

/* Empty */
.adm-empty { text-align: center; padding: 40px; color: #94a3b8; font-size: 0.88rem; }

/* Paginación */
.adm-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 22px;
    border-top: 1px solid #f0f4f8;
    flex-wrap: wrap;
    gap: 10px;
}

.adm-pagination__info { font-size: 0.78rem; color: #64748b; }
.adm-pagination__links { display: flex; gap: 4px; flex-wrap: wrap; }

.adm-page-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 34px; height: 34px;
    padding: 0 8px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.18s;
}

.adm-page-btn--active { background: #0a3d6b; color: #fff; border-color: #0a3d6b; }
.adm-page-btn--disabled { opacity: 0.4; pointer-events: none; }
.adm-page-btn:not(.adm-page-btn--active):not(.adm-page-btn--disabled):hover { background: #e2e8f0; }

/* Leyenda */
.adm-legend {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    padding: 18px 22px;
}

.adm-legend__title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.adm-legend__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
}

.adm-legend__item { display: flex; flex-direction: column; gap: 5px; }

.adm-legend__item p { font-size: 0.8rem; color: #64748b; line-height: 1.5; }
</style>