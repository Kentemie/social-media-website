<script setup>
import { Link } from "@inertiajs/vue3";


defineProps({
    user: {
        type: Object,
    },
    forApproveOrReject: {
        type: Boolean,
        default: false,
    },
    showRoleDropdown: {
        type: Boolean,
        default: false,
    },
    disableRoleDropdown: {
        type: Boolean,
        default: false,
    }
});
defineEmits([
    "approve",
    "reject",
    "roleChange"
]);
</script>

<template>
    <div class="transition-all bg-white border-2 border-transparent hover:border-indigo-500">
        <div class="flex items-center gap-2 py-2 px-2">
            <Link :href="route('profile', user.username)">
                <img alt="Some image" :src="user.avatar_url" class="w-[32px] rounded-full" />
            </Link>
            <div class="flex flex-1 justify-between">
                <Link :href="route('profile', user.username)">
                    <h3 class="font-black hover:underline">{{ user.name }}</h3>
                </Link>
                <div v-if="forApproveOrReject" class="flex gap-1">
                    <button
                        class="text-xs text-white py-1 px-2 rounded bg-emerald-500 hover:bg-emerald-600"
                        @click="$emit('approve')">
                        approve
                    </button>
                    <button
                        class="text-xs text-white py-1 px-2 rounded bg-red-500 hover:bg-red-600"
                        @click="$emit('reject')">
                        reject
                    </button>
                </div>
                <div v-if="showRoleDropdown">
                    <select
                        @change="$emit('roleChange', user.id, $event.target.value)"
                        :disabled="disableRoleDropdown"
                        class="rounded-md border-0 py-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 max-w-xs text-sm leading-6"
                    >
                        <option :selected="user.role === 'admin'">admin</option>
                        <option :selected="user.role === 'user'">user</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
