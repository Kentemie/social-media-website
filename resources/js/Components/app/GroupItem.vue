<script setup>
import { Link } from "@inertiajs/vue3";


defineProps({
    group: {
        type: Object
    }
});
</script>

<template>
    <div class="mb-3 cursor-pointer hover:bg-gray-100">
        <Link :href="route('group.profile', group.slug)" class="flex items-start gap-1 py-2 px-2">
            <img :src="group.thumbnail_url" alt="Some image" class="w-[32px] rounded-full" />
            <div class="flex-1">
                <div class="flex justify-between">
                    <h3 class="font-black text-lg">{{ group.name }}</h3>
                    {{ group.status === "approved" ? (group.role === "admin" ? group.role : "") : "you are not welcome here" }}
                </div>
                <div class="text-xs text-gray-500">{{ group.short_description }}</div>
            </div>
        </Link>
    </div>
</template>
