<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Form from './Form.vue';

const form = useForm({
    name: '', code: '', city: '', department: '', address: '',
    phone: '', email: '', manager_name: '', opened_at: '',
    notes: '', is_active: true,
});

const submit = () => form.post(route('branches.store'));
</script>

<template>
    <Head title="Crear sede" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="hz-head">Crear sede</h2>
        </template>
        <div class="hz-wrap">
            <Form :form="form" submit-label="Crear sede" @submit="submit" />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap');
.hz-head { font-size: 1.25rem; font-weight: 700; color: #0a3d6b; font-family: 'DM Sans', sans-serif; }
.hz-wrap { padding: 24px 0 48px; }
</style>
