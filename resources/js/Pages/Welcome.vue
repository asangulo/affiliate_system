<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
});

const menuOpen = ref(false);

const planes = [
    {
        name: 'Plan Esencial',
        precio: '$25.000',
        periodo: '/mes',
        color: 'plan--sky',
        descripcion: 'Protección básica con dignidad para tu familia.',
        beneficios: [
            'Servicio funerario completo',
            'Traslado local incluido',
            'Asesoría 24/7',
            'Hasta 2 beneficiarios',
        ],
        destacado: false,
    },
    {
        name: 'Plan Familiar',
        precio: '$45.000',
        periodo: '/mes',
        color: 'plan--green',
        descripcion: 'La opción más elegida por las familias colombianas.',
        beneficios: [
            'Todo lo del Plan Esencial',
            'Traslado nacional incluido',
            'Sala de velación premium',
            'Hasta 5 beneficiarios',
            'Asesoría jurídica básica',
        ],
        destacado: true,
    },
    {
        name: 'Plan Integral',
        precio: '$75.000',
        periodo: '/mes',
        color: 'plan--gold',
        descripcion: 'Cobertura total y tranquilidad para toda tu familia.',
        beneficios: [
            'Todo lo del Plan Familiar',
            'Traslado internacional',
            'Sala VIP de velación',
            'Beneficiarios ilimitados',
            'Asesoría jurídica completa',
            'Servicio de repatriación',
        ],
        destacado: false,
    },
];

const valores = [
    { icon: '🕊️', titulo: 'Dignidad', desc: 'Cada servicio se presta con el más alto respeto y dignidad hacia nuestros afiliados y sus familias.' },
    { icon: '🤝', titulo: 'Compromiso', desc: 'Más de 20 años acompañando a las familias del Huila en los momentos más difíciles.' },
    { icon: '❤️', titulo: 'Calidez', desc: 'Un equipo humano dedicado a ofrecer consuelo, apoyo y orientación en cada momento.' },
    { icon: '⭐', titulo: 'Calidad', desc: 'Instalaciones modernas y personal altamente capacitado para brindarte el mejor servicio.' },
];
</script>

<template>
    <Head title="Horizonte S.A.S Pre Exequiales — Planes de Afiliación" />

    <div class="lp">

        <!-- ════════════ NAVBAR ════════════ -->
        <nav class="lp-nav">
            <div class="lp-nav__inner">
                <!-- Logo -->
                <div class="lp-nav__brand">
                    <svg viewBox="0 0 56 56" fill="none" class="lp-nav__svg">
                        <circle cx="28" cy="18" r="9" fill="#F5C518"/>
                        <g stroke="#F5C518" stroke-width="2.2" stroke-linecap="round">
                            <line x1="28" y1="3"  x2="28" y2="7.5"/>
                            <line x1="43" y1="9"  x2="40" y2="12"/>
                            <line x1="48" y1="18" x2="43.5" y2="18"/>
                            <line x1="13" y1="9"  x2="16" y2="12"/>
                            <line x1="8"  y1="18" x2="12.5" y2="18"/>
                        </g>
                        <ellipse cx="14" cy="42" rx="18" ry="11" fill="#1a6b2a"/>
                        <ellipse cx="42" cy="44" rx="18" ry="10" fill="#228b35"/>
                        <ellipse cx="28" cy="49" rx="29" ry="10" fill="#2eaa45"/>
                    </svg>
                    <div class="lp-nav__text">
                        <span class="lp-nav__name">HORIZONTE</span>
                        <span class="lp-nav__sub">Pre Exequiales</span>
                    </div>
                </div>

                <!-- Links desktop -->
                <div class="lp-nav__links">
                    <a href="#nosotros" class="lp-nav__a">Nosotros</a>
                    <a href="#planes" class="lp-nav__a">Planes</a>
                    <a href="#valores" class="lp-nav__a">Valores</a>
                    <a href="#contacto" class="lp-nav__a">Contacto</a>
                </div>

                <!-- CTA -->
                <div class="lp-nav__cta">
                    <Link v-if="canLogin" :href="route('login')" class="lp-btn lp-btn--ghost">Iniciar sesión</Link>
                    <Link v-if="canRegister" :href="route('register')" class="lp-btn lp-btn--solid">Afiliarme</Link>
                </div>

                <!-- Burger -->
                <button class="lp-nav__burger" @click="menuOpen = !menuOpen">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <path v-if="!menuOpen" d="M4 6h16M4 12h16M4 18h16"/>
                        <path v-else d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile menu -->
            <div class="lp-mobile-menu" :class="{ 'lp-mobile-menu--open': menuOpen }">
                <a href="#nosotros" class="lp-mobile-menu__a" @click="menuOpen=false">Nosotros</a>
                <a href="#planes" class="lp-mobile-menu__a" @click="menuOpen=false">Planes</a>
                <a href="#valores" class="lp-mobile-menu__a" @click="menuOpen=false">Valores</a>
                <a href="#contacto" class="lp-mobile-menu__a" @click="menuOpen=false">Contacto</a>
                <div class="lp-mobile-menu__cta">
                    <Link v-if="canLogin" :href="route('login')" class="lp-btn lp-btn--ghost">Iniciar sesión</Link>
                    <Link v-if="canRegister" :href="route('register')" class="lp-btn lp-btn--solid">Afiliarme</Link>
                </div>
            </div>
        </nav>

        <!-- ════════════ HERO ════════════ -->
        <section class="lp-hero">
            <!-- Fondo animado -->
            <div class="lp-hero__bg">
                <div class="lp-hero__sky"></div>
                <div class="lp-hero__sun-wrap">
                    <div class="lp-hero__sun">
                        <div class="lp-hero__rays"></div>
                    </div>
                </div>
                <div class="lp-hero__hills">
                    <div class="lp-hero__hill lp-hero__hill--1"></div>
                    <div class="lp-hero__hill lp-hero__hill--2"></div>
                    <div class="lp-hero__hill lp-hero__hill--3"></div>
                </div>
            </div>

            <!-- Contenido hero -->
            <div class="lp-hero__content">
                <span class="lp-hero__badge">20 años de confianza</span>
                <h1 class="lp-hero__h1">
                    Protege a tu familia<br>
                    <span class="lp-hero__h1--accent">con dignidad y amor</span>
                </h1>
                <p class="lp-hero__p">
                    Somos Horizonte S.A.S Pre Exequiales. Te acompañamos en los momentos más difíciles,
                    ofreciendo planes de afiliación accesibles para que tu familia siempre esté protegida.
                </p>
                <div class="lp-hero__btns">
                    <Link v-if="canRegister" :href="route('register')" class="lp-btn lp-btn--hero-primary">
                        Ver planes de afiliación
                    </Link>
                    <a href="#nosotros" class="lp-btn lp-btn--hero-ghost">Conocer más</a>
                </div>

                <!-- Estadísticas -->
                <div class="lp-hero__stats">
                    <div class="lp-hero__stat">
                        <span class="lp-hero__stat-n">+15.000</span>
                        <span class="lp-hero__stat-l">Familias afiliadas</span>
                    </div>
                    <div class="lp-hero__stat-sep"></div>
                    <div class="lp-hero__stat">
                        <span class="lp-hero__stat-n">20 años</span>
                        <span class="lp-hero__stat-l">De experiencia</span>
                    </div>
                    <div class="lp-hero__stat-sep"></div>
                    <div class="lp-hero__stat">
                        <span class="lp-hero__stat-n">24/7</span>
                        <span class="lp-hero__stat-l">Atención disponible</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════ NOSOTROS ════════════ -->
        <section class="lp-section lp-about" id="nosotros">
            <div class="lp-container lp-about__grid">
                <div class="lp-about__visual">
                    <div class="lp-about__emblem">
                        <svg viewBox="0 0 160 160" fill="none">
                            <circle cx="80" cy="80" r="75" fill="url(#bgCircle)" opacity="0.15"/>
                            <defs>
                                <radialGradient id="bgCircle" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#2eaa45"/>
                                    <stop offset="100%" stop-color="#0a3d6b"/>
                                </radialGradient>
                            </defs>
                            <!-- Sol grande -->
                            <circle cx="80" cy="55" r="22" fill="#F5C518"/>
                            <g stroke="#F5C518" stroke-width="3" stroke-linecap="round">
                                <line x1="80" y1="18" x2="80" y2="26"/>
                                <line x1="110" y1="28" x2="104" y2="34"/>
                                <line x1="118" y1="55" x2="110" y2="55"/>
                                <line x1="50"  y1="28" x2="56"  y2="34"/>
                                <line x1="42"  y1="55" x2="50"  y2="55"/>
                                <line x1="108" y1="80" x2="103" y2="76"/>
                                <line x1="52"  y1="80" x2="57"  y2="76"/>
                            </g>
                            <!-- Colinas -->
                            <ellipse cx="40"  cy="120" rx="55" ry="35" fill="#155824"/>
                            <ellipse cx="120" cy="126" rx="55" ry="32" fill="#1e7f33"/>
                            <ellipse cx="80"  cy="135" rx="85" ry="30" fill="#27a83e"/>
                            <!-- Texto -->
                            <text x="80" y="154" text-anchor="middle" font-family="serif" font-size="9" fill="#fff" opacity="0.7">HORIZONTE S.A.S</text>
                        </svg>
                    </div>
                    <div class="lp-about__badge-float">
                        <span class="lp-about__badge-n">+20</span>
                        <span class="lp-about__badge-l">años</span>
                    </div>
                </div>

                <div class="lp-about__body">
                    <span class="lp-label">¿Quiénes somos?</span>
                    <h2 class="lp-h2">Acompañándote con <em>amor</em> desde hace más de dos décadas</h2>
                    <p class="lp-p">
                        Horizonte S.A.S Pre Exequiales nació con una misión clara: brindar a las familias colombianas
                        un servicio funerario de alta calidad, accesible y lleno de calidez humana. Sabemos que los
                        momentos de pérdida son los más difíciles, y por eso estamos aquí para ayudarte a cada paso.
                    </p>
                    <p class="lp-p">
                        Con instalaciones modernas, personal especializado y planes de afiliación flexibles, te
                        garantizamos que tu familia tendrá la protección y el apoyo que merece.
                    </p>
                    <div class="lp-about__chips">
                        <span class="lp-chip lp-chip--blue">Servicio 24/7</span>
                        <span class="lp-chip lp-chip--green">Traslados nacionales</span>
                        <span class="lp-chip lp-chip--gold">Salas premium</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════ PLANES ════════════ -->
        <section class="lp-section lp-planes" id="planes">
            <div class="lp-container">
                <div class="lp-section-header">
                    <span class="lp-label">Nuestros planes</span>
                    <h2 class="lp-h2">Elige el plan que mejor<br>se adapte a tu familia</h2>
                    <p class="lp-p lp-p--center">Todos nuestros planes incluyen atención personalizada y acompañamiento profesional.</p>
                </div>

                <div class="lp-planes__grid">
                    <div
                        v-for="plan in planes"
                        :key="plan.name"
                        class="lp-plan"
                        :class="[plan.color, { 'lp-plan--star': plan.destacado }]"
                    >
                        <div v-if="plan.destacado" class="lp-plan__badge">⭐ Más popular</div>
                        <div class="lp-plan__header">
                            <h3 class="lp-plan__name">{{ plan.name }}</h3>
                            <div class="lp-plan__price">
                                <span class="lp-plan__amount">{{ plan.precio }}</span>
                                <span class="lp-plan__period">{{ plan.periodo }}</span>
                            </div>
                            <p class="lp-plan__desc">{{ plan.descripcion }}</p>
                        </div>
                        <ul class="lp-plan__list">
                            <li v-for="b in plan.beneficios" :key="b" class="lp-plan__item">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="lp-plan__check">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ b }}
                            </li>
                        </ul>
                        <Link :href="canRegister ? route('register') : route('login')" class="lp-plan__cta">
                            Quiero este plan
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════ VALORES ════════════ -->
        <section class="lp-section lp-valores" id="valores">
            <div class="lp-container">
                <div class="lp-section-header">
                    <span class="lp-label lp-label--light">Nuestros valores</span>
                    <h2 class="lp-h2 lp-h2--light">Lo que nos define</h2>
                </div>
                <div class="lp-valores__grid">
                    <div v-for="v in valores" :key="v.titulo" class="lp-valor">
                        <div class="lp-valor__icon">{{ v.icon }}</div>
                        <h3 class="lp-valor__title">{{ v.titulo }}</h3>
                        <p class="lp-valor__desc">{{ v.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════ CTA FINAL ════════════ -->
        <section class="lp-cta-final">
            <div class="lp-cta-final__bg">
                <div class="lp-cta-final__hill lp-cta-final__hill--1"></div>
                <div class="lp-cta-final__hill lp-cta-final__hill--2"></div>
            </div>
            <div class="lp-container lp-cta-final__content">
                <h2 class="lp-cta-final__h2">¿Listo para proteger<br>a tu familia hoy?</h2>
                <p class="lp-cta-final__p">Únete a más de 15.000 familias que ya confían en Horizonte S.A.S.</p>
                <div class="lp-cta-final__btns">
                    <Link v-if="canRegister" :href="route('register')" class="lp-btn lp-btn--white">
                        Afiliarme ahora
                    </Link>
                    <a href="#contacto" class="lp-btn lp-btn--outline-white">Más información</a>
                </div>
            </div>
        </section>

        <!-- ════════════ CONTACTO ════════════ -->
        <section class="lp-section lp-contacto" id="contacto">
            <div class="lp-container lp-contacto__grid">
                <div class="lp-contacto__info">
                    <span class="lp-label">Contáctanos</span>
                    <h2 class="lp-h2">Estamos aquí para ayudarte</h2>
                    <div class="lp-contacto__items">
                        <div class="lp-contacto__item">
                            <div class="lp-contacto__ico">📞</div>
                            <div>
                                <p class="lp-contacto__item-title">Teléfono</p>
                                <p class="lp-contacto__item-val">+57 (8) 000-0000</p>
                            </div>
                        </div>
                        <div class="lp-contacto__item">
                            <div class="lp-contacto__ico">📧</div>
                            <div>
                                <p class="lp-contacto__item-title">Correo</p>
                                <p class="lp-contacto__item-val">info@horizonte.com.co</p>
                            </div>
                        </div>
                        <div class="lp-contacto__item">
                            <div class="lp-contacto__ico">📍</div>
                            <div>
                                <p class="lp-contacto__item-title">Dirección</p>
                                <p class="lp-contacto__item-val">Neiva, Huila, Colombia</p>
                            </div>
                        </div>
                        <div class="lp-contacto__item">
                            <div class="lp-contacto__ico">🕐</div>
                            <div>
                                <p class="lp-contacto__item-title">Horario de atención</p>
                                <p class="lp-contacto__item-val">24 horas · 7 días a la semana</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lp-contacto__form">
                    <h3 class="lp-contacto__form-title">Envíanos un mensaje</h3>
                    <div class="lp-cform">
                        <div class="lp-cform__row">
                            <div class="lp-cform__field">
                                <label class="lp-cform__label">Nombre</label>
                                <input type="text" placeholder="Tu nombre completo" class="lp-cform__input"/>
                            </div>
                            <div class="lp-cform__field">
                                <label class="lp-cform__label">Teléfono</label>
                                <input type="tel" placeholder="Tu número de contacto" class="lp-cform__input"/>
                            </div>
                        </div>
                        <div class="lp-cform__field">
                            <label class="lp-cform__label">Correo electrónico</label>
                            <input type="email" placeholder="tu@correo.com" class="lp-cform__input"/>
                        </div>
                        <div class="lp-cform__field">
                            <label class="lp-cform__label">Mensaje</label>
                            <textarea rows="4" placeholder="¿En qué podemos ayudarte?" class="lp-cform__input lp-cform__textarea"></textarea>
                        </div>
                        <button class="lp-cform__btn">Enviar mensaje</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════ FOOTER ════════════ -->
        <footer class="lp-footer">
            <div class="lp-container lp-footer__inner">
                <div class="lp-footer__brand">
                    <svg viewBox="0 0 48 48" fill="none" class="lp-footer__svg">
                        <circle cx="24" cy="16" r="8" fill="#F5C518"/>
                        <g stroke="#F5C518" stroke-width="2" stroke-linecap="round">
                            <line x1="24" y1="2"  x2="24" y2="6"/>
                            <line x1="37" y1="7"  x2="34.5" y2="9.5"/>
                            <line x1="42" y1="16" x2="38" y2="16"/>
                            <line x1="11" y1="7"  x2="13.5" y2="9.5"/>
                            <line x1="6"  y1="16" x2="10" y2="16"/>
                        </g>
                        <ellipse cx="12" cy="36" rx="16" ry="9"  fill="#1a6b2a"/>
                        <ellipse cx="36" cy="38" rx="16" ry="8"  fill="#228b35"/>
                        <ellipse cx="24" cy="42" rx="25" ry="8"  fill="#2eaa45"/>
                    </svg>
                    <div>
                        <p class="lp-footer__name">HORIZONTE S.A.S</p>
                        <p class="lp-footer__sub">Pre Exequiales</p>
                    </div>
                </div>
                <p class="lp-footer__copy">
                    &copy; {{ new Date().getFullYear() }} Horizonte S.A.S Pre Exequiales · Todos los derechos reservados
                </p>
                <div class="lp-footer__links">
                    <Link v-if="canLogin" :href="route('login')" class="lp-footer__a">Iniciar sesión</Link>
                    <Link v-if="canRegister" :href="route('register')" class="lp-footer__a">Registrarse</Link>
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:   #0a3d6b;
    --blue:   #1565a8;
    --sky:    #2e8bc0;
    --green:  #2eaa45;
    --dkgreen:#1a6b2a;
    --gold:   #f5a623;
    --gold-lt:#ffe066;
    --font:   'DM Sans', sans-serif;
    --serif:  'Playfair Display', serif;
}

.lp {
    font-family: var(--font);
    color: #1e293b;
    overflow-x: hidden;
}

/* ── UTILIDADES ── */
.lp-container {
    max-width: 1140px;
    margin: 0 auto;
    padding: 0 24px;
}

.lp-section { padding: 90px 0; }

.lp-label {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--sky);
    margin-bottom: 12px;
}

.lp-label--light { color: rgba(255,255,255,0.6); }

.lp-h2 {
    font-family: var(--serif);
    font-size: clamp(1.6rem, 3.5vw, 2.4rem);
    color: var(--navy);
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 18px;
}

.lp-h2 em { font-style: italic; color: var(--green); }
.lp-h2--light { color: #fff; }

.lp-p { font-size: 0.96rem; color: #475569; line-height: 1.7; margin-bottom: 14px; }
.lp-p--center { text-align: center; max-width: 560px; margin: 0 auto 14px; }

.lp-section-header { text-align: center; margin-bottom: 56px; }

/* ── BOTONES ── */
.lp-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 24px;
    border-radius: 10px;
    font-family: var(--font);
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
    letter-spacing: 0.02em;
}

.lp-btn--solid {
    background: linear-gradient(135deg, var(--navy), var(--green));
    color: #fff;
    box-shadow: 0 4px 16px rgba(10,61,107,0.28);
}
.lp-btn--solid:hover { opacity: 0.9; transform: translateY(-1px); }

.lp-btn--ghost {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,0.4);
    backdrop-filter: blur(4px);
}
.lp-btn--ghost:hover { background: rgba(255,255,255,0.25); }

.lp-btn--hero-primary {
    background: #fff;
    color: var(--navy);
    font-weight: 700;
    box-shadow: 0 6px 24px rgba(0,0,0,0.18);
    padding: 13px 28px;
    font-size: 0.95rem;
}
.lp-btn--hero-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.25); }

.lp-btn--hero-ghost {
    color: rgba(255,255,255,0.9);
    border: 1.5px solid rgba(255,255,255,0.5);
    padding: 13px 28px;
    font-size: 0.95rem;
}
.lp-btn--hero-ghost:hover { background: rgba(255,255,255,0.12); }

.lp-btn--white {
    background: #fff;
    color: var(--navy);
    font-weight: 700;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 13px 28px;
}
.lp-btn--white:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(0,0,0,0.22); }

.lp-btn--outline-white {
    border: 1.5px solid rgba(255,255,255,0.6);
    color: #fff;
    padding: 13px 28px;
}
.lp-btn--outline-white:hover { background: rgba(255,255,255,0.12); }

/* ── NAVBAR ── */
.lp-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 999;
    background: rgba(10,61,107,0.97);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.lp-nav__inner {
    max-width: 1140px;
    margin: 0 auto;
    padding: 0 24px;
    height: 66px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.lp-nav__brand {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.lp-nav__svg { width: 42px; height: 42px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }

.lp-nav__text { display: flex; flex-direction: column; }

.lp-nav__name {
    font-family: var(--serif);
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.06em;
    line-height: 1;
}

.lp-nav__sub {
    font-size: 0.58rem;
    color: var(--gold-lt);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.lp-nav__links {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
}

.lp-nav__a {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 8px;
    transition: all 0.18s;
}

.lp-nav__a:hover { color: #fff; background: rgba(255,255,255,0.08); }

.lp-nav__cta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 12px;
}

.lp-nav__burger {
    display: none;
    background: none; border: none; cursor: pointer;
    color: rgba(255,255,255,0.8);
    padding: 6px; border-radius: 8px;
    margin-left: auto;
}

.lp-nav__burger svg { width: 22px; height: 22px; }

/* Mobile menu */
.lp-mobile-menu {
    display: none;
    flex-direction: column;
    padding: 12px 24px 20px;
    border-top: 1px solid rgba(255,255,255,0.08);
    gap: 4px;
}

.lp-mobile-menu--open { display: flex; }

.lp-mobile-menu__a {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 10px 12px;
    border-radius: 8px;
    transition: all 0.18s;
}

.lp-mobile-menu__a:hover { color: #fff; background: rgba(255,255,255,0.08); }

.lp-mobile-menu__cta {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    flex-wrap: wrap;
}

/* ── HERO ── */
.lp-hero {
    padding-top: 66px;
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.lp-hero__bg {
    position: absolute;
    inset: 0;
}

.lp-hero__sky {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, #062b4e 0%, #1262a8 38%, #2e8bc0 68%, #7ec8e3 100%);
}

.lp-hero__sun-wrap {
    position: absolute;
    top: 12%;
    left: 50%;
    transform: translateX(-50%);
    animation: sunRise 1.2s ease-out forwards;
}

@keyframes sunRise {
    from { transform: translateX(-50%) translateY(50px); opacity: 0; }
    to   { transform: translateX(-50%) translateY(0);    opacity: 1; }
}

.lp-hero__sun {
    width: 140px; height: 140px;
    border-radius: 50%;
    background: radial-gradient(circle at 38% 38%, #ffe45a, #f59819);
    box-shadow: 0 0 80px 36px rgba(245,152,25,0.5), 0 0 160px 80px rgba(245,152,25,0.2);
    position: relative;
    animation: sunGlow 5s ease-in-out infinite alternate;
}

@keyframes sunGlow {
    from { box-shadow: 0 0 80px 36px rgba(245,152,25,0.5), 0 0 160px 80px rgba(245,152,25,0.2); }
    to   { box-shadow: 0 0 110px 50px rgba(245,152,25,0.65), 0 0 200px 100px rgba(245,152,25,0.3); }
}

.lp-hero__rays {
    position: absolute; inset: -55px; border-radius: 50%;
    background: conic-gradient(
        from 0deg,
        transparent 0deg,   rgba(255,220,70,0.22) 5deg,  transparent 10deg,
        transparent 24deg,  rgba(255,220,70,0.22) 29deg, transparent 34deg,
        transparent 48deg,  rgba(255,220,70,0.22) 53deg, transparent 58deg,
        transparent 72deg,  rgba(255,220,70,0.22) 77deg, transparent 82deg,
        transparent 96deg,  rgba(255,220,70,0.22) 101deg,transparent 106deg,
        transparent 120deg, rgba(255,220,70,0.22) 125deg,transparent 130deg,
        transparent 144deg, rgba(255,220,70,0.22) 149deg,transparent 154deg,
        transparent 168deg, rgba(255,220,70,0.22) 173deg,transparent 178deg,
        transparent 192deg, rgba(255,220,70,0.22) 197deg,transparent 202deg,
        transparent 216deg, rgba(255,220,70,0.22) 221deg,transparent 226deg,
        transparent 240deg, rgba(255,220,70,0.22) 245deg,transparent 250deg,
        transparent 264deg, rgba(255,220,70,0.22) 269deg,transparent 274deg,
        transparent 288deg, rgba(255,220,70,0.22) 293deg,transparent 298deg,
        transparent 312deg, rgba(255,220,70,0.22) 317deg,transparent 322deg,
        transparent 336deg, rgba(255,220,70,0.22) 341deg,transparent 346deg,
        transparent 360deg
    );
    animation: rotateSun 24s linear infinite;
}

@keyframes rotateSun { to { transform: rotate(360deg); } }

.lp-hero__hills { position: absolute; bottom: 0; left: 0; right: 0; height: 45%; }

.lp-hero__hill { position: absolute; bottom: 0; border-radius: 50% 50% 0 0; }
.lp-hero__hill--1 { left: -10%; right: -10%; height: 55%; background: linear-gradient(180deg, #0f4a1e, #0a3014); }
.lp-hero__hill--2 { left: -6%;  right: 28%;  height: 72%; background: linear-gradient(180deg, #155824, #0f4a1e); }
.lp-hero__hill--3 { left: 24%;  right: -8%;  height: 65%; background: linear-gradient(180deg, #1e7f33, #155824); }

.lp-hero__content {
    position: relative;
    z-index: 10;
    max-width: 1140px;
    margin: 0 auto;
    padding: 100px 24px 180px;
    text-align: center;
    animation: fadeUp 0.8s ease-out 0.3s both;
}

@keyframes fadeUp {
    from { transform: translateY(28px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.lp-hero__badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: var(--gold-lt);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    padding: 6px 16px;
    border-radius: 99px;
    margin-bottom: 22px;
    backdrop-filter: blur(4px);
}

.lp-hero__h1 {
    font-family: var(--serif);
    font-size: clamp(2rem, 6vw, 3.8rem);
    color: #fff;
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 22px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.3);
}

.lp-hero__h1--accent { color: var(--gold-lt); }

.lp-hero__p {
    font-size: clamp(0.95rem, 2.5vw, 1.1rem);
    color: rgba(255,255,255,0.82);
    max-width: 600px;
    margin: 0 auto 36px;
    line-height: 1.7;
}

.lp-hero__btns {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 52px;
}

.lp-hero__stats {
    display: inline-flex;
    align-items: center;
    gap: 20px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 16px;
    padding: 16px 28px;
    backdrop-filter: blur(8px);
    flex-wrap: wrap;
    justify-content: center;
}

.lp-hero__stat { text-align: center; }

.lp-hero__stat-n {
    display: block;
    font-family: var(--serif);
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--gold-lt);
}

.lp-hero__stat-l {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.65);
}

.lp-hero__stat-sep {
    width: 1px; height: 36px;
    background: rgba(255,255,255,0.2);
}

/* ── NOSOTROS ── */
.lp-about { background: #fff; }

.lp-about__grid {
    display: grid;
    grid-template-columns: 1fr 1.4fr;
    gap: 64px;
    align-items: center;
}

.lp-about__visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lp-about__emblem svg {
    width: 260px; height: 260px;
    filter: drop-shadow(0 8px 30px rgba(10,61,107,0.2));
}

.lp-about__badge-float {
    position: absolute;
    bottom: -10px; right: 10px;
    background: linear-gradient(135deg, var(--navy), var(--blue));
    border-radius: 50%;
    width: 80px; height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 24px rgba(10,61,107,0.35);
}

.lp-about__badge-n {
    font-family: var(--serif);
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--gold-lt);
    line-height: 1;
}

.lp-about__badge-l {
    font-size: 0.6rem;
    color: rgba(255,255,255,0.65);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.lp-about__chips {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.lp-chip {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 99px;
    font-size: 0.78rem;
    font-weight: 600;
}

.lp-chip--blue  { background: #dbeafe; color: #1e40af; }
.lp-chip--green { background: #dcfce7; color: #166534; }
.lp-chip--gold  { background: #fef3c7; color: #92400e; }

/* ── PLANES ── */
.lp-planes { background: #f0f4f8; }

.lp-planes__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}

.lp-plan {
    background: #fff;
    border-radius: 20px;
    padding: 32px 26px 26px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    position: relative;
    border: 2px solid transparent;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    transition: transform 0.2s, box-shadow 0.2s;
}

.lp-plan:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.12);
}

.lp-plan--star {
    border-color: var(--green);
    box-shadow: 0 8px 36px rgba(46,170,69,0.2);
}

.lp-plan__badge {
    position: absolute;
    top: -14px; left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, var(--dkgreen), var(--green));
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 5px 16px;
    border-radius: 99px;
    white-space: nowrap;
    box-shadow: 0 2px 10px rgba(46,170,69,0.35);
}

.lp-plan__header { text-align: center; }

.lp-plan__name {
    font-family: var(--serif);
    font-size: 1.15rem;
    color: var(--navy);
    font-weight: 700;
    margin-bottom: 10px;
}

.lp-plan__price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 4px;
    margin-bottom: 8px;
}

.lp-plan__amount {
    font-family: var(--serif);
    font-size: 2.2rem;
    font-weight: 700;
}

.lp-plan--sky   .lp-plan__amount { color: var(--blue); }
.lp-plan--green .lp-plan__amount { color: var(--green); }
.lp-plan--gold  .lp-plan__amount { color: #b45309; }

.lp-plan__period { font-size: 0.82rem; color: #64748b; }

.lp-plan__desc { font-size: 0.82rem; color: #64748b; }

.lp-plan__list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}

.lp-plan__item {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    font-size: 0.87rem;
    color: #334155;
    line-height: 1.4;
}

.lp-plan__check { width: 17px; height: 17px; flex-shrink: 0; margin-top: 1px; }

.lp-plan--sky   .lp-plan__check { color: var(--blue); }
.lp-plan--green .lp-plan__check { color: var(--green); }
.lp-plan--gold  .lp-plan__check { color: #d97706; }

.lp-plan__cta {
    display: block;
    text-align: center;
    padding: 12px;
    border-radius: 11px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 700;
    transition: all 0.2s;
    font-family: var(--font);
}

.lp-plan--sky   .lp-plan__cta { background: linear-gradient(135deg, var(--navy), var(--blue)); color: #fff; }
.lp-plan--green .lp-plan__cta { background: linear-gradient(135deg, var(--dkgreen), var(--green)); color: #fff; }
.lp-plan--gold  .lp-plan__cta { background: linear-gradient(135deg, #92400e, #d97706); color: #fff; }

.lp-plan__cta:hover { opacity: 0.88; transform: translateY(-1px); }

/* ── VALORES ── */
.lp-valores {
    background: linear-gradient(135deg, var(--navy) 0%, #0d5c28 100%);
    position: relative;
    overflow: hidden;
}

.lp-valores::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.lp-valores__grid {
    position: relative;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.lp-valor {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 18px;
    padding: 28px 22px;
    text-align: center;
    transition: transform 0.2s, background 0.2s;
}

.lp-valor:hover {
    transform: translateY(-4px);
    background: rgba(255,255,255,0.1);
}

.lp-valor__icon {
    font-size: 2.2rem;
    margin-bottom: 14px;
}

.lp-valor__title {
    font-family: var(--serif);
    font-size: 1.1rem;
    color: var(--gold-lt);
    font-weight: 600;
    margin-bottom: 10px;
}

.lp-valor__desc {
    font-size: 0.84rem;
    color: rgba(255,255,255,0.65);
    line-height: 1.6;
}

/* ── CTA FINAL ── */
.lp-cta-final {
    position: relative;
    background: linear-gradient(135deg, var(--dkgreen) 0%, var(--blue) 100%);
    overflow: hidden;
    padding: 80px 0;
    text-align: center;
}

.lp-cta-final__bg {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 60%;
    pointer-events: none;
}

.lp-cta-final__hill {
    position: absolute;
    bottom: 0;
    border-radius: 50% 50% 0 0;
}

.lp-cta-final__hill--1 { left: -5%; right: 40%; height: 100%; background: rgba(0,0,0,0.1); }
.lp-cta-final__hill--2 { left: 35%; right: -5%; height: 80%;  background: rgba(0,0,0,0.07); }

.lp-cta-final__content { position: relative; z-index: 2; }

.lp-cta-final__h2 {
    font-family: var(--serif);
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    color: #fff;
    font-weight: 700;
    margin-bottom: 18px;
    line-height: 1.2;
}

.lp-cta-final__p {
    font-size: 1rem;
    color: rgba(255,255,255,0.78);
    margin-bottom: 36px;
}

.lp-cta-final__btns {
    display: flex;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
}

/* ── CONTACTO ── */
.lp-contacto { background: #fff; }

.lp-contacto__grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 60px;
    align-items: start;
}

.lp-contacto__items { margin-top: 28px; display: flex; flex-direction: column; gap: 20px; }

.lp-contacto__item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.lp-contacto__ico {
    width: 42px; height: 42px;
    border-radius: 12px;
    background: #f0f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.lp-contacto__item-title { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; }
.lp-contacto__item-val   { font-size: 0.9rem; color: #1e293b; font-weight: 500; }

.lp-contacto__form { background: #f8fafc; border-radius: 18px; padding: 32px 28px; border: 1.5px solid #e2e8f0; }

.lp-contacto__form-title {
    font-family: var(--serif);
    font-size: 1.2rem;
    color: var(--navy);
    font-weight: 700;
    margin-bottom: 22px;
}

.lp-cform { display: flex; flex-direction: column; gap: 14px; }
.lp-cform__row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.lp-cform__field { display: flex; flex-direction: column; gap: 6px; }
.lp-cform__label { font-size: 0.78rem; font-weight: 600; color: #334155; }

.lp-cform__input {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.87rem;
    font-family: var(--font);
    color: #1e293b;
    background: #fff;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    resize: none;
}

.lp-cform__input:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(21,101,168,0.12);
}

.lp-cform__input::placeholder { color: #cbd5e1; }
.lp-cform__textarea { resize: vertical; }

.lp-cform__btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 11px;
    background: linear-gradient(135deg, var(--navy), var(--green));
    color: #fff;
    font-family: var(--font);
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    box-shadow: 0 4px 18px rgba(10,61,107,0.28);
}

.lp-cform__btn:hover { opacity: 0.9; transform: translateY(-1px); }

/* ── FOOTER ── */
.lp-footer {
    background: var(--navy-dk, #071e38);
    border-top: 1px solid rgba(255,255,255,0.06);
    padding: 28px 0;
}

.lp-footer__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.lp-footer__brand { display: flex; align-items: center; gap: 10px; }

.lp-footer__svg { width: 36px; height: 36px; }

.lp-footer__name {
    font-family: var(--serif);
    font-size: 0.9rem;
    color: #fff;
    font-weight: 700;
    letter-spacing: 0.05em;
    line-height: 1;
}

.lp-footer__sub {
    font-size: 0.6rem;
    color: var(--gold-lt);
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.lp-footer__copy { font-size: 0.75rem; color: rgba(255,255,255,0.38); }

.lp-footer__links { display: flex; gap: 16px; }

.lp-footer__a {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    transition: color 0.18s;
}

.lp-footer__a:hover { color: var(--gold-lt); }

/* ── RESPONSIVE ── */
@media (max-width: 1024px) {
    .lp-planes__grid   { grid-template-columns: 1fr 1fr; }
    .lp-valores__grid  { grid-template-columns: repeat(2, 1fr); }
    .lp-about__grid    { grid-template-columns: 1fr; gap: 36px; }
    .lp-about__visual  { order: -1; }
    .lp-contacto__grid { grid-template-columns: 1fr; gap: 40px; }
}

@media (max-width: 768px) {
    .lp-nav__links, .lp-nav__cta { display: none; }
    .lp-nav__burger { display: flex; }
    .lp-section { padding: 64px 0; }
    .lp-planes__grid { grid-template-columns: 1fr; max-width: 380px; margin: 0 auto; }
    .lp-valores__grid { grid-template-columns: 1fr 1fr; }
    .lp-hero__stats { flex-direction: column; gap: 14px; }
    .lp-hero__stat-sep { width: 60px; height: 1px; }
    .lp-footer__inner { flex-direction: column; text-align: center; }
    .lp-cform__row { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
    .lp-valores__grid { grid-template-columns: 1fr; }
    .lp-hero__content { padding: 80px 16px 140px; }
}
</style>