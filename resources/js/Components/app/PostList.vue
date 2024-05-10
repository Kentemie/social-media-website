<script setup>

import { ref } from "vue";

import PostItem from "@/Components/app/PostItem.vue";
import PostModal from "@/Components/app/PostModal.vue";
import {usePage} from "@inertiajs/vue3";


defineProps({
    posts: {
        type: Array,
    }
});


const showEditModal = ref(false);
const editPost = ref({});


const authUser = usePage().props.auth.user;


function openEditModal(post) {
    showEditModal.value = true;
    editPost.value = post;
}

function onModalHide() {
    editPost.value = {
        id: null,
        body: '',
        user: authUser,
    };
}

</script>

<template>
    <div class="overflow-auto">
        <PostItem v-for="post in posts" :key="post.id" :post="post" @editClick="openEditModal" />
        <PostModal :post="editPost" v-model="showEditModal" @hide="onModalHide" />
    </div>
</template>

<style scoped>

</style>
