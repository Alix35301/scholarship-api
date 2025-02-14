import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useFlash() {
    const page = usePage();

    const flash = computed(() => page.props.flash);

    return {
        flash,
    };
}
