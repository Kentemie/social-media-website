<script setup>

import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import PostModal from "@/Components/app/PostModal.vue";


const authUser = usePage().props.auth.user;


const showModal = ref(false);
const newPost = ref({
    id: 0,
    body: '',
    user: authUser,
});


function showCreatePostModal() {
    showModal.value = true;
}

</script>

<template>
    <div class="p-4 bg-white rounded-lg border shadow mb-3">
        <div
            @click="showCreatePostModal"
            class="py-2 px-3 border-2 border-gray-300 hover:border-gray-500 text-gray-500 rounded-md shadow mb-3 w-full"
        >
            Click here to create a new post
        </div>
<!--        <div v-if="showModal" class="flex gap-2 justify-between">-->
<!--            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 relative">-->
<!--                Attach files-->
<!--                <input type="file" class="absolute top-0 bottom-0 left-0 right-0 opacity-0">-->
<!--            </button>-->
<!--            <button @click="submitPostCreation" type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">-->
<!--                Submit-->
<!--            </button>-->
<!--        </div>-->
        <PostModal :post="newPost" v-model="showModal" />
    </div>
</template>

<style scoped>

</style>
