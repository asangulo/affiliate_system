<script setup>
// GuestLayout.vue
// Usado por: Login, Register, ForgotPassword, ResetPassword, ConfirmPassword, VerifyEmail
</script>

<template>
    <div class="hz-guest">
        <!-- Fondo vivo con paisaje -->
        <div class="hz-bg" aria-hidden="true">
            <div class="hz-bg__sky"></div>
            <div class="hz-bg__sun-wrap">
                <div class="hz-bg__sun">
                    <div class="hz-bg__rays"></div>
                </div>
            </div>
            <div class="hz-bg__hills">
                <div class="hz-bg__hill hz-bg__hill--back"></div>
                <div class="hz-bg__hill hz-bg__hill--mid"></div>
                <div class="hz-bg__hill hz-bg__hill--front"></div>
            </div>
        </div>

        <!-- Card central con contenido de la página -->
        <div class="hz-guest__card">
            <!-- Logo marca -->
            <div class="hz-guest__brand">
                <svg viewBox="0 0 64 64" fill="none" class="hz-guest__svg">
                    <circle cx="32" cy="22" r="11" fill="#F5C518"/>
                    <g stroke="#F5C518" stroke-width="2.5" stroke-linecap="round">
                        <line x1="32" y1="4"  x2="32" y2="9"/>
                        <line x1="50" y1="11" x2="46.5" y2="14.5"/>
                        <line x1="56" y1="22" x2="51" y2="22"/>
                        <line x1="14" y1="11" x2="17.5" y2="14.5"/>
                        <line x1="8"  y1="22" x2="13" y2="22"/>
                        <line x1="46" y1="33" x2="43" y2="30"/>
                        <line x1="18" y1="33" x2="21" y2="30"/>
                    </g>
                    <ellipse cx="16" cy="50" rx="22" ry="13" fill="#1a6b2a"/>
                    <ellipse cx="48" cy="52" rx="22" ry="12" fill="#228b35"/>
                    <ellipse cx="32" cy="56" rx="34" ry="12" fill="#2eaa45"/>
                </svg>
                <div class="hz-guest__brand-text">
                    <span class="hz-guest__name">HORIZONTE</span>
                    <span class="hz-guest__sub">S.A.S · Pre Exequiales</span>
                </div>
            </div>

            <div class="hz-guest__sep"></div>

            <!-- Slot: contenido de cada página auth -->
            <slot />

            <p class="hz-guest__footer">
                Sistema de Gestión de Afiliados &copy; {{ new Date().getFullYear() }}
            </p>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.hz-guest {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-family: 'DM Sans', sans-serif;
    overflow: hidden;
    padding: 20px;
}

/* ── FONDO ── */
.hz-bg {
    position: fixed;
    inset: 0;
    z-index: 0;
}

.hz-bg__sky {
    position: absolute; inset: 0;
    background: linear-gradient(180deg,
        #062b4e 0%,
        #1262a8 35%,
        #2e8bc0 65%,
        #7ec8e3 100%
    );
}

.hz-bg__sun-wrap {
    position: absolute;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    animation: sunRise 1s ease-out forwards;
}

@keyframes sunRise {
    from { transform: translateX(-50%) translateY(40px); opacity: 0; }
    to   { transform: translateX(-50%) translateY(0);    opacity: 1; }
}

.hz-bg__sun {
    width: 110px; height: 110px;
    border-radius: 50%;
    background: radial-gradient(circle at 38% 38%, #ffe45a, #f59819);
    box-shadow: 0 0 60px 28px rgba(245,152,25,0.45), 0 0 130px 60px rgba(245,152,25,0.18);
    position: relative;
    animation: sunGlow 5s ease-in-out infinite alternate;
}

@keyframes sunGlow {
    from { box-shadow: 0 0 60px 28px rgba(245,152,25,0.45), 0 0 130px 60px rgba(245,152,25,0.18); }
    to   { box-shadow: 0 0 80px 40px rgba(245,152,25,0.6),  0 0 170px 80px rgba(245,152,25,0.28); }
}

.hz-bg__rays {
    position: absolute; inset: -45px; border-radius: 50%;
    background: conic-gradient(
        from 0deg,
        transparent 0deg,   rgba(255,220,70,0.28) 5deg,  transparent 10deg,
        transparent 22deg,  rgba(255,220,70,0.28) 27deg, transparent 32deg,
        transparent 44deg,  rgba(255,220,70,0.28) 49deg, transparent 54deg,
        transparent 66deg,  rgba(255,220,70,0.28) 71deg, transparent 76deg,
        transparent 88deg,  rgba(255,220,70,0.28) 93deg, transparent 98deg,
        transparent 110deg, rgba(255,220,70,0.28) 115deg,transparent 120deg,
        transparent 132deg, rgba(255,220,70,0.28) 137deg,transparent 142deg,
        transparent 154deg, rgba(255,220,70,0.28) 159deg,transparent 164deg,
        transparent 176deg, rgba(255,220,70,0.28) 181deg,transparent 186deg,
        transparent 198deg, rgba(255,220,70,0.28) 203deg,transparent 208deg,
        transparent 220deg, rgba(255,220,70,0.28) 225deg,transparent 230deg,
        transparent 242deg, rgba(255,220,70,0.28) 247deg,transparent 252deg,
        transparent 264deg, rgba(255,220,70,0.28) 269deg,transparent 274deg,
        transparent 286deg, rgba(255,220,70,0.28) 291deg,transparent 296deg,
        transparent 308deg, rgba(255,220,70,0.28) 313deg,transparent 318deg,
        transparent 330deg, rgba(255,220,70,0.28) 335deg,transparent 340deg,
        transparent 352deg, rgba(255,220,70,0.28) 357deg,transparent 360deg
    );
    animation: rotateSun 22s linear infinite;
}

@keyframes rotateSun { to { transform: rotate(360deg); } }

.hz-bg__hills {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 52%;
}

.hz-bg__hill {
    position: absolute;
    bottom: 0;
    border-radius: 50% 50% 0 0;
}

.hz-bg__hill--back  { left: -12%; right: -12%; height: 55%; background: linear-gradient(180deg, #155824, #0e3a18); }
.hz-bg__hill--mid   { left: -6%;  right: 28%;  height: 68%; background: linear-gradient(180deg, #1e7f33, #155824); }
.hz-bg__hill--front { left: 22%;  right: -8%;  height: 62%; background: linear-gradient(180deg, #27a83e, #1e7f33); }

/* ── CARD ── */
.hz-guest__card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 420px;
    background: rgba(255,255,255,0.97);
    border-radius: 22px;
    padding: 36px 34px 28px;
    box-shadow:
        0 28px 70px rgba(0,0,0,0.32),
        0 0 0 1px rgba(255,255,255,0.55) inset,
        0 1px 0 rgba(255,255,255,0.8) inset;
    backdrop-filter: blur(14px);
    animation: cardUp 0.65s ease-out 0.2s both;
}

@keyframes cardUp {
    from { transform: translateY(22px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

/* Brand dentro de la card */
.hz-guest__brand {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 18px;
}

.hz-guest__svg { width: 52px; height: 52px; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.15)); }

.hz-guest__brand-text { display: flex; flex-direction: column; }

.hz-guest__name {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: #0a3d6b;
    letter-spacing: 0.05em;
    line-height: 1;
}

.hz-guest__sub {
    font-size: 0.68rem;
    font-weight: 500;
    color: #2e8bc0;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-top: 3px;
}

.hz-guest__sep {
    height: 2px;
    background: linear-gradient(90deg, #0a3d6b, #2eaa45, #f5a623, transparent);
    border-radius: 2px;
    margin-bottom: 22px;
    opacity: 0.55;
}

.hz-guest__footer {
    text-align: center;
    font-size: 0.72rem;
    color: #94a3b8;
    margin-top: 22px;
}

/* ── Estilos globales para los formularios que usan este layout ── */
/* Se aplican como :deep() en las páginas hijas, pero también los exponemos aquí
   para componentes que usen clases Tailwind heredadas */
</style>