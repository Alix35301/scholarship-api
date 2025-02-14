<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    products: Array,
    buyers: Array,
    event: Object,
});

const form = useForm({
    buyer_id: '',
    items: [
        {
            product_id: '',
            quantity: 1,
            unit_price: '',
            discount: 0,
            is_foc: false,
            foc_reason: ''
        }
    ],
    notes: '',
});

const addItem = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
        unit_price: '',
        discount: 0,
        is_foc: false,
        foc_reason: ''
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const getMaxStock = (index) => {
    const item = form.items[index];
    return item.product_id ? props.products.find(p => p.id === item.product_id).stock : 1;
};

const getProductPrice = (index) => {
    const item = form.items[index];
    if (!item.product_id) return 0;
    const product = props.products.find(p => p.id === item.product_id);
    if (product && !item.unit_price) {
        item.unit_price = product.price;
    }
    return product ? product.price : 0;
};

const toggleFoc = (index) => {
    form.items[index] = {
        ...form.items[index],
        is_foc: !form.items[index].is_foc,
        discount: !form.items[index].is_foc ? 0 : form.items[index].discount
    };
};

const calculateSubtotal = (item) => {
    if (item.is_foc) return 0;
    return (item.quantity * (item.unit_price || 0)) - (item.discount || 0);
};

const totalAmount = computed(() => {
    return form.items.reduce((total, item) => {
        return total + calculateSubtotal(item);
    }, 0);
});

const totalFocValue = computed(() => {
    return form.items.reduce((total, item) => {
        if (item.is_foc) {
            return total + (item.quantity * (item.unit_price || 0));
        }
        return total;
    }, 0);
});

const submit = () => {
    form.post(route('events.sales.store', props.event.id));
};
</script>

<template>
    <Head title="Create Sale" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <Breadcrumbs
                    :items="[
                        { label: 'Dashboard', url: route('dashboard') },
                        { label: event.name, url: route('events.show', event.id) },
                        { label: 'Sales', url: route('events.sales.index', event.id) },
                        { label: 'Create Sale' }
                    ]"
                />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Sale</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="flex gap-6 flex-col">
                                <div v-for="(item, index) in form.items" :key="index" class="border p-4 md:p-6 rounded-lg">
                                    <div class="flex justify-between items-center mb-6">
                                        <h3 class="text-lg font-medium">Item {{ index + 1 }}</h3>
                                        <button type="button" @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 px-4 py-2 -mr-2" :disabled="form.items.length === 1">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="space-y-6">
                                        <!-- Product Selection -->
                                        <div class="w-full">
                                            <InputLabel class="text-base" :for="'product_' + index" value="Product" />
                                            <select :id="'product_' + index" v-model="item.product_id"
                                                class="mt-2 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm min-h-[44px]"
                                                required @change="getProductPrice(index)">
                                                <option value="">Select a product</option>
                                                <option v-for="product in products" :key="product.id" :value="product.id">
                                                    {{ product.name }} - RF {{ product.price }} (Stock: {{ product.stock }})
                                                </option>
                                            </select>
                                            <InputError :message="form.errors['items.' + index + '.product_id']" class="mt-2" />
                                        </div>

                                        <!-- Quantity Controls -->
                                        <div class="w-full">
                                            <InputLabel :for="'quantity_' + index" value="Quantity" class="text-base" />
                                            <div class="mt-2 flex">
                                                <button type="button"
                                                    class="px-6 py-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 touch-manipulation"
                                                    @click="item.quantity = Math.max(1, Number(item.quantity) - 1)"
                                                    :disabled="!item.product_id">
                                                    −
                                                </button>

                                                <input type="number" v-model.number="item.quantity"
                                                    class="w-24 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-center min-h-[44px]"
                                                    min="1" :max="getMaxStock(index)"
                                                    :disabled="!item.product_id"
                                                    @input="item.quantity = Math.min(Math.max(1, Number(item.quantity)), getMaxStock(index))" />

                                                <button type="button"
                                                    class="px-6 py-3 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 touch-manipulation"
                                                    @click="item.quantity = Math.min(getMaxStock(index), Number(item.quantity) + 1)"
                                                    :disabled="!item.product_id">
                                                    +
                                                </button>
                                            </div>
                                            <p v-if="item.product_id" class="mt-2 text-sm text-gray-500">
                                                Available stock: {{ getMaxStock(index) }} units
                                            </p>
                                            <InputError :message="form.errors['items.' + index + '.quantity']" class="mt-2" />
                                        </div>

                                        <!-- Discount (if not FOC) -->
                                        <div v-if="!item.is_foc" class="w-full">
                                            <InputLabel :for="'discount_' + index" value="Discount" class="text-base" />
                                            <TextInput :id="'discount_' + index" v-model="item.discount"
                                                type="number" step="0.01" class="mt-2 block w-full min-h-[44px]" />
                                            <InputError :message="form.errors['items.' + index + '.discount']" class="mt-2" />
                                        </div>

                                        <!-- FOC Controls -->
                                        <div class="w-full space-y-4">
                                            <label class="flex items-center min-h-[44px] cursor-pointer">
                                                <Checkbox :id="'is_foc_' + index"
                                                    :modelValue="item.is_foc"
                                                    @update:modelValue="(value) => { toggleFoc(index); }"
                                                    class="h-5 w-5" />
                                                <span class="ml-3 text-base text-gray-600">Free of Charge (FOC)</span>
                                            </label>

                                            <div class="w-full" v-if="item.is_foc">
                                                <InputLabel :for="'foc_reason_' + index" value="FOC Reason" class="text-base" />
                                                <TextArea :id="'foc_reason_' + index" v-model="item.foc_reason"
                                                    class="mt-2 block w-full" rows="3" required />
                                                <InputError :message="form.errors['items.' + index + '.foc_reason']" class="mt-2" />
                                            </div>
                                        </div>

                                        <!-- Item Total -->
                                        <div class="pt-4 border-t">
                                            <p class="text-right text-gray-600 text-lg">
                                                <template v-if="item.is_foc">
                                                    FOC Value: RF {{ (item.quantity * (item.unit_price || 0)).toFixed(2) }}
                                                </template>
                                                <template v-else>
                                                    Subtotal: RF {{ calculateSubtotal(item).toFixed(2) }}
                                                </template>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add Item & Totals -->
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-4">
                                    <button type="button" @click="addItem"
                                        class="text-indigo-600 hover:text-indigo-800 px-4 py-2 border border-indigo-600 rounded-md">
                                        + Add Another Item
                                    </button>
                                    <div class="text-right space-y-2">
                                        <p class="text-gray-600 text-lg">
                                            Total FOC Value: RF {{ totalFocValue.toFixed(2) }}
                                        </p>
                                        <p class="text-xl font-semibold">
                                            Total Amount: RF {{ totalAmount.toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="pt-4">
                                <InputLabel for="notes" value="Notes" class="text-base" />
                                <TextArea id="notes" v-model="form.notes" class="mt-2 block w-full" rows="4" />
                                <InputError :message="form.errors.notes" class="mt-2" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4 pt-4">
                                <PrimaryButton :disabled="form.processing" class="px-6 py-3 text-lg">
                                    Create Sale
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
