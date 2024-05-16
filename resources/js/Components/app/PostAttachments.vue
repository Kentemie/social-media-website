<script setup>
import { ArrowDownTrayIcon } from "@heroicons/vue/24/outline";
import { DocumentIcon } from "@heroicons/vue/24/solid";
import { isImage } from "@/helpers.js";

defineProps({
    attachments: {
        type: Array
    }
});
defineEmits(["attachmentClick"])

</script>

<template>
    <div v-for="(attachment, idx) in attachments.slice(0, 4)">
        <div
            @click="$emit('attachmentClick', idx)"
            class="group aspect-square bg-blue-100 flex flex-col items-center justify-center text-gray-500 relative cursor-pointer"
        >
            <div
                v-if="idx === 3 && attachments.length > 4"
                class="absolute left-0 right-0 top-0 bottom-0 z-10 bg-black/60 text-white flex items-center justify-center text-2xl"
            >
                +{{ attachments.length - 4 }} more
            </div>
            <a
                @click.stop
                :href="route('post.download', attachment)"
                class="z-20 opacity-0 group-hover:opacity-100 transition-all w-7 h-7 flex items-center justify-center text-gray-100 bg-gray-700 rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800"
            >
                <ArrowDownTrayIcon class="w-4 h-4"/>
            </a>
            <img v-if="isImage(attachment)"
                :src="attachment.url"
                alt="Some image"
                class="object-contain aspect-square"
            />
            <div v-else class="flex flex-col justify-center items-center px-3">
                <DocumentIcon class="w-12 h-12"/>
                <small>{{ attachment.name }}</small>
            </div>
        </div>
    </div>
</template>
