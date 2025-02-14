<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps({
    event: Object,
    sales: Object,
});

const { flash } = useFlash();

const deleteSale = (id) => {
    if (confirm('Are you sure you want to delete this sale?')) {
        router.delete(route('events.sales.destroy', [props.event.id, id]));
    }
};

const updateStatus = (sale, status) => {
    router.patch(route('events.sales.update-status', [props.event.id, sale.id]), {
        status: status
    });
};
</script>

<template>
    <Head title="Sales" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: event.name, url: route('events.show', event.id) },
                        { label: 'Sales' }
                    ]"
                />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sales</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between mb-6">
                            <h3 class="text-lg font-semibold">Sales List</h3>
                            <Link
                                :href="route('events.sales.create', props.event.id)"
                                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                            >
                                Create Sale
                            </Link>
                        </div>
<!--
                        <div v-if="flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ flash.message }}
                        </div> -->

                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-4 py-3 text-left">Sale ID</th>
                                        <th class="px-4 py-3 text-left">Items</th>
                                        <th class="px-4 py-3 text-left">Seller</th>
                                        <th class="px-4 py-3 text-left">Total Items</th>
                                        <th class="px-4 py-3 text-left">Total Amount</th>
                                        <th class="px-4 py-3 text-left">FOC Value</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="sale in sales.data" :key="sale.id" class="border-t">
                                        <td class="px-4 py-3">#{{ sale.id }}</td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm">
                                                <div v-for="item in sale.items" :key="item.id" class="mb-1">
                                                    {{ item.product.name }}
                                                    <span class="text-gray-500">
                                                        ({{ item.quantity }} × ${{ item.unit_price }})
                                                        {{ item.is_foc ? '(FOC)' : '' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">{{ sale.seller.name }}</td>
                                        <td class="px-4 py-3">
                                            {{ sale.items.reduce((sum, item) => sum + item.quantity, 0) }}
                                            <span class="text-sm text-gray-500" v-if="sale.items.some(item => item.is_foc)">
                                                ({{ sale.items.filter(item => !item.is_foc).reduce((sum, item) => sum + item.quantity, 0) }} paid,
                                                {{ sale.items.filter(item => item.is_foc).reduce((sum, item) => sum + item.quantity, 0) }} FOC)
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">${{ sale.total_amount }}</td>
                                        <td class="px-4 py-3">${{ sale.total_foc_amount }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="{
                                                    'px-2 py-1 rounded text-sm': true,
                                                    'bg-yellow-100 text-yellow-800': sale.status === 'pending',
                                                    'bg-green-100 text-green-800': sale.status === 'completed',
                                                    'bg-red-100 text-red-800': sale.status === 'cancelled'
                                                }"
                                            >
                                                {{ sale.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-4">
                                                <Link
                                                    :href="route('events.sales.show', [props.event.id, sale.id])"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    View
                                                </Link>
                                                <button
                                                    @click="deleteSale(sale.id)"
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
