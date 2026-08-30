<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    user:       Object,
    roles:      Array,
    affiliates: Array,
});

const form = useForm({
    name:         props.user.name,
    email:        props.user.email,
    password:     '',
    role:         props.user.roles?.[0]?.name ?? '',
    affiliate_id: props.user.affiliate_id ?? '',
});

const submit = () => form.patch(route('admin.users.update', props.user.id));
</script>

<template>
    <Head title="Editar Usuario"/>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Usuario</h2>
        </template>

        <div class="py-8">
            <div class="uf-wrap">
                <form @submit.prevent="submit" class="uf-form">

                    <!-- Sección datos -->
                    <div class="uf-section">
                        <div class="uf-section__head">
                            <div class="uf-num">1</div>
                            <div>
                                <div class="uf-title">Datos del usuario</div>
                                <div class="uf-sub">Información de acceso al sistema</div>
                            </div>
                        </div>
                        <div class="uf-grid">
                            <div class="uf-field">
                                <label class="uf-label">Nombre <span class="uf-req">*</span></label>
                                <input v-model="form.name" type="text" class="uf-input" required/>
                                <InputError :message="form.errors.name"/>
                            </div>
                            <div class="uf-field">
                                <label class="uf-label">Email <span class="uf-req">*</span></label>
                                <input v-model="form.email" type="email" class="uf-input" required/>
                                <InputError :message="form.errors.email"/>
                            </div>
                            <div class="uf-field">
                                <label class="uf-label">
                                    Nueva contraseña
                                    <span class="uf-hint">(dejar vacío para no cambiar)</span>
                                </label>
                                <input v-model="form.password" type="password" class="uf-input" placeholder="••••••••"/>
                                <InputError :message="form.errors.password"/>
                            </div>
                            <div class="uf-field">
                                <label class="uf-label">Rol <span class="uf-req">*</span></label>
                                <select v-model="form.role" class="uf-input uf-select" required>
                                    <option value="">Seleccionar rol...</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.role"/>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="uf-actions">
                        <Link :href="route('admin.users.index')" class="uf-btn uf-btn--cancel">
                            Cancelar
                        </Link>
                        <button type="submit" class="uf-btn uf-btn--save" :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>

                </form>
            </div>
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
.uf-label { font-size:0.78rem; font-weight:600; color:#374151; letter-spacing:0.03em; }
.uf-req { color:#ef4444; }
.uf-hint { font-size:0.7rem; color:#94a3b8; font-weight:400; margin-left:4px; }
.uf-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.88rem; font-family:'DM Sans',sans-serif; color:#1e293b; background:#f8fafc; outline:none; transition:border-color 0.2s,box-shadow 0.2s; width:100%; }
.uf-input:focus { border-color:#1565a8; background:#fff; box-shadow:0 0 0 3px rgba(21,101,168,0.1); }
.uf-select { cursor:pointer; }
.uf-actions { display:flex; align-items:center; justify-content:flex-end; gap:12px; }
.uf-btn { display:inline-flex; align-items:center; padding:11px 24px; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:0.9rem; font-weight:600; cursor:pointer; text-decoration:none; border:none; transition:all 0.2s; }
.uf-btn--cancel { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.uf-btn--cancel:hover { background:#e2e8f0; }
.uf-btn--save { background:linear-gradient(135deg,#0a3d6b,#1565a8); color:#fff; box-shadow:0 4px 16px rgba(10,61,107,0.28); }
.uf-btn--save:hover:not(:disabled) { opacity:0.91; transform:translateY(-1px); }
.uf-btn--save:disabled { opacity:0.65; cursor:not-allowed; }
@media (max-width:640px) {
    .uf-grid { grid-template-columns:1fr; }
    .uf-actions { flex-direction:column-reverse; }
    .uf-btn { width:100%; justify-content:center; }
}
</style>