<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Form from './Form.vue';

const props = defineProps({ branch: Object });

const form = useForm({
    name:         props.branch.name ?? '',
    code:         props.branch.code ?? '',
    city:         props.branch.city ?? '',
    department:   props.branch.department ?? '',
    address:      props.branch.address ?? '',
    phone:        props.branch.phone ?? '',
    email:        props.branch.email ?? '',
    manager_name: props.branch.manager_name ?? '',
    opened_at:    props.branch.opened_at ? String(props.branch.opened_at).substring(0, 10) : '',
    notes:        props.branch.notes ?? '',
    is_active:    Boolean(props.branch.is_active),
});

const submit = () => form.put(route('branches.update', props.branch.id));
</script>

<template>
    <Head :title="`Editar ${branch.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="hz-head">Editar sede</h2>
        </template>
        <div class="hz-wrap">
            <Form
                :form="form"
                :affiliates-count="branch.affiliates_count"
                submit-label="Guardar cambios"
                @submit="submit"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap');
.hz-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.hz-wrap { padding: 24px 0 48px; }
</style>
