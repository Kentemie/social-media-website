<script setup>

import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import PostItem from "@/Components/app/PostItem.vue";
import PostModal from "@/Components/app/PostModal.vue";
import PostAttachmentPreviewModal from "@/Components/app/PostAttachmentPreviewModal.vue";


defineProps({
    posts: {
        type: Array,
    }
});


const showEditModal = ref(false);
const showAttachmentPreviewModal = ref(false);
const editPost = ref({});
const postAttachmentsPreview = ref({});


const authUser = usePage().props.auth.user;


function openEditModal(post) {
    showEditModal.value = true;
    editPost.value = post;
}

function openAttachmentPreviewModal(post, index) {
    showAttachmentPreviewModal.value = true;
    postAttachmentsPreview.value = {
        post,
        index
    };
}

function onEditModalHide() {
    editPost.value = {
        id: null,
        body: '',
        user: authUser,
    };
}

</script>

<template>
    <div class="overflow-auto">
        <PostItem
            v-for="post in posts"
            :key="post.id"
            :post="post"
            @editClick="openEditModal"
            @attachmentClick="openAttachmentPreviewModal"
        />
        <PostModal
            :post="editPost"
            v-model="showEditModal"
            @hide="onEditModalHide"
        />
        <PostAttachmentPreviewModal
            :attachments="postAttachmentsPreview.post?.attachments || []"
            v-model:index="postAttachmentsPreview.index"
            v-model="showAttachmentPreviewModal"
        />
    </div>
</template>

<style scoped>

</style>
