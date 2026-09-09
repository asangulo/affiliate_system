<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    roles:          Array,
    permissions:    Array,
    protectedRoles: Array,
});

const roleColor = (name) => ({
    super_admin: 'badge--navy',
    empleado:    'badge--blue',
    cliente:     'badge--green',
}[name] || 'badge--gray');

const roleLabel = (name) => ({
    super_admin: 'Super Admin',
    empleado:    'Empleado',
    cliente:     'Cliente',
}[name] || name);

const isProtected = (name) => props.protectedRoles.includes(name);

// ── Crear rol ────────────────────────────────────────────────
const showRoleModal = ref(false);
const roleForm = useForm({ name: '', permissions: [] });

const openRoleModal = () => { roleForm.reset(); showRoleModal.value = true; };
const closeRoleModal = () => { showRoleModal.value = false; };

const submitRole = () => {
    roleForm.post(route('admin.roles.store'), {
        onSuccess: () => closeRoleModal(),
    });
};

// ── Crear permiso ────────────────────────────────────────────
const showPermModal = ref(false);
const permForm = useForm({ name: '' });

const openPermModal = () => { permForm.reset(); showPermModal.value = true; };
const closePermModal = () => { showPermModal.value = false; };

const submitPermission = () => {
    permForm.post(route('admin.permissions.store'), {
        onSuccess: () => closePermModal(),
    });
};

// ── Editar permisos de un rol existente ──────────────────────
const editingRoleId = ref(null);
const editForm = useForm({ permissions: [] });

const startEdit = (role) => {
    editingRoleId.value = role.id;
    editForm.permissions = role.permissions.map((p) => p.name);
};

const cancelEdit = () => { editingRoleId.value = null; };

const saveEdit = (role) => {
    editForm.patch(route('admin.roles.update', role.id), {
        preserveScroll: true,
        onSuccess: () => { editingRoleId.value = null; },
    });
};

const destroyRole = (role) => {
    if (!confirm(`¿Eliminar el rol "${roleLabel(role.name)}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route('admin.roles.destroy', role.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Roles y Permisos"/>
    <AuthenticatedLayout>
        <template #header>
            <h2>Roles y Permisos</h2>
            <p>Gestiona los roles del sistema y los permisos que puede tener cada uno</p>
        </template>

        <div class="rl">
            <div class="rl-actions">
                <button class="rl-btn rl-btn--outline" @click="openPermModal">+ Nuevo permiso</button>
                <button class="rl-btn" @click="openRoleModal">+ Nuevo rol</button>
            </div>

            <div class="rl-grid">
                <div v-for="role in roles" :key="role.id" class="rl-card">
                    <div class="rl-card__header">
                        <span class="badge" :class="roleColor(role.name)">
                            {{ roleLabel(role.name) }}
                        </span>
                        <span class="rl-card__count">{{ role.users_count }} usuario(s)</span>
                    </div>

                    <div class="rl-card__perms">
                        <div class="rl-card__perms-row">
                            <p class="rl-card__perms-title">Permisos asignados</p>
                            <button v-if="editingRoleId !== role.id" class="rl-link" @click="startEdit(role)">Editar</button>
                        </div>

                        <!-- Vista normal -->
                        <div v-if="editingRoleId !== role.id" class="rl-perms-list">
                            <span v-for="perm in role.permissions" :key="perm.id" class="rl-perm">
                                {{ perm.name }}
                            </span>
                            <span v-if="!role.permissions.length" class="rl-perm rl-perm--empty">
                                Sin permisos asignados
                            </span>
                        </div>

                        <!-- Edición -->
                        <div v-else class="rl-edit">
                            <label v-for="perm in permissions" :key="perm.id" class="rl-check">
                                <input type="checkbox" :value="perm.name" v-model="editForm.permissions" />
                                {{ perm.name }}
                            </label>
                            <div class="rl-edit__actions">
                                <button class="rl-btn rl-btn--sm" :disabled="editForm.processing" @click="saveEdit(role)">Guardar</button>
                                <button class="rl-btn rl-btn--sm rl-btn--outline" @click="cancelEdit">Cancelar</button>
                            </div>
                        </div>
                    </div>

                    <div class="rl-card__footer">
                        <button
                            v-if="!isProtected(role.name) && role.users_count === 0"
                            class="rl-link rl-link--danger"
                            @click="destroyRole(role)"
                        >Eliminar rol</button>
                        <span v-else-if="!isProtected(role.name)" class="rl-hint">No se puede eliminar: tiene usuarios asignados.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: nuevo rol -->
        <Teleport to="body">
            <div v-if="showRoleModal" class="rl-modal-overlay" @click.self="closeRoleModal">
                <div class="rl-modal">
                    <div class="rl-modal__header">
                        <h3>Nuevo rol</h3>
                        <button @click="closeRoleModal" class="rl-modal__close">✕</button>
                    </div>
                    <form @submit.prevent="submitRole" class="rl-modal__body">
                        <label class="rl-field">
                            <span>Nombre del rol</span>
                            <input v-model="roleForm.name" type="text" placeholder="Ej: contador" />
                            <span v-if="roleForm.errors.name" class="rl-error">{{ roleForm.errors.name }}</span>
                        </label>

                        <p class="rl-field__label">Permisos</p>
                        <div class="rl-edit rl-edit--modal">
                            <label v-for="perm in permissions" :key="perm.id" class="rl-check">
                                <input type="checkbox" :value="perm.name" v-model="roleForm.permissions" />
                                {{ perm.name }}
                            </label>
                        </div>

                        <div class="rl-modal__actions">
                            <button type="button" class="rl-btn rl-btn--outline" @click="closeRoleModal">Cancelar</button>
                            <button type="submit" class="rl-btn" :disabled="roleForm.processing">Crear rol</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Modal: nuevo permiso -->
        <Teleport to="body">
            <div v-if="showPermModal" class="rl-modal-overlay" @click.self="closePermModal">
                <div class="rl-modal rl-modal--sm">
                    <div class="rl-modal__header">
                        <h3>Nuevo permiso</h3>
                        <button @click="closePermModal" class="rl-modal__close">✕</button>
                    </div>
                    <form @submit.prevent="submitPermission" class="rl-modal__body">
                        <label class="rl-field">
                            <span>Nombre del permiso</span>
                            <input v-model="permForm.name" type="text" placeholder="Ej: Ver auditoría contable" />
                            <span v-if="permForm.errors.name" class="rl-error">{{ permForm.errors.name }}</span>
                        </label>
                        <div class="rl-modal__actions">
                            <button type="button" class="rl-btn rl-btn--outline" @click="closePermModal">Cancelar</button>
                            <button type="submit" class="rl-btn" :disabled="permForm.processing">Crear permiso</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
*, *::before, *::after { box-sizing:border-box; }

.rl { display:flex; flex-direction:column; gap:20px; font-family:'DM Sans',sans-serif; }

.rl-actions { display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }

.rl-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; }

.rl-card { background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; display:flex; flex-direction:column; }

.rl-card__header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; background:#f8fafc; border-bottom:1px solid #f0f4f8; }

.badge { display:inline-block; padding:4px 12px; border-radius:99px; font-size:0.78rem; font-weight:700; }
.badge--navy  { background:#dbeafe; color:#1e3a8a; }
.badge--blue  { background:#e0f2fe; color:#075985; }
.badge--green { background:#dcfce7; color:#166534; }
.badge--gray  { background:#f1f5f9; color:#475569; }

.rl-card__count { font-size:0.78rem; color:#94a3b8; font-weight:500; }

.rl-card__perms { padding:16px 20px; flex:1; }
.rl-card__perms-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.rl-card__perms-title { font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em; }

.rl-perms-list { display:flex; flex-wrap:wrap; gap:6px; }

.rl-perm { display:inline-block; padding:3px 9px; background:#f0f4f8; color:#475569; border-radius:6px; font-size:0.73rem; font-weight:500; }
.rl-perm--empty { color:#cbd5e1; font-style:italic; }

.rl-card__footer { padding:12px 20px; border-top:1px solid #f0f4f8; }
.rl-hint { font-size:0.75rem; color:#cbd5e1; }

.rl-link { background:none; border:none; padding:0; font-size:0.78rem; font-weight:700; color:#1565a8; cursor:pointer; }
.rl-link:hover { text-decoration:underline; }
.rl-link--danger { color:#dc2626; }

.rl-edit { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:6px 12px; max-height:220px; overflow-y:auto; padding-right:4px; }
.rl-edit--modal { max-height:260px; margin-bottom:16px; border:1px solid #e2e8f0; border-radius:10px; padding:12px; }

.rl-check { display:flex; align-items:center; gap:7px; font-size:0.8rem; color:#334155; }
.rl-check input { accent-color:#1565a8; }

.rl-edit__actions { grid-column:1/-1; display:flex; gap:8px; margin-top:10px; }

.rl-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:9px; border:none;
    background:linear-gradient(135deg,#0a3d6b,#1565a8); color:#fff;
    font-family:'DM Sans',sans-serif; font-size:0.85rem; font-weight:600;
    cursor:pointer; transition:opacity 0.18s;
}
.rl-btn:hover { opacity:0.9; }
.rl-btn:disabled { opacity:0.6; cursor:not-allowed; }
.rl-btn--sm { padding:6px 14px; font-size:0.78rem; }
.rl-btn--outline { background:#fff; color:#334155; border:1.5px solid #e2e8f0; }

/* ── Modal ── */
.rl-modal-overlay {
    position:fixed; inset:0; background:rgba(15,23,42,0.5);
    display:flex; align-items:center; justify-content:center;
    padding:20px; z-index:1000;
}

.rl-modal { background:#fff; border-radius:16px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
.rl-modal--sm { max-width:400px; }

.rl-modal__header { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid #f0f4f8; }
.rl-modal__header h3 { font-family:'Playfair Display',serif; font-size:1.15rem; color:#0a3d6b; font-weight:700; }
.rl-modal__close { background:none; border:none; font-size:1rem; color:#94a3b8; cursor:pointer; }

.rl-modal__body { padding:20px 22px; }

.rl-field { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.rl-field span:first-child { font-size:0.8rem; font-weight:600; color:#334155; }
.rl-field input {
    padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:9px;
    font-size:0.88rem; font-family:'DM Sans',sans-serif; color:#1e293b;
}
.rl-field input:focus { outline:none; border-color:#1565a8; }
.rl-field__label { font-size:0.78rem; font-weight:700; color:#334155; margin-bottom:8px; }

.rl-error { font-size:0.75rem; color:#dc2626; }

.rl-modal__actions { display:flex; justify-content:flex-end; gap:10px; }

@media (max-width: 480px) {
    .rl-actions { justify-content:stretch; }
    .rl-actions .rl-btn { flex:1; justify-content:center; }
}
</style>
