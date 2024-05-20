<script setup>

import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import PostModal from "@/Components/app/PostModal.vue";


const authUser = usePage().props.auth.user;


defineProps({
    group: {
        type: Object,
        default: null,
    }
});


const showModal = ref(false);
const newPost = ref({
    id: 0,
    body: "",
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
        <PostModal :post="newPost" :group="group" v-model="showModal" />
    </div>
</template>

<style scoped>

</style>
