<!-- ════════════════════════════════════════════════════════════════
     ForgotPassword.vue  —  Coloca en resources/js/Pages/Auth/
     ════════════════════════════════════════════════════════════════ -->
<!--
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
 
defineProps({ status: { type: String } });
 
const form = useForm({ email: '' });
const submit = () => form.post(route('password.email'));
</script>
 
<template>
    <GuestLayout>
        <Head title="Recuperar Contraseña" />
        <h2 class="hz-form__title">Recuperar contraseña</h2>
        <p class="hz-form__sub">Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.</p>
        <div v-if="status" class="hz-alert hz-alert--success">{{ status }}</div>
        <form @submit.prevent="submit" class="hz-form">
            <div class="hz-field">
                <label for="email" class="hz-label">Correo electrónico</label>
                <div class="hz-input-wrap">
                    <svg class="hz-input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <input id="email" v-model="form.email" type="email" autocomplete="username"
                        placeholder="correo@horizonte.com" required autofocus class="hz-input"/>
                </div>
                <InputError :message="form.errors.email" />
            </div>
            <button type="submit" :disabled="form.processing" class="hz-btn-primary">
                <span v-if="!form.processing">Enviar enlace de recuperación</span>
                <span v-else>Enviando...</span>
            </button>
            <p class="hz-form__footer-link">
                <Link :href="route('login')" class="hz-link">← Volver a iniciar sesión</Link>
            </p>
        </form>
    </GuestLayout>
</template>

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