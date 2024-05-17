<script setup>

import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";

defineProps({
    content: {
        type: String,
    },
    shortContent: {
        type: String,
    },
    contentClass: {
        type: String,
    }
})

</script>

<template>
    <Disclosure v-slot="{ open }">
        <div v-if="!open" :class="contentClass" v-html="shortContent" class="ck-content-output" />
        <div v-if="content && content.length > shortContent.length">
            <DisclosurePanel>
                <div v-html="content" :class="contentClass" class="ck-content-output" />
            </DisclosurePanel>
            <div class="flex justify-end">
                <DisclosureButton class="text-blue-500 hover:underline mr-4">
                    {{ open ? "Read less" : "Read more" }}
                </DisclosureButton>
            </div>
        </div>
    </Disclosure>
</template>

<style scoped>

</style>
