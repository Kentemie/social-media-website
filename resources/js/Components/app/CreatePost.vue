<script setup>
import { ref } from "vue";

const postCreating = ref(false);
</script>

<template>
    <div class="p-4 bg-white rounded-lg border shadow mb-3">
        <div @click="postCreating = !postCreating" class="py-3 px-2 text-gray-500 border border-2 border-gray-300 rounded mb-3">
            Click here to create a new post
        </div>
        <div v-if="postCreating" class="flex gap-2 justify-between">
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 relative">
                Attach files
                <input type="file" class="absolute top-0 bottom-0 left-0 right-0 opacity-0">
            </button>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Submit
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>
