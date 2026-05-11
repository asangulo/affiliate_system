<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

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
    <Head title="Iniciar Sesión | Horizonte S.A.S" />

    <div class="horizonte-login">
        <!-- Fondo animado con paisaje -->
        <div class="bg-landscape">
            <div class="sky"></div>
            <div class="sun-wrapper">
                <div class="sun">
                    <div class="sun-rays"></div>
                </div>
            </div>
            <div class="hills">
                <div class="hill hill-back"></div>
                <div class="hill hill-mid"></div>
                <div class="hill hill-front"></div>
            </div>
        </div>

        <!-- Card de login -->
        <div class="login-container">
            <div class="login-card">
                <!-- Logo / Marca -->
                <div class="brand">
                    <div class="brand-icon">
                        <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Sol -->
                            <circle cx="30" cy="22" r="10" fill="#F5C518"/>
                            <!-- Rayos -->
                            <line x1="30" y1="4" x2="30" y2="9" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="47" y1="11" x2="43.5" y2="14.5" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="52" y1="22" x2="47" y2="22" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="13" y1="11" x2="16.5" y2="14.5" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                            <line x1="8" y1="22" x2="13" y2="22" stroke="#F5C518" stroke-width="2.5" stroke-linecap="round"/>
                            <!-- Colinas -->
                            <ellipse cx="15" cy="46" rx="20" ry="12" fill="#2D7A3A"/>
                            <ellipse cx="45" cy="48" rx="20" ry="11" fill="#3A9E4A"/>
                            <ellipse cx="30" cy="52" rx="32" ry="12" fill="#4DB860"/>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">HORIZONTE</span>
                        <span class="brand-sub">S.A.S · Pre Exequiales</span>
                    </div>
                </div>

                <div class="divider"></div>

                <h2 class="login-title">Bienvenido al Sistema</h2>
                <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

                <!-- Status -->
                <div v-if="status" class="status-msg">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="login-form">
                    <!-- Email -->
                    <div class="field-group">
                        <label for="email">Correo electrónico</label>
                        <div class="input-wrapper">
                            <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="username"
                                placeholder="usuario@horizonte.com"
                                required
                                autofocus
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Contraseña -->
                    <div class="field-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <svg class="field-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                required
                            />
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <!-- Recuérdame + Olvidé contraseña -->
                    <div class="form-options">
                        <label class="remember-label">
                            <input type="checkbox" v-model="form.remember" />
                            <span>Recordarme</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="forgot-link"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-login"
                    >
                        <span v-if="!form.processing">Iniciar Sesión</span>
                        <span v-else class="loading-dots">Ingresando<span>.</span><span>.</span><span>.</span></span>
                    </button>
                </form>

                <p class="login-footer">
                    Sistema de Gestión de Afiliados &copy; {{ new Date().getFullYear() }} · Horizonte S.A.S
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.horizonte-login {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-family: 'DM Sans', sans-serif;
    overflow: hidden;
}

/* ── FONDO PAISAJE ── */
.bg-landscape {
    position: fixed;
    inset: 0;
    z-index: 0;
}

.sky {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, #0a3d6b 0%, #1565a8 30%, #2e8bc0 60%, #87ceeb 100%);
}

.sun-wrapper {
    position: absolute;
    top: 8%;
    left: 50%;
    transform: translateX(-50%);
    animation: sunRise 2s ease-out forwards;
}

@keyframes sunRise {
    from { transform: translateX(-50%) translateY(30px); opacity: 0; }
    to   { transform: translateX(-50%) translateY(0);   opacity: 1; }
}

.sun {
    width: 120px; height: 120px;
    border-radius: 50%;
    background: radial-gradient(circle at 40% 40%, #ffe066, #f5a623);
    box-shadow: 0 0 60px 30px rgba(245,166,35,0.45), 0 0 120px 60px rgba(245,166,35,0.2);
    position: relative;
    animation: sunGlow 4s ease-in-out infinite alternate;
}

@keyframes sunGlow {
    from { box-shadow: 0 0 60px 30px rgba(245,166,35,0.45), 0 0 120px 60px rgba(245,166,35,0.2); }
    to   { box-shadow: 0 0 80px 40px rgba(245,166,35,0.6),  0 0 160px 80px rgba(245,166,35,0.3); }
}

.sun-rays {
    position: absolute;
    inset: -40px;
    border-radius: 50%;
    background: conic-gradient(
        from 0deg,
        transparent 0deg, rgba(255,220,80,0.3) 5deg, transparent 10deg,
        transparent 20deg, rgba(255,220,80,0.3) 25deg, transparent 30deg,
        transparent 40deg, rgba(255,220,80,0.3) 45deg, transparent 50deg,
        transparent 60deg, rgba(255,220,80,0.3) 65deg, transparent 70deg,
        transparent 80deg, rgba(255,220,80,0.3) 85deg, transparent 90deg,
        transparent 100deg, rgba(255,220,80,0.3) 105deg, transparent 110deg,
        transparent 120deg, rgba(255,220,80,0.3) 125deg, transparent 130deg,
        transparent 140deg, rgba(255,220,80,0.3) 145deg, transparent 150deg,
        transparent 160deg, rgba(255,220,80,0.3) 165deg, transparent 170deg,
        transparent 180deg, rgba(255,220,80,0.3) 185deg, transparent 190deg,
        transparent 200deg, rgba(255,220,80,0.3) 205deg, transparent 210deg,
        transparent 220deg, rgba(255,220,80,0.3) 225deg, transparent 230deg,
        transparent 240deg, rgba(255,220,80,0.3) 245deg, transparent 250deg,
        transparent 260deg, rgba(255,220,80,0.3) 265deg, transparent 270deg,
        transparent 280deg, rgba(255,220,80,0.3) 285deg, transparent 290deg,
        transparent 300deg, rgba(255,220,80,0.3) 305deg, transparent 310deg,
        transparent 320deg, rgba(255,220,80,0.3) 325deg, transparent 330deg,
        transparent 340deg, rgba(255,220,80,0.3) 345deg, transparent 350deg,
        transparent 360deg
    );
    animation: rotateSun 20s linear infinite;
}

@keyframes rotateSun {
    to { transform: rotate(360deg); }
}

.hills {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 55%;
}

.hill {
    position: absolute;
    bottom: 0;
    border-radius: 50% 50% 0 0;
}

.hill-back {
    left: -10%; right: -10%;
    height: 55%;
    background: linear-gradient(180deg, #1a6b2a 0%, #145420 100%);
}

.hill-mid {
    left: -5%; right: 30%;
    height: 65%;
    background: linear-gradient(180deg, #228b35 0%, #1a6b2a 100%);
}

.hill-front {
    left: 25%; right: -10%;
    height: 60%;
    background: linear-gradient(180deg, #2eaa45 0%, #228b35 100%);
}

/* ── CONTAINER LOGIN ── */
.login-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
    padding: 20px;
    animation: cardAppear 0.7s ease-out 0.3s both;
}

@keyframes cardAppear {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.login-card {
    background: rgba(255, 255, 255, 0.97);
    border-radius: 20px;
    padding: 40px 36px 32px;
    box-shadow:
        0 25px 60px rgba(0,0,0,0.3),
        0 0 0 1px rgba(255,255,255,0.6) inset,
        0 1px 0 rgba(255,255,255,0.8) inset;
    backdrop-filter: blur(12px);
}

/* ── MARCA ── */
.brand {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}

.brand-icon svg {
    width: 56px;
    height: 56px;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.15));
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #0a3d6b;
    line-height: 1;
    letter-spacing: 0.05em;
}

.brand-sub {
    font-size: 0.72rem;
    font-weight: 500;
    color: #2e8bc0;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-top: 3px;
}

.divider {
    height: 2px;
    background: linear-gradient(90deg, #0a3d6b, #2eaa45, #f5a623, transparent);
    border-radius: 2px;
    margin-bottom: 22px;
    opacity: 0.6;
}

.login-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    color: #0a3d6b;
    font-weight: 600;
    margin-bottom: 4px;
}

.login-subtitle {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 24px;
}

.status-msg {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    color: #065f46;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 0.85rem;
    margin-bottom: 16px;
}

/* ── FORMULARIO ── */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-group label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #334155;
    letter-spacing: 0.03em;
}

.input-wrapper {
    position: relative;
}

.field-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 17px;
    height: 17px;
    color: #94a3b8;
    pointer-events: none;
}

.input-wrapper input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9rem;
    font-family: 'DM Sans', sans-serif;
    color: #1e293b;
    background: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    outline: none;
}

.input-wrapper input:focus {
    border-color: #1565a8;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(21, 101, 168, 0.12);
}

.input-wrapper input::placeholder {
    color: #cbd5e1;
}

.form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: -4px;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.83rem;
    color: #475569;
    cursor: pointer;
}

.remember-label input[type="checkbox"] {
    width: 15px; height: 15px;
    accent-color: #1565a8;
    cursor: pointer;
}

.forgot-link {
    font-size: 0.83rem;
    color: #1565a8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.forgot-link:hover { color: #2eaa45; }

.btn-login {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #0a3d6b 0%, #1565a8 50%, #0d7c30 100%);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 20px rgba(10, 61, 107, 0.35);
    margin-top: 4px;
}

.btn-login:hover:not(:disabled) {
    opacity: 0.92;
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(10, 61, 107, 0.45);
}

.btn-login:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.loading-dots span {
    animation: blink 1.2s infinite;
    animation-fill-mode: both;
}
.loading-dots span:nth-child(2) { animation-delay: 0.2s; }
.loading-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes blink {
    0%, 80%, 100% { opacity: 0; }
    40% { opacity: 1; }
}

.login-footer {
    text-align: center;
    font-size: 0.73rem;
    color: #94a3b8;
    margin-top: 24px;
    line-height: 1.5;
}
</style>