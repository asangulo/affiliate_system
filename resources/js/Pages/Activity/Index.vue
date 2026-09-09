<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    logs:    Object,
    filters: Object,
    users:   Array,
    events:  Object,
    types:   Object,
    stats:   Object,
});

const search      = ref(props.filters.search ?? '');
const event       = ref(props.filters.event ?? '');
const userId      = ref(props.filters.user_id ?? '');
const subjectType = ref(props.filters.subject_type ?? '');
const from        = ref(props.filters.from ?? '');
const to          = ref(props.filters.to ?? '');

const abierto = ref(null);

let timer = null;
watch([search, event, userId, subjectType, from, to], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('activity.index'), {
            search: search.value,
            event: event.value,
            user_id: userId.value,
            subject_type: subjectType.value,
            from: from.value,
            to: to.value,
        }, { preserveState: true, replace: true });
    }, 350);
});

const limpiar = () => {
    search.value = ''; event.value = ''; userId.value = '';
    subjectType.value = ''; from.value = ''; to.value = '';
};

const toggle = (id) => {
    abierto.value = abierto.value === id ? null : id;
};

const cuando = (iso) => new Date(iso).toLocaleString('es-CO', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
});

const inicial = (nombre) => (nombre ?? '?').charAt(0).toUpperCase();
</script>

<template>
    <Head title="Historial" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="ac-head">Historial de cambios</h2>
        </template>

        <div class="ac-page">

            <!-- Cifras -->
            <div class="ac-stats">
                <div class="ac-stat">
                    <span class="ac-stat__num">{{ stats.today }}</span>
                    <span class="ac-stat__label">Movimientos hoy</span>
                </div>
                <div class="ac-stat">
                    <span class="ac-stat__num">{{ stats.week }}</span>
                    <span class="ac-stat__label">Últimos 7 días</span>
                </div>
                <div class="ac-stat">
                    <span class="ac-stat__num">{{ stats.total }}</span>
                    <span class="ac-stat__label">Total registrado</span>
                </div>
            </div>

            <!-- Filtros -->
            <div class="ac-filters">
                <input v-model="search" type="search" class="ac-input ac-input--grow"
                       placeholder="Buscar por descripción, registro o usuario" />
                <select v-model="event" class="ac-input">
                    <option value="">Toda acción</option>
                    <option v-for="(label, key) in events" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="subjectType" class="ac-input">
                    <option value="">Todo módulo</option>
                    <option v-for="(label, key) in types" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="userId" class="ac-input">
                    <option value="">Todo usuario</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
                <input v-model="from" type="date" class="ac-input" title="Desde" />
                <input v-model="to" type="date" class="ac-input" title="Hasta" />
                <button class="ac-clear" @click="limpiar">Limpiar</button>
            </div>

            <!-- Línea de tiempo -->
            <div v-if="logs.data.length" class="ac-timeline">
                <article
                    v-for="log in logs.data"
                    :key="log.id"
                    class="ac-entry"
                    :class="`ac-entry--${log.event}`"
                >
                    <div class="ac-entry__avatar">{{ inicial(log.user_name) }}</div>

                    <div class="ac-entry__main">
                        <div class="ac-entry__top">
                            <p class="ac-entry__desc">{{ log.description }}</p>
                            <time class="ac-entry__time" :title="cuando(log.created_at)">
                                {{ log.created_human }}
                            </time>
                        </div>

                        <div class="ac-entry__meta">
                            <span class="ac-tag" :class="`ac-tag--${log.event}`">
                                {{ log.event_label }}
                            </span>
                            <span class="ac-meta-item">{{ log.subject_name }}</span>
                            <span class="ac-meta-item">{{ log.user_name }}</span>
                            <span v-if="log.ip_address" class="ac-meta-item ac-meta-item--dim">
                                {{ log.ip_address }}
                            </span>
                            <button
                                v-if="log.changes.length"
                                class="ac-toggle"
                                @click="toggle(log.id)"
                            >
                                {{ abierto === log.id ? 'Ocultar' : `Ver ${log.changes.length} ${log.changes.length === 1 ? 'cambio' : 'cambios'}` }}
                            </button>
                        </div>

                        <table v-if="abierto === log.id && log.changes.length" class="ac-changes">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Antes</th>
                                    <th>Después</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(c, i) in log.changes" :key="i">
                                    <td class="ac-changes__field">{{ c.field }}</td>
                                    <td class="ac-changes__from">{{ c.from }}</td>
                                    <td class="ac-changes__to">{{ c.to }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div v-else class="ac-empty">
                <p class="ac-empty__title">No hay movimientos que mostrar</p>
                <p class="ac-empty__text">
                    Cuando alguien cree, edite o elimine información, quedará registrado aquí.
                </p>
            </div>

            <!-- Paginación -->
            <nav v-if="logs.last_page > 1" class="ac-pager">
                <Link
                    v-for="link in logs.links"
                    :key="link.label"
                    :href="link.url ?? ''"
                    class="ac-pager__item"
                    :class="{
                        'ac-pager__item--on': link.active,
                        'ac-pager__item--off': !link.url,
                    }"
                    v-html="link.label"
                />
            </nav>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.ac-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.ac-page { max-width: 980px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 20px; }

.ac-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.ac-stat { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #0a3d6b; border-radius: 14px; padding: 15px 20px; }
.ac-stat__num { display: block; font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
.ac-stat__label { font-size: .77rem; color: #64748b; }

.ac-filters { display: flex; gap: 9px; flex-wrap: wrap; align-items: center; }
.ac-input { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .83rem; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; }
.ac-input:focus { border-color: #1565a8; background: #fff; box-shadow: 0 0 0 3px rgba(21,101,168,.1); }
.ac-input--grow { flex: 1; min-width: 200px; }
.ac-clear { background: none; border: none; font-family: inherit; font-size: .82rem; font-weight: 600; color: #64748b; cursor: pointer; padding: 8px 4px; }
.ac-clear:hover { color: #0a3d6b; text-decoration: underline; }

/* Timeline */
.ac-timeline { display: flex; flex-direction: column; gap: 2px; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.ac-entry { display: flex; gap: 14px; padding: 16px 20px; border-bottom: 1px solid #f1f5f9; border-left: 3px solid transparent; }
.ac-entry:last-child { border-bottom: none; }
.ac-entry--created { border-left-color: #2eaa45; }
.ac-entry--updated { border-left-color: #1565a8; }
.ac-entry--deleted { border-left-color: #dc2626; }

.ac-entry__avatar { width: 34px; height: 34px; border-radius: 50%; background: #0a3d6b; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .88rem; flex-shrink: 0; }
.ac-entry__main { flex: 1; min-width: 0; }
.ac-entry__top { display: flex; align-items: baseline; justify-content: space-between; gap: 14px; }
.ac-entry__desc { font-size: .89rem; color: #0f172a; font-weight: 500; }
.ac-entry__time { font-size: .74rem; color: #94a3b8; white-space: nowrap; }

.ac-entry__meta { display: flex; align-items: center; gap: 10px; margin-top: 7px; flex-wrap: wrap; }
.ac-tag { padding: 2px 9px; border-radius: 99px; font-size: .7rem; font-weight: 700; }
.ac-tag--created { background: #dcfce7; color: #166534; }
.ac-tag--updated { background: #dbeafe; color: #1e40af; }
.ac-tag--deleted { background: #fee2e2; color: #b91c1c; }
.ac-tag--import  { background: #fef3c7; color: #92400e; }

.ac-meta-item { font-size: .75rem; color: #64748b; }
.ac-meta-item--dim { color: #cbd5e1; }
.ac-toggle { background: none; border: none; font-family: inherit; font-size: .76rem; font-weight: 600; color: #1565a8; cursor: pointer; padding: 0; }
.ac-toggle:hover { text-decoration: underline; }

.ac-changes { width: 100%; margin-top: 11px; border-collapse: collapse; font-size: .8rem; background: #f8fafc; border-radius: 9px; overflow: hidden; }
.ac-changes th { text-align: left; padding: 7px 12px; font-size: .7rem; color: #94a3b8; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
.ac-changes td { padding: 7px 12px; border-bottom: 1px solid #eef2f7; }
.ac-changes tr:last-child td { border-bottom: none; }
.ac-changes__field { font-weight: 600; color: #475569; }
.ac-changes__from { color: #94a3b8; text-decoration: line-through; }
.ac-changes__to { color: #166534; font-weight: 500; }

.ac-empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px 24px; text-align: center; }
.ac-empty__title { font-size: 1rem; font-weight: 700; color: #334155; }
.ac-empty__text { font-size: .85rem; color: #64748b; margin-top: 5px; }

.ac-pager { display: flex; gap: 5px; justify-content: center; flex-wrap: wrap; }
.ac-pager__item { padding: 7px 12px; border-radius: 8px; font-size: .82rem; color: #475569; background: #fff; border: 1px solid #e2e8f0; text-decoration: none; }
.ac-pager__item--on { background: #0a3d6b; color: #fff; border-color: #0a3d6b; }
.ac-pager__item--off { opacity: .4; pointer-events: none; }

@media (max-width: 720px) {
    .ac-stats { grid-template-columns: 1fr; }
    .ac-entry__top { flex-direction: column; gap: 3px; }
}
</style>
