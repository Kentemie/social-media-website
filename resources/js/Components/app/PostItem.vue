<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ChatBubbleLeftRightIcon, HandThumbUpIcon } from "@heroicons/vue/24/outline";
import { router } from "@inertiajs/vue3";

import axiosClient from "@/axiosClient.js";

import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import ReadMoreReadLess from "@/Components/ReadMoreReadLess.vue";
import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import PostAttachments from "@/Components/app/PostAttachments.vue";
import CommentList from "@/Components/app/CommentList.vue";


const props = defineProps({
    post: Object,
});
const emit = defineEmits(
    ["editClick", "attachmentClick"],
);


function openPostEditModal() {
    emit("editClick", props.post);
}

function openPostAttachmentModal(index) {
    emit("attachmentClick", props.post, index);
}

function deletePost() {
    if (window.confirm("Are you sure you want to delete this post?")) {
        router.delete(route("post.destroy", props.post.id), {
            preserveScroll: true,
        });
    }
}

function sendPostReaction() {
    axiosClient.post(route("post.reaction", props.post), {
        reaction: "like",
    })
        .then(({ data }) => {
            props.post.current_user_has_reaction = data.current_user_has_reaction;
            props.post.number_of_reactions = data.number_of_reactions;
        });
}

</script>

<template>
    <div class="bg-white border rounded p-4 shadow mb-3">
        <div class="flex items-center justify-between">
            <PostUserHeader :post="post" />
            <EditDeleteDropdown :user="post.user" @edit="openPostEditModal" @delete="deletePost" />
        </div>
        <div class="mb-2">
            <ReadMoreReadLess :content="post.body" />
        </div>
        <div v-if="post.attachments.length !== 0" class="grid gap-3 mb-3" :class="[
            post.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2 lg:grid-cols-3'
        ]">
            <PostAttachments
                :attachments="post.attachments"
                @attachmentClick="openPostAttachmentModal"
            />
        </div>
        <Disclosure v-slot="{ open }">

            <!--     Like & Comment buttons     -->

            <div class="flex gap-2">
                <button
                    @click="sendPostReaction"
                    class="text-gray-800 flex flex-1 gap-1 items-center justify-center rounded-lg py-2 px-4"
                    :class="[
                        post.current_user_has_reaction ? 'bg-sky-100 hover:bg-sky-200' : 'bg-gray-100 hover:bg-gray-200'
                    ]"
                >
                    <HandThumbUpIcon class="w-6 h-6" />
                    <span class="mr-1">
                        {{ post.number_of_reactions }}
                    </span>
                    {{ post.current_user_has_reaction ? "Unlike" : "Like" }}
                </button>
                <DisclosureButton
                    class="text-gray-800 flex flex-1 gap-1 items-center justify-center rounded-lg py-2 px-4 bg-gray-100 hover:bg-gray-200"
                >
                    <ChatBubbleLeftRightIcon class="w-6 h-6" />
                    <span class="mr-1">
                        {{ post.number_of_comments }}
                    </span>
                    Comment
                </DisclosureButton>
            </div>

            <DisclosurePanel class="comment-list mt-5 h-[400px] overflow-auto">
                <CommentList :post="post" :data="{ comments: post.comments }" />
            </DisclosurePanel>
        </Disclosure>
    </div>
</template>

