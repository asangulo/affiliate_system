<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    branches: Array,
    plans: Array
});

const form = useForm({
    full_name: '',
    id_number: '',
    branch_id: '',
    plan_id: '',
    fee_amount: 0,
    email: '',
    phone: '',
});

const submit = () => {
    form.post(route('affiliates.store'));
};
</script>

<template>
    <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-bold mb-6">Nuevo Afiliado</h2>
        
        <form @submit.prevent="submit" class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block font-medium">Nombre Completo</label>
                <input v-model="form.full_name" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
                <div v-if="form.errors.full_name" class="text-red-500 text-sm">{{ form.errors.full_name }}</div>
            </div>

            <div>
                <label class="block font-medium">Cédula / ID</label>
                <input v-model="form.id_number" type="text" class="w-full border-gray-300 rounded-md shadow-sm" />
            </div>

            <div>
                <label class="block font-medium">Sede</label>
                <select v-model="form.branch_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Seleccione una sede</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                        {{ branch.name }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block font-medium">Plan</label>
                <select v-model="form.plan_id" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Seleccione un plan</option>
                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                        {{ plan.name }}
                    </option>
                </select>
            </div>

            <div class="col-span-2 mt-4">
                <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    {{ form.processing ? 'Guardando...' : 'Registrar Afiliado' }}
                </button>
            </div>
        </form>
    </div>
</template>