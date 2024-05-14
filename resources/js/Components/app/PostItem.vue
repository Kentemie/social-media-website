<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { DocumentIcon } from '@heroicons/vue/24/solid';
import { HandThumbUpIcon, ChatBubbleLeftRightIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { router, usePage } from "@inertiajs/vue3";
import { isImage } from '@/helpers.js';
import { ref } from "vue";

import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import axiosClient from "@/axiosClient.js";
import InputTextarea from "@/Components/InputTextarea.vue";
import IndigoButton from "@/Components/IndigoButton.vue";
import ReadMoreReadLess from "@/Components/ReadMoreReadLess.vue";
import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import DangerButton from "@/Components/DangerButton.vue";


const authUser = usePage().props.auth.user;


const props = defineProps({
    post: Object,
});
const emit = defineEmits(
    ['editClick', 'attachmentClick'],
);


const newCommentText = ref('');
const editingComment = ref(null);


function openEditModal() {
    emit('editClick', props.post);
}

function openAttachmentModal(index) {
    emit('attachmentClick', props.post, index);
}

function deletePost() {
    if (window.confirm("Are you sure you want to delete this post?")) {
        router.delete(route('post.destroy', props.post.id), {
            preserveScroll: true,
        });
    }
}

function sendReaction() {
    axiosClient.post(route('post.reaction', props.post), {
        reaction: 'like',
    })
        .then(({ data }) => {
            props.post.current_user_has_reaction = data.current_user_has_reaction;
            props.post.number_of_reactions = data.number_of_reactions;
        });
}

function createComment() {
    axiosClient.post(route('post.comment.create', props.post), {
        comment: newCommentText.value,
    })
        .then(({ data }) => {
            newCommentText.value = '';
            props.post.comments.unshift(data);
            props.post.number_of_comments++;
        });
}

function editComment(comment) {
    editingComment.value = {
        id: comment.id,
        comment: comment.comment.replace(/<br\s*\/?>/gi, '\n'),
    }
}

function deleteComment(comment_id) {
    if (window.confirm("Are you sure you want to delete this comment?")) {
        axiosClient.delete(route('post.comment.delete', comment_id))
            .then(() => {
                props.post.comments = props.post.comments.filter(com => com.id !== comment_id);
                props.post.number_of_comments--;
            });
    }
}

function updateComment() {
    axiosClient.put(route('post.comment.update', editingComment.value.id), editingComment.value)
        .then(({ data }) => {
            editingComment.value = null;
            props.post.comments = props.post.comments.map((comment) =>{
                if (comment.id === data.id) {
                    return data;
                }
                return comment;
            });
        });
}

</script>

<template>
    <div class="bg-white border rounded p-4 shadow mb-3">
        <div class="flex items-center justify-between">
            <PostUserHeader :post="post" />
            <EditDeleteDropdown :user="post.user" @edit="openEditModal" @delete="deletePost" />
        </div>
        <div class="mb-2">
            <ReadMoreReadLess :content="post.body" />
        </div>
        <div v-if="post.attachments.length !== 0" class="grid gap-3 mb-3" :class="[
            post.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2 lg:grid-cols-3'
        ]">
            <div v-for="(attachment, idx) in post.attachments.slice(0, 4)">
                <div
                    @click="openAttachmentModal(idx)"
                    class="group aspect-square bg-blue-100 flex flex-col items-center justify-center text-gray-500 relative cursor-pointer"
                >

                    <div
                        v-if="idx === 3 && post.attachments.length > 4"
                        class="absolute left-0 right-0 top-0 bottom-0 z-10 bg-black/60 text-white flex items-center justify-center text-2xl"
                    >
                        +{{ post.attachments.length - 4 }} more
                    </div>

                    <a
                        @click.stop
                        :href="route('post.download', attachment)"
                        class="z-20 opacity-0 group-hover:opacity-100 transition-all w-7 h-7 flex items-center justify-center text-gray-100 bg-gray-700 rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4" />
                    </a>

                    <img v-if="isImage(attachment)"
                         :src="attachment.url"
                         alt="Some image"
                         class="object-contain aspect-square"
                    />
                    <div v-else class="flex flex-col justify-center items-center px-3">
                        <DocumentIcon class="w-12 h-12" />
                        <small>{{ attachment.name }}</small>
                    </div>
                </div>
            </div>
        </div>
        <Disclosure v-slot="{ open }">
            <div class="flex gap-2">
                <button
                    @click="sendReaction"
                    class="text-gray-800 flex flex-1 gap-1 items-center justify-center rounded-lg py-2 px-4"
                    :class="[
                    post.current_user_has_reaction ? 'bg-sky-100 hover:bg-sky-200' : 'bg-gray-100 hover:bg-gray-200'
                ]"
                >
                    <HandThumbUpIcon class="w-6 h-6" />
                    <span class="mr-1">
                        {{ post.number_of_reactions }}
                    </span>
                    Like
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

            <DisclosurePanel class="mt-5">
                <div class="flex gap-2 mb-5">
                    <a href="javascript:void(0)">
                        <img
                            :src="authUser.avatar_url"
                            alt="Some avatar"
                            class="w-[40px] h-[40px] rounded-full border border-2 transition-all hover:border-blue-500"
                        />
                    </a>
                    <div class="flex flex-1">
                        <InputTextarea
                            v-model="newCommentText"
                            rows="1"
                            class="w-full resize-none max-h-[160px] rounded-r-none"
                            placeholder="Enter your comment here"
                        />
                        <IndigoButton
                            @click="createComment"
                            class="rounded-l-none w-[100px]"
                        >
                            Submit
                        </IndigoButton>
                    </div>
                </div>
                <div>
                    <div
                        v-for="comment in post.comments" :key="comment.id" class="mb-5"
                    >
                        <div class="flex gap-2 justify-between">
                            <div class="flex gap-2">
                                <a href="javascript:void(0)">
                                    <img
                                        :src="comment.user.avatar_url"
                                        alt="Some avatar"
                                        class="w-[40px] h-[40px] rounded-full border border-2 transition-all hover:border-blue-500"
                                    />
                                </a>
                                <div>
                                    <h4 class="font-bold">
                                        <a href="javascript:void(0)" class="hover:underline">
                                            {{ comment.user.name }}
                                        </a>
                                    </h4>
                                    <small class="text-xs text-gray-400">{{ comment.updated_at }}</small>
                                </div>
                            </div>
                            <EditDeleteDropdown :user="comment.user" @edit="editComment(comment)" @delete="deleteComment(comment.id)" />
                        </div>
                        <div v-if="editingComment && editingComment.id === comment.id" class="ml-12">
                            <InputTextarea
                                v-model="editingComment.comment"
                                rows="1"
                                class="w-full resize-none max-h-[160px]"
                                placeholder="Enter your comment here"
                            />
                            <div class="flex gap-2 justify-end">
                                <button @click="editingComment = null" class="text-indigo-500">
                                    cancel
                                </button>
                                <IndigoButton
                                    @click="updateComment"
                                    class="w-[100px]"
                                >
                                    update
                                </IndigoButton>
                            </div>
                        </div>
                        <ReadMoreReadLess
                            v-else
                            :content="comment.comment"
                            content-class="text-sm ml-12 flex flex-1"
                        />
                    </div>
                </div>
            </DisclosurePanel>
        </Disclosure>
    </div>
</template>

<style scoped>

</style>
