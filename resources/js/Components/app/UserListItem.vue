<script setup>
import { Link } from "@inertiajs/vue3";


defineProps({
    user: Object,
    forApproveOrReject: {
        type: Boolean,
        default: false ,
    }
});
defineEmits([
    "approve",
    "reject"
]);
</script>

<template>
    <div class="cursor-pointer transition-all bg-white border-2 border-transparent hover:border-indigo-500">
        <Link :href="route('profile', user.username)" class="flex items-center gap-2 py-2 px-2">
            <img alt="Some image" :src="user.avatar_url" class="w-[32px] rounded-full" />
            <div class="flex flex-1 justify-between">
                <h3 class="font-black">{{ user.name }}</h3>
                <div class="flex gap-1">
                    <button
                        v-if="forApproveOrReject"
                        class="text-xs text-white py-1 px-2 rounded bg-emerald-500 hover:bg-emerald-600"
                        @click.prevent.stop="$emit('approve')">
                        approve
                    </button>
                    <button
                        v-if="forApproveOrReject"
                        class="text-xs text-white py-1 px-2 rounded bg-red-500 hover:bg-red-600"
                        @click.prevent.stop="$emit('reject')">
                        reject
                    </button>
                </div>
            </div>
        </Link>
    </div>
</template>

<style scoped>

</style>
