<!-- ═══════════════════════════════════════════════════════════
     Admin/Users/Create.vue
     Coloca en: resources/js/Pages/Admin/Users/Create.vue
     ═══════════════════════════════════════════════════════════ -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
 
const props = defineProps({
    roles:      Array,
    affiliates: Array,
});
 
const form = useForm({
    name:         '',
    email:        '',
    password:     '',
    role:         '',
    affiliate_id: '',
    is_active:    true,
});
 
const submit = () => form.post(route('admin.users.store'));
</script>
 
<template>
    <Head title="Nuevo Usuario"/>
    <AuthenticatedLayout>
        <template #header>
            <h2>Nuevo Usuario</h2>
            <p>Crear cuenta de acceso al sistema</p>
        </template>
 
        <div class="uf-wrap">
            <form @submit.prevent="submit" class="uf-form">
 
                <div class="uf-section">
                    <div class="uf-section__head">
                        <div class="uf-num">1</div>
                        <div>
                            <h3 class="uf-title">Datos de Acceso</h3>
                            <p class="uf-sub">Credenciales para iniciar sesión</p>
                        </div>
                    </div>
                    <div class="uf-grid">
                        <div class="uf-field uf-field--full">
                            <label class="uf-label">Nombre completo <span class="uf-req">*</span></label>
                            <input v-model="form.name" type="text" class="uf-input" placeholder="Nombre del usuario"/>
                            <InputError :message="form.errors.name"/>
                        </div>
                        <div class="uf-field">
                            <label class="uf-label">Correo electrónico <span class="uf-req">*</span></label>
                            <input v-model="form.email" type="email" class="uf-input" placeholder="correo@horizonte.com"/>
                            <InputError :message="form.errors.email"/>
                        </div>
                        <div class="uf-field">
                            <label class="uf-label">Contraseña <span class="uf-req">*</span></label>
                            <input v-model="form.password" type="password" class="uf-input" placeholder="Mínimo 8 caracteres"/>
                            <InputError :message="form.errors.password"/>
                        </div>
                    </div>
                </div>
 
                <div class="uf-section">
                    <div class="uf-section__head">
                        <div class="uf-num">2</div>
                        <div>
                            <h3 class="uf-title">Rol y Vinculación</h3>
                            <p class="uf-sub">Permisos y afiliado vinculado (para clientes)</p>
                        </div>
                    </div>
                    <div class="uf-grid">
                        <div class="uf-field">
                            <label class="uf-label">Rol <span class="uf-req">*</span></label>
                            <select v-model="form.role" class="uf-input uf-select">
                                <option value="">Seleccionar rol...</option>
                                <option v-for="r in roles" :key="r.id" :value="r.name">
                                    {{ r.name === 'super_admin' ? 'Super Admin'
                                     : r.name === 'empleado'   ? 'Empleado'
                                     : 'Cliente' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role"/>
                        </div>
                        <div class="uf-field">
                            <label class="uf-label">
                                Afiliado vinculado
                                <span class="uf-hint">(solo para clientes)</span>
                            </label>
                            <select v-model="form.affiliate_id" class="uf-input uf-select"
                                :disabled="form.role !== 'cliente'">
                                <option value="">Sin vincular</option>
                                <option v-for="a in affiliates" :key="a.id" :value="a.id">
                                    {{ a.full_name }} — {{ a.id_number }}
                                </option>
                            </select>
                        </div>
                        <div class="uf-field uf-field--full">
                            <label class="uf-check">
                                <input type="checkbox" v-model="form.is_active"/>
                                <span>Cuenta activa al crear</span>
                            </label>
                        </div>
                    </div>
                </div>
 
                <!-- Descripción de roles -->
                <div class="uf-roles-info">
                    <div v-if="roles.some(r => r.name === 'super_admin')" class="uf-role-card">
                        <span class="uf-role-badge uf-role-badge--navy">Super Admin</span>
                        <p>Acceso total. Gestiona usuarios, roles y toda la información del sistema.</p>
                    </div>
                    <div class="uf-role-card">
                        <span class="uf-role-badge uf-role-badge--blue">Empleado</span>
                        <p>Puede registrar, editar y gestionar afiliados e importar desde Excel.</p>
                    </div>
                    <div class="uf-role-card">
                        <span class="uf-role-badge uf-role-badge--green">Cliente</span>
                        <p>Solo accede al portal personal para consultar su plan y estado de cuenta.</p>
                    </div>
                </div>
 
                <div class="uf-actions">
                    <Link :href="route('admin.users.index')" class="uf-btn uf-btn--cancel">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="uf-btn uf-btn--save">
                        {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; }
.uf-wrap { max-width:780px; margin:0 auto; font-family:'DM Sans',sans-serif; }
.uf-form { display:flex; flex-direction:column; gap:22px; }
.uf-section { background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.uf-section__head { display:flex; align-items:flex-start; gap:14px; padding:18px 24px; border-bottom:1px solid #f0f4f8; background:#f8fafc; }
.uf-num { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#0a3d6b,#2eaa45); color:#fff; font-weight:700; font-size:0.9rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.uf-title { font-size:1rem; font-weight:700; color:#0a3d6b; margin-bottom:2px; }
.uf-sub { font-size:0.8rem; color:#64748b; }
.uf-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; padding:22px 24px; }
.uf-field { display:flex; flex-direction:column; gap:5px; }
.uf-field--full { grid-column: 1 / -1; }
.uf-label { font-size:0.78rem; font-weight:600; color:#374151; letter-spacing:0.03em; }
.uf-req { color:#ef4444; }
.uf-hint { font-size:0.7rem; color:#94a3b8; font-weight:400; margin-left:4px; }
.uf-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.88rem; font-family:'DM Sans',sans-serif; color:#1e293b; background:#f8fafc; outline:none; transition:border-color 0.2s,box-shadow 0.2s; width:100%; }
.uf-input:focus { border-color:#1565a8; background:#fff; box-shadow:0 0 0 3px rgba(21,101,168,0.1); }
.uf-input:disabled { opacity:0.6; cursor:not-allowed; }
.uf-select { cursor:pointer; }
.uf-check { display:flex; align-items:center; gap:9px; font-size:0.85rem; color:#374151; cursor:pointer; }
.uf-check input { width:16px; height:16px; accent-color:#1565a8; cursor:pointer; }

.uf-roles-info { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
.uf-role-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:16px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.uf-role-card p { font-size:0.8rem; color:#64748b; line-height:1.5; margin-top:10px; }
.uf-role-badge { display:inline-block; padding:4px 12px; border-radius:99px; font-size:0.78rem; font-weight:700; }
.uf-role-badge--navy  { background:#dbeafe; color:#1e3a8a; }
.uf-role-badge--blue  { background:#e0f2fe; color:#075985; }
.uf-role-badge--green { background:#dcfce7; color:#166534; }

.uf-actions { display:flex; align-items:center; justify-content:flex-end; gap:12px; }
.uf-btn { display:inline-flex; align-items:center; padding:11px 24px; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:0.9rem; font-weight:600; cursor:pointer; text-decoration:none; border:none; transition:all 0.2s; }
.uf-btn--cancel { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.uf-btn--cancel:hover { background:#e2e8f0; }
.uf-btn--save { background:linear-gradient(135deg,#0a3d6b,#1565a8); color:#fff; box-shadow:0 4px 16px rgba(10,61,107,0.28); }
.uf-btn--save:hover:not(:disabled) { opacity:0.91; transform:translateY(-1px); }
.uf-btn--save:disabled { opacity:0.65; cursor:not-allowed; }
@media (max-width:640px) {
    .uf-grid { grid-template-columns:1fr; }
    .uf-roles-info { grid-template-columns:1fr; }
    .uf-actions { flex-direction:column-reverse; }
    .uf-btn { width:100%; justify-content:center; }
}
</style>