<script setup>

import { ref } from "vue";

import PostItem from "@/Components/app/PostItem.vue";
import PostModal from "@/Components/app/PostModal.vue";


defineProps({
    posts: {
        type: Array,
    }
});


const showEditModal = ref(false);
const editPost = ref({});


function openEditModal(post) {
    showEditModal.value = true;
    editPost.value=post;
}

</script>

<template>
    <div class="overflow-auto">
        <PostItem v-for="post in posts" :key="post.id" :post="post" @editClick="openEditModal" />
        <PostModal :post="editPost" v-model="showEditModal" />
    </div>
</template>

<style scoped>

</style>
