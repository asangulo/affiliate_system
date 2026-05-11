<!-- ════════════════════════════════════════════════════════════════
     VerifyEmail.vue  —  Coloca en resources/js/Pages/Auth/
     ════════════════════════════════════════════════════════════════ -->
<!--
<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
 
const props = defineProps({ status: { type: String } });
const form = useForm({});
const submit = () => form.post(route('verification.send'));
const sent = computed(() => props.status === 'verification-link-sent');
</script>
 
<template>
    <GuestLayout>
        <Head title="Verificar Email" />
        <h2 class="hz-form__title">Verificar correo</h2>
        <p class="hz-form__sub">Te enviamos un enlace de verificación. Si no lo recibiste, solicita uno nuevo.</p>
        <div v-if="sent" class="hz-alert hz-alert--success">Se envió un nuevo enlace a tu correo.</div>
        <form @submit.prevent="submit" class="hz-form">
            <button type="submit" :disabled="form.processing" class="hz-btn-primary">
                <span v-if="!form.processing">Reenviar correo de verificación</span>
                <span v-else>Enviando...</span>
            </button>
            <p class="hz-form__footer-link">
                <Link :href="route('logout')" method="post" as="button" class="hz-link">Cerrar sesión</Link>
            </p>
        </form>
    </GuestLayout>
</template>
-->

    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
    *, *::before, *::after { box-sizing: border-box; }
    .hz-form__title { font-family:'Playfair Display',serif; font-size:1.3rem; color:#0a3d6b; font-weight:700; margin-bottom:4px; }
    .hz-form__sub   { font-size:0.83rem; color:#64748b; margin-bottom:20px; font-family:'DM Sans',sans-serif; }
    .hz-alert--success { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; border-radius:8px; padding:10px 14px; font-size:0.83rem; margin-bottom:16px; }
    .hz-form        { display:flex; flex-direction:column; gap:16px; font-family:'DM Sans',sans-serif; }
    .hz-field       { display:flex; flex-direction:column; gap:5px; }
    .hz-label       { font-size:0.78rem; font-weight:600; color:#334155; letter-spacing:0.03em; }
    .hz-input-wrap  { position:relative; }
    .hz-input-icon  { position:absolute; left:11px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:#94a3b8; pointer-events:none; }
    .hz-input       { width:100%; padding:10px 13px 10px 37px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.87rem; font-family:'DM Sans',sans-serif; color:#1e293b; background:#f8fafc; outline:none; transition:border-color 0.2s,box-shadow 0.2s; }
    .hz-input:focus { border-color:#1565a8; background:#fff; box-shadow:0 0 0 3px rgba(21,101,168,0.12); }
    .hz-input::placeholder { color:#cbd5e1; }
    .hz-link        { font-size:0.82rem; color:#1565a8; text-decoration:none; font-weight:500; }
    .hz-link:hover  { color:#2eaa45; }
    .hz-btn-primary { width:100%; padding:12px; border:none; border-radius:11px; background:linear-gradient(135deg,#0a3d6b 0%,#1565a8 50%,#0d7c30 100%); color:#fff; font-family:'DM Sans',sans-serif; font-size:0.93rem; font-weight:600; cursor:pointer; box-shadow:0 4px 18px rgba(10,61,107,0.32); margin-top:2px; }
    .hz-btn-primary:hover:not(:disabled) { opacity:0.91; transform:translateY(-1px); }
    .hz-btn-primary:disabled { opacity:0.65; cursor:not-allowed; }
    .hz-form__footer-link { text-align:center; font-size:0.82rem; color:#64748b; }
