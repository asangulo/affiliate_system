<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    branches: Object,
    filters:  Object,
    stats:    Object,
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

let timer = null;
watch([search, status], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('branches.index'),
            { search: search.value, status: status.value },
            { preserveState: true, replace: true });
    }, 350);
});

const toggle = (branch) => {
    router.patch(route('branches.toggle', branch.id), {}, { preserveScroll: true });
};

const destroy = (branch) => {
    if (!confirm(`¿Eliminar la sede "${branch.name}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route('branches.destroy', branch.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Sedes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="hz-head">Sedes</h2>
        </template>

        <div class="hz-page">

            <!-- Resumen -->
            <div class="hz-stats">
                <div class="hz-stat">
                    <span class="hz-stat__num">{{ stats.total }}</span>
                    <span class="hz-stat__label">Sedes registradas</span>
                </div>
                <div class="hz-stat hz-stat--ok">
                    <span class="hz-stat__num">{{ stats.active }}</span>
                    <span class="hz-stat__label">En operación</span>
                </div>
                <div class="hz-stat hz-stat--off">
                    <span class="hz-stat__num">{{ stats.inactive }}</span>
                    <span class="hz-stat__label">Cerradas</span>
                </div>
            </div>

            <!-- Barra de acciones -->
            <div class="hz-bar">
                <div class="hz-bar__filters">
                    <input
                        v-model="search"
                        type="search"
                        class="hz-input"
                        placeholder="Buscar por nombre, ciudad o encargado"
                    />
                    <select v-model="status" class="hz-input hz-input--select">
                        <option value="">Todas</option>
                        <option value="active">En operación</option>
                        <option value="inactive">Cerradas</option>
                    </select>
                </div>
                <Link :href="route('branches.create')" class="hz-btn hz-btn--primary">
                    Crear sede
                </Link>
            </div>

            <!-- Listado -->
            <div v-if="branches.data.length" class="hz-grid">
                <article
                    v-for="branch in branches.data"
                    :key="branch.id"
                    class="hz-card"
                    :class="{ 'hz-card--off': !branch.is_active }"
                >
                    <header class="hz-card__head">
                        <div>
                            <h3 class="hz-card__title">{{ branch.name }}</h3>
                            <p v-if="branch.code" class="hz-card__code">{{ branch.code }}</p>
                        </div>
                        <span
                            class="hz-pill"
                            :class="branch.is_active ? 'hz-pill--ok' : 'hz-pill--off'"
                        >
                            {{ branch.is_active ? 'Operando' : 'Cerrada' }}
                        </span>
                    </header>

                    <dl class="hz-card__body">
                        <div v-if="branch.city">
                            <dt>Ubicación</dt>
                            <dd>{{ branch.city }}<span v-if="branch.department">, {{ branch.department }}</span></dd>
                        </div>
                        <div v-if="branch.address">
                            <dt>Dirección</dt>
                            <dd>{{ branch.address }}</dd>
                        </div>
                        <div v-if="branch.manager_name">
                            <dt>Encargado</dt>
                            <dd>{{ branch.manager_name }}</dd>
                        </div>
                        <div v-if="branch.phone">
                            <dt>Teléfono</dt>
                            <dd>{{ branch.phone }}</dd>
                        </div>
                    </dl>

                    <footer class="hz-card__foot">
                        <span class="hz-count">
                            <strong>{{ branch.affiliates_count }}</strong>
                            {{ branch.affiliates_count === 1 ? 'afiliado' : 'afiliados' }}
                        </span>
                        <div class="hz-card__actions">
                            <button class="hz-link" @click="toggle(branch)">
                                {{ branch.is_active ? 'Cerrar' : 'Reabrir' }}
                            </button>
                            <Link :href="route('branches.edit', branch.id)" class="hz-link">
                                Editar
                            </Link>
                            <button
                                v-if="branch.affiliates_count === 0"
                                class="hz-link hz-link--danger"
                                @click="destroy(branch)"
                            >
                                Eliminar
                            </button>
                        </div>
                    </footer>
                </article>
            </div>

            <!-- Vacío -->
            <div v-else class="hz-empty">
                <p class="hz-empty__title">No hay sedes que coincidan</p>
                <p class="hz-empty__text">
                    Ajusta la búsqueda o crea la primera sede para empezar a asignarle afiliados.
                </p>
                <Link :href="route('branches.create')" class="hz-btn hz-btn--primary">
                    Crear sede
                </Link>
            </div>

            <!-- Paginación -->
            <nav v-if="branches.last_page > 1" class="hz-pager">
                <Link
                    v-for="link in branches.links"
                    :key="link.label"
                    :href="link.url ?? ''"
                    class="hz-pager__item"
                    :class="{
                        'hz-pager__item--on': link.active,
                        'hz-pager__item--off': !link.url,
                    }"
                    v-html="link.label"
                />
            </nav>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.hz-page   { max-width: 1180px; margin: 0 auto; padding: 24px 0 48px; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 22px; }
.hz-head   { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; }

/* Resumen */
.hz-stats  { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.hz-stat   { background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #0a3d6b; border-radius: 14px; padding: 16px 20px; display: flex; flex-direction: column; gap: 2px; }
.hz-stat--ok  { border-left-color: #2eaa45; }
.hz-stat--off { border-left-color: #94a3b8; }
.hz-stat__num   { font-size: 1.7rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
.hz-stat__label { font-size: 0.78rem; color: #64748b; }

/* Barra */
.hz-bar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.hz-bar__filters { display: flex; gap: 10px; flex: 1; min-width: 260px; }
.hz-input { padding: 9px 13px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: 0.88rem; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; transition: border-color .2s, box-shadow .2s; }
.hz-input:focus { border-color: #1565a8; background: #fff; box-shadow: 0 0 0 3px rgba(21,101,168,.1); }
.hz-bar__filters .hz-input:first-child { flex: 1; }
.hz-input--select { cursor: pointer; }

.hz-btn { display: inline-flex; align-items: center; padding: 10px 20px; border-radius: 10px; font-size: .88rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; font-family: inherit; transition: opacity .2s, transform .2s; }
.hz-btn--primary { background: linear-gradient(135deg,#0a3d6b,#1565a8); color: #fff; box-shadow: 0 4px 16px rgba(10,61,107,.25); }
.hz-btn--primary:hover { opacity: .92; transform: translateY(-1px); }

/* Tarjetas */
.hz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.hz-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; }
.hz-card--off { background: #fafafa; }
.hz-card--off .hz-card__title { color: #64748b; }

.hz-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; padding: 16px 18px 12px; border-bottom: 1px solid #f1f5f9; }
.hz-card__title { font-size: 1rem; font-weight: 700; color: #0a3d6b; }
.hz-card__code  { font-size: .74rem; color: #94a3b8; margin-top: 2px; }

.hz-pill { padding: 3px 10px; border-radius: 99px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
.hz-pill--ok  { background: #dcfce7; color: #166534; }
.hz-pill--off { background: #f1f5f9; color: #64748b; }

.hz-card__body { padding: 14px 18px; display: flex; flex-direction: column; gap: 9px; flex: 1; }
.hz-card__body dt { font-size: .7rem; color: #94a3b8; font-weight: 500; }
.hz-card__body dd { font-size: .85rem; color: #334155; margin-top: 1px; }

.hz-card__foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 18px; background: #f8fafc; border-top: 1px solid #f1f5f9; flex-wrap: wrap; }
.hz-count { font-size: .8rem; color: #64748b; }
.hz-count strong { color: #0a3d6b; font-size: .95rem; }
.hz-card__actions { display: flex; gap: 12px; }

.hz-link { background: none; border: none; padding: 0; font-family: inherit; font-size: .82rem; font-weight: 600; color: #1565a8; cursor: pointer; text-decoration: none; }
.hz-link:hover { text-decoration: underline; }
.hz-link--danger { color: #dc2626; }

/* Vacío */
.hz-empty { background: #fff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 48px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.hz-empty__title { font-size: 1rem; font-weight: 700; color: #334155; }
.hz-empty__text  { font-size: .85rem; color: #64748b; max-width: 380px; margin-bottom: 8px; }

/* Paginación */
.hz-pager { display: flex; gap: 5px; justify-content: center; flex-wrap: wrap; }
.hz-pager__item { padding: 7px 12px; border-radius: 8px; font-size: .82rem; color: #475569; background: #fff; border: 1px solid #e2e8f0; text-decoration: none; }
.hz-pager__item--on  { background: #0a3d6b; color: #fff; border-color: #0a3d6b; }
.hz-pager__item--off { opacity: .4; pointer-events: none; }

@media (max-width: 720px) {
    .hz-stats { grid-template-columns: 1fr; }
    .hz-bar__filters { flex-direction: column; }
    .hz-btn { width: 100%; justify-content: center; }
}
</style>
