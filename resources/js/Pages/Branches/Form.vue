<script setup>
import InputError from '@/Components/InputError.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    form:        Object,
    submitLabel: { type: String, default: 'Guardar' },
    affiliatesCount: { type: Number, default: null },
});

const emit = defineEmits(['submit']);
</script>

<template>
    <form class="bf" @submit.prevent="emit('submit')">

        <section class="bf-block">
            <header class="bf-block__head">
                <h3>Identificación</h3>
                <p>Cómo se reconoce la sede dentro del sistema</p>
            </header>
            <div class="bf-grid">
                <div class="bf-field bf-field--wide">
                    <label>Nombre de la sede <span class="bf-req">*</span></label>
                    <input v-model="form.name" type="text" class="bf-input" placeholder="Granada" required />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="bf-field">
                    <label>Código interno</label>
                    <input v-model="form.code" type="text" class="bf-input" placeholder="GRA-01" />
                    <InputError :message="form.errors.code" />
                </div>
                <div class="bf-field">
                    <label>Fecha de apertura</label>
                    <input v-model="form.opened_at" type="date" class="bf-input" />
                    <InputError :message="form.errors.opened_at" />
                </div>
            </div>
        </section>

        <section class="bf-block">
            <header class="bf-block__head">
                <h3>Ubicación</h3>
                <p>Dónde queda y cómo llegar</p>
            </header>
            <div class="bf-grid">
                <div class="bf-field">
                    <label>Ciudad o municipio</label>
                    <input v-model="form.city" type="text" class="bf-input" placeholder="Granada" />
                    <InputError :message="form.errors.city" />
                </div>
                <div class="bf-field">
                    <label>Departamento</label>
                    <input v-model="form.department" type="text" class="bf-input" placeholder="Cundinamarca" />
                    <InputError :message="form.errors.department" />
                </div>
                <div class="bf-field bf-field--wide">
                    <label>Dirección</label>
                    <input v-model="form.address" type="text" class="bf-input" placeholder="Calle 9 # 12-10" />
                    <InputError :message="form.errors.address" />
                </div>
            </div>
        </section>

        <section class="bf-block">
            <header class="bf-block__head">
                <h3>Contacto</h3>
                <p>A quién buscar y por dónde</p>
            </header>
            <div class="bf-grid">
                <div class="bf-field">
                    <label>Encargado</label>
                    <input v-model="form.manager_name" type="text" class="bf-input" placeholder="Nombre del responsable" />
                    <InputError :message="form.errors.manager_name" />
                </div>
                <div class="bf-field">
                    <label>Teléfono</label>
                    <input v-model="form.phone" type="text" class="bf-input" placeholder="320 000 0000" />
                    <InputError :message="form.errors.phone" />
                </div>
                <div class="bf-field bf-field--wide">
                    <label>Correo</label>
                    <input v-model="form.email" type="email" class="bf-input" placeholder="sede@horizonte.com" />
                    <InputError :message="form.errors.email" />
                </div>
            </div>
        </section>

        <section class="bf-block">
            <header class="bf-block__head">
                <h3>Estado y notas</h3>
                <p>Una sede cerrada deja de aparecer al crear afiliados</p>
            </header>
            <div class="bf-grid">
                <div class="bf-field bf-field--wide">
                    <label class="bf-check">
                        <input v-model="form.is_active" type="checkbox" />
                        <span>Esta sede está en operación</span>
                    </label>
                    <p v-if="affiliatesCount !== null && !form.is_active && affiliatesCount > 0" class="bf-warn">
                        Tiene {{ affiliatesCount }} afiliados asignados. Cerrarla no los elimina,
                        pero la sede dejará de ofrecerse en formularios nuevos.
                    </p>
                </div>
                <div class="bf-field bf-field--wide">
                    <label>Notas internas</label>
                    <textarea v-model="form.notes" rows="3" class="bf-input" placeholder="Horarios, referencias, observaciones..."></textarea>
                    <InputError :message="form.errors.notes" />
                </div>
            </div>
        </section>

        <div class="bf-actions">
            <Link :href="route('branches.index')" class="bf-btn bf-btn--cancel">Cancelar</Link>
            <button type="submit" class="bf-btn bf-btn--save" :disabled="form.processing">
                {{ form.processing ? 'Guardando…' : submitLabel }}
            </button>
        </div>

    </form>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

.bf { max-width: 820px; margin: 0 auto; font-family: 'DM Sans', sans-serif; display: flex; flex-direction: column; gap: 20px; }

.bf-block { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.bf-block__head { padding: 16px 22px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; }
.bf-block__head h3 { font-size: .95rem; font-weight: 700; color: #0a3d6b; }
.bf-block__head p  { font-size: .78rem; color: #64748b; margin-top: 2px; }

.bf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 20px 22px; }
.bf-field { display: flex; flex-direction: column; gap: 5px; }
.bf-field--wide { grid-column: 1 / -1; }
.bf-field label { font-size: .78rem; font-weight: 600; color: #374151; }
.bf-req { color: #ef4444; }

.bf-input { padding: 9px 13px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: .88rem; font-family: inherit; color: #1e293b; background: #f8fafc; outline: none; width: 100%; transition: border-color .2s, box-shadow .2s; resize: vertical; }
.bf-input:focus { border-color: #1565a8; background: #fff; box-shadow: 0 0 0 3px rgba(21,101,168,.1); }

.bf-check { display: flex; align-items: center; gap: 9px; font-size: .88rem; color: #374151; cursor: pointer; font-weight: 500; }
.bf-check input { width: 16px; height: 16px; accent-color: #2eaa45; }

.bf-warn { font-size: .78rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 9px 12px; margin-top: 8px; }

.bf-actions { display: flex; justify-content: flex-end; gap: 12px; }
.bf-btn { display: inline-flex; align-items: center; padding: 11px 24px; border-radius: 10px; font-family: inherit; font-size: .9rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: opacity .2s, transform .2s; }
.bf-btn--cancel { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.bf-btn--cancel:hover { background: #e2e8f0; }
.bf-btn--save { background: linear-gradient(135deg,#0a3d6b,#1565a8); color: #fff; box-shadow: 0 4px 16px rgba(10,61,107,.28); }
.bf-btn--save:hover:not(:disabled) { opacity: .92; transform: translateY(-1px); }
.bf-btn--save:disabled { opacity: .6; cursor: not-allowed; }

@media (max-width: 640px) {
    .bf-grid { grid-template-columns: 1fr; }
    .bf-actions { flex-direction: column-reverse; }
    .bf-btn { width: 100%; justify-content: center; }
}
</style>
