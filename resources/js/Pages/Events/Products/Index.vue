<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useFlash } from '@/Composables/useFlash';

const props = defineProps({
    event: Object,
    products: Object,
});

const { flash } = useFlash();

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('events.products.destroy', [props.event.id, id]));
    }
};
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: 'Events', url: route('events.index') },
                        { label: event.name, url: route('events.show', event.id) },
                        { label: 'Products' }
                    ]"
                />
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ event.name }} - Products</h2>
                    <Link
                        :href="route('events.products.create', event.id)"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                    >
                        Add Product
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="flash && flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ flash.message }}
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-4 py-3 text-left">Name</th>
                                        <th class="px-4 py-3 text-left">Price</th>
                                        <th class="px-4 py-3 text-left">Stock</th>
                                        <th class="px-4 py-3 text-left">Owner</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in products.data" :key="product.id" class="border-t">
                                        <td class="px-4 py-3">{{ product.name }}</td>
                                        <td class="px-4 py-3">RF {{ product.price }}</td>
                                        <td class="px-4 py-3">{{ product.stock }}</td>
                                        <td class="px-4 py-3">{{ product.owner.name }}</td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="{
                                                    'px-2 py-1 rounded text-sm': true,
                                                    'bg-green-100 text-green-800': product.status === 'active',
                                                    'bg-red-100 text-red-800': product.status === 'inactive'
                                                }"
                                            >
                                                {{ product.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-4">
                                                <Link
                                                    :href="route('events.products.edit', [event.id, product.id])"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="deleteProduct(product.id)"
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
