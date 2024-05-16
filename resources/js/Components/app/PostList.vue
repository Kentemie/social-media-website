<script setup>

import { onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

import axiosClient from "@/axiosClient.js";

import PostItem from "@/Components/app/PostItem.vue";
import PostModal from "@/Components/app/PostModal.vue";
import PostAttachmentPreviewModal from "@/Components/app/PostAttachmentPreviewModal.vue";


const authUser = usePage().props.auth.user;

const props = defineProps({
    posts: {
        type: Object,
    },
});


const showEditModal = ref(false);
const showAttachmentPreviewModal = ref(false);
const editPost = ref({});
const postAttachmentsPreview = ref({});
const loadMoreIntersect = ref(null);

onMounted(() => {
    const observer = new IntersectionObserver(
        entries => entries.forEach(entry => entry.isIntersecting && loadMore()), {
            rootMargin: "-250px 0px 0px 0px"
        }
    );
    observer.observe(loadMoreIntersect.value);
});


function loadMore() {
    if (!props.posts.links.next) {
        return;
    }

    axiosClient.get(props.posts.links.next)
        .then(({ data }) => {
            props.posts.data = [...props.posts.data, ...data.data];
            props.posts.links.next = data.links.next;
        });
}

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
            v-for="post in posts.data"
            :key="post.id"
            :post="post"
            @editClick="openEditModal"
            @attachmentClick="openAttachmentPreviewModal"
        />
        <div ref="loadMoreIntersect" />
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
