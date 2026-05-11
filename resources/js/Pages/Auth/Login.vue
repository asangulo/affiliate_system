<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <h2 class="hz-form__title">Bienvenido de nuevo</h2>
        <p class="hz-form__sub">Ingresa tus credenciales para acceder al sistema</p>

        <div v-if="status" class="hz-alert hz-alert--success">{{ status }}</div>

        <form @submit.prevent="submit" class="hz-form">
            <!-- Email -->
            <div class="hz-field">
                <label for="email" class="hz-label">Correo electrónico</label>
                <div class="hz-input-wrap">
                    <svg class="hz-input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    <input id="email" v-model="form.email" type="email" autocomplete="username"
                        placeholder="usuario@horizonte.com" required autofocus class="hz-input"/>
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <!-- Contraseña -->
            <div class="hz-field">
                <label for="password" class="hz-label">Contraseña</label>
                <div class="hz-input-wrap">
                    <svg class="hz-input-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <input id="password" v-model="form.password" type="password" autocomplete="current-password"
                        placeholder="••••••••" required class="hz-input"/>
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <!-- Opciones -->
            <div class="hz-row">
                <label class="hz-check">
                    <input type="checkbox" v-model="form.remember"/>
                    <span>Recordarme</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')" class="hz-link">
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <button type="submit" :disabled="form.processing" class="hz-btn-primary">
                <span v-if="!form.processing">Iniciar Sesión</span>
                <span v-else>Ingresando...</span>
            </button>

            <p class="hz-form__footer-link">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="hz-link">Regístrate aquí</Link>
            </p>
        </form>
    </GuestLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; }

.hz-form__title {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    color: #0a3d6b;
    font-weight: 700;
    margin-bottom: 4px;
}

.hz-form__sub {
    font-size: 0.83rem;
    color: #64748b;
    margin-bottom: 22px;
    font-family: 'DM Sans', sans-serif;
}

.hz-alert {
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.83rem;
    margin-bottom: 16px;
}

.hz-alert--success {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    color: #065f46;
}

.hz-form {
    display: flex;
    flex-direction: column;
    gap: 17px;
    font-family: 'DM Sans', sans-serif;
}

.hz-field { display: flex; flex-direction: column; gap: 5px; }

.hz-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #334155;
    letter-spacing: 0.03em;
}

.hz-input-wrap { position: relative; }

.hz-input-icon {
    position: absolute;
    left: 11px; top: 50%;
    transform: translateY(-50%);
    width: 16px; height: 16px;
    color: #94a3b8;
    pointer-events: none;
}

.hz-input {
    width: 100%;
    padding: 10px 13px 10px 38px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.88rem;
    font-family: 'DM Sans', sans-serif;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}

.hz-input:focus {
    border-color: #1565a8;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(21,101,168,0.12);
}

.hz-input::placeholder { color: #cbd5e1; }

.hz-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: -4px;
}

.hz-check {
    display: flex; align-items: center; gap: 7px;
    font-size: 0.82rem; color: #475569; cursor: pointer;
}

.hz-check input[type="checkbox"] { width: 14px; height: 14px; accent-color: #1565a8; }

.hz-link {
    font-size: 0.82rem;
    color: #1565a8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.18s;
}

.hz-link:hover { color: #2eaa45; }

.hz-btn-primary {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 11px;
    background: linear-gradient(135deg, #0a3d6b 0%, #1565a8 50%, #0d7c30 100%);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.93rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(10,61,107,0.32);
    margin-top: 2px;
}

.hz-btn-primary:hover:not(:disabled) {
    opacity: 0.91;
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(10,61,107,0.42);
}

.hz-btn-primary:disabled { opacity: 0.65; cursor: not-allowed; }

.hz-form__footer-link {
    text-align: center;
    font-size: 0.82rem;
    color: #64748b;
    margin-top: -4px;
}
</style>