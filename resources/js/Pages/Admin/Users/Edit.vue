<!-- ═══════════════════════════════════════════════════════════
     Admin/Users/Edit.vue
     Coloca en: resources/js/Pages/Admin/Users/Edit.vue
     Solo cambia el <script setup> y el <template #header>
     El resto de estilos es igual — copia el <style> de abajo
     ═══════════════════════════════════════════════════════════
 
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
    password:     '',           // vacío = no cambia
    role:         props.user.roles?.[0]?.name ?? '',
    affiliate_id: props.user.affiliate_id ?? '',
});
 
const submit = () => form.patch(route('admin.users.update', props.user.id));
</script>
 
<template>
    <Head title="Editar Usuario"/>
    <AuthenticatedLayout>
        <template #header>
            <h2>Editar Usuario</h2>
            <p>Modificando: <strong>{{ user.name }}</strong></p>
        </template>
        ... mismo formulario que Create pero:
        - password con placeholder "Dejar vacío para no cambiar"
        - submit llama a form.patch(route('admin.users.update', user.id))
        - botón dice "Guardar Cambios"
    </AuthenticatedLayout>
</template>
-->
 
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing: border-box; }
 
.uf-wrap { max-width:780px; margin:0 auto; font-family:'DM Sans',sans-serif; }
.uf-form { display:flex; flex-direction:column; gap:22px; }
 
.uf-section { background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
 
.uf-section__head { display:flex; align-items:flex-start; gap:14px; padding:18px 24px; border-bottom:1px solid #f0f4f8; background:#f8fafc; }
.uf-num { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#0a3d6b,#2eaa45); color:#fff; font-weight:700; font-size:0.9rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.uf-title { font-size:1rem; font-weight:700; color:#0a3d6b; margin-bottom:2px; }
.uf-sub   { font-size:0.8rem; color:#64748b; }
 
.uf-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; padding:22px 24px; }
.uf-field { display:flex; flex-direction:column; gap:5px; }
.uf-field--full { grid-column:1/-1; }
 
.uf-label { font-size:0.78rem; font-weight:600; color:#374151; letter-spacing:0.03em; }
.uf-req   { color:#ef4444; }
.uf-hint  { font-size:0.7rem; color:#94a3b8; font-weight:400; margin-left:4px; }
 
.uf-input { padding:9px 13px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:0.88rem; font-family:'DM Sans',sans-serif; color:#1e293b; background:#f8fafc; outline:none; transition:border-color 0.2s,box-shadow 0.2s; width:100%; }
.uf-input:focus { border-color:#1565a8; background:#fff; box-shadow:0 0 0 3px rgba(21,101,168,0.1); }
.uf-input:disabled { background:#f1f5f9; cursor:not-allowed; opacity:0.6; }
.uf-select { cursor:pointer; }
 
.uf-check { display:flex; align-items:center; gap:8px; font-size:0.85rem; color:#374151; cursor:pointer; }
.uf-check input { width:15px; height:15px; accent-color:#1565a8; }
 
/* Roles info */
.uf-roles-info { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
.uf-role-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; display:flex; flex-direction:column; gap:6px; }
.uf-role-card p { font-size:0.78rem; color:#64748b; line-height:1.5; }
.uf-role-badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:0.72rem; font-weight:700; }
.uf-role-badge--navy  { background:#dbeafe; color:#1e3a8a; }
.uf-role-badge--blue  { background:#e0f2fe; color:#075985; }
.uf-role-badge--green { background:#dcfce7; color:#166534; }
 
/* Acciones */
.uf-actions { display:flex; align-items:center; justify-content:flex-end; gap:12px; }
.uf-btn { display:inline-flex; align-items:center; padding:11px 24px; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:0.9rem; font-weight:600; cursor:pointer; text-decoration:none; border:none; transition:all 0.2s; }
.uf-btn--cancel { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.uf-btn--cancel:hover { background:#e2e8f0; }
.uf-btn--save { background:linear-gradient(135deg,#0a3d6b,#1565a8); color:#fff; box-shadow:0 4px 16px rgba(10,61,107,0.28); }
.uf-btn--save:hover:not(:disabled) { opacity:0.91; transform:translateY(-1px); }
.uf-btn--save:disabled { opacity:0.65; cursor:not-allowed; }
 
@media (max-width:640px) {
    .uf-grid { grid-template-columns:1fr; }
    .uf-field--full { grid-column:1; }
    .uf-roles-info { grid-template-columns:1fr; }
    .uf-actions { flex-direction:column-reverse; }
    .uf-btn { width:100%; justify-content:center; }
}
</style>