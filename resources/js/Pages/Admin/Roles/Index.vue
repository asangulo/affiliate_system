<!-- ═══════════════════════════════════════════════════════════
     Admin/Roles/Index.vue
     Coloca en: resources/js/Pages/Admin/Roles/Index.vue
     ═══════════════════════════════════════════════════════════ -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
 
defineProps({
    roles:       Array,
    permissions: Array,
});
 
const roleColor = (name) => ({
    super_admin: 'badge--navy',
    empleado:    'badge--blue',
    cliente:     'badge--green',
}[name] || 'badge--gray');
 
const roleLabel = (name) => ({
    super_admin: 'Super Admin',
    empleado:    'Empleado',
    cliente:     'Cliente',
}[name] || name);
 
const permGroup = (name) => name.split('.')[0];
</script>
 
<template>
    <Head title="Roles y Permisos"/>
    <AuthenticatedLayout>
        <template #header>
            <h2>Roles y Permisos</h2>
            <p>Vista de los roles definidos en el sistema</p>
        </template>
 
        <div class="rl">
            <div class="rl-grid">
                <div v-for="role in roles" :key="role.id" class="rl-card">
                    <div class="rl-card__header">
                        <span class="badge" :class="roleColor(role.name)">
                            {{ roleLabel(role.name) }}
                        </span>
                        <span class="rl-card__count">{{ role.users_count }} usuario(s)</span>
                    </div>
                    <div class="rl-card__perms">
                        <p class="rl-card__perms-title">Permisos asignados</p>
                        <div class="rl-perms-list">
                            <span v-for="perm in role.permissions" :key="perm.id"
                                class="rl-perm">
                                {{ perm.name }}
                            </span>
                            <span v-if="!role.permissions.length" class="rl-perm rl-perm--empty">
                                Sin permisos asignados
                            </span>
                        </div>
                    </div>
                </div>
            </div>
 
            <div class="rl-info">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <p>Los roles y permisos se gestionan desde el código (RolesAndPermissionsSeeder). Para modificarlos ejecuta el seeder nuevamente.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
 
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');
*, *::before, *::after { box-sizing:border-box; }
 
.rl { display:flex; flex-direction:column; gap:20px; font-family:'DM Sans',sans-serif; }
 
.rl-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; }
 
.rl-card { background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
 
.rl-card__header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; background:#f8fafc; border-bottom:1px solid #f0f4f8; }
 
.badge { display:inline-block; padding:4px 12px; border-radius:99px; font-size:0.78rem; font-weight:700; }
.badge--navy  { background:#dbeafe; color:#1e3a8a; }
.badge--blue  { background:#e0f2fe; color:#075985; }
.badge--green { background:#dcfce7; color:#166534; }
.badge--gray  { background:#f1f5f9; color:#475569; }
 
.rl-card__count { font-size:0.78rem; color:#94a3b8; font-weight:500; }
 
.rl-card__perms { padding:16px 20px; }
.rl-card__perms-title { font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px; }
 
.rl-perms-list { display:flex; flex-wrap:wrap; gap:6px; }
 
.rl-perm { display:inline-block; padding:3px 9px; background:#f0f4f8; color:#475569; border-radius:6px; font-size:0.73rem; font-weight:500; }
.rl-perm--empty { color:#cbd5e1; font-style:italic; }
 
.rl-info { display:flex; align-items:flex-start; gap:10px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px 16px; font-size:0.83rem; color:#1e40af; }
.rl-info svg { width:18px; height:18px; flex-shrink:0; margin-top:1px; }
</style>