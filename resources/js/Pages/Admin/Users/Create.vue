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
                    <div class="uf-role-card">
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