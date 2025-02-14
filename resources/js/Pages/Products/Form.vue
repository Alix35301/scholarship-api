<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';

const props = defineProps({
    product: {
        type: Object,
        default: () => ({
            name: '',
            description: '',
            price: '',
            stock: '',
            status: 'active'
        })
    },
    mode: {
        type: String,
        default: 'create'
    }
});

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    stock: props.product.stock,
    status: props.product.status
});

const submit = () => {
    if (props.mode === 'create') {
        form.post(route('products.store'));
    } else {
        form.put(route('products.update', props.product.id));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="name" value="Name" />
            <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
                required
            />
            <InputError :message="form.errors.name" class="mt-2" />
        </div>

        <div>
            <InputLabel for="description" value="Description" />
            <TextArea
                id="description"
                v-model="form.description"
                class="mt-1 block w-full"
                rows="4"
            />
            <InputError :message="form.errors.description" class="mt-2" />
        </div>

        <div>
            <InputLabel for="price" value="Price" />
            <TextInput
                id="price"
                v-model="form.price"
                type="number"
                step="0.01"
                class="mt-1 block w-full"
                required
            />
            <InputError :message="form.errors.price" class="mt-2" />
        </div>

        <div>
            <InputLabel for="stock" value="Stock" />
            <TextInput
                id="stock"
                v-model="form.stock"
                type="number"
                class="mt-1 block w-full"
                required
            />
            <InputError :message="form.errors.stock" class="mt-2" />
        </div>

        <div>
            <InputLabel for="status" value="Status" />
            <select
                id="status"
                v-model="form.status"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                required
            >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <InputError :message="form.errors.status" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">
                {{ mode === 'create' ? 'Create Product' : 'Update Product' }}
            </PrimaryButton>
        </div>
    </form>
</template>
