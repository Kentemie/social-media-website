<script setup>

import { ChatBubbleLeftEllipsisIcon, HandThumbUpIcon } from "@heroicons/vue/24/outline/index.js";
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

import axiosClient from "@/axiosClient.js";

import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import InputTextarea from "@/Components/InputTextarea.vue";
import IndigoButton from "@/Components/IndigoButton.vue";
import ReadMoreReadLess from "@/Components/ReadMoreReadLess.vue";


const newComment = ref("");
const editingComment = ref(null);


const props = defineProps({
    post: Object,
    data: Object,
    parentComment: {
        type: [Object, null],
        default: null,
    }
});
const emit = defineEmits([
    "commentCreate",
    "commentDelete",
]);


function createComment() {
    axiosClient.post(route("post.comment.create", props.post), {
        comment: newComment.value,
        parent_id: props.parentComment?.id || null,
    })
        .then(({ data }) => {
            newComment.value = "";
            props.data.comments.unshift(data);
            if (props.parentComment) {
                props.parentComment.number_of_comments++;
            }
            props.post.number_of_comments++;
            emit("commentCreate");
        });
}

function editComment(comment) {
    editingComment.value = {
        id: comment.id,
        comment: comment.comment.replace(/<br\s*\/?>/gi, "\n"),
    }
}

function deleteComment(commentId) {
    if (window.confirm("Are you sure you want to delete this comment?")) {
        axiosClient.delete(route("post.comment.delete", commentId))
            .then(() => {
                const commentIndex = props.data.comments.findIndex(com => com.id !== commentId);
                props.data.comments.splice(commentIndex, 1);

                if (props.parentComment) {
                    props.parentComment.number_of_comments--;
                }
                props.post.number_of_comments--;
                emit("commentDelete");
            });
    }
}

function updateComment() {
    axiosClient.put(route("post.comment.update", editingComment.value.id), editingComment.value)
        .then(({ data }) => {
            editingComment.value = null;
            props.data.comments = props.data.comments.map((comment) =>{
                if (comment.id === data.id) {
                    return data;
                }
                return comment;
            });
        });
}

function sendCommentReaction(comment) {
    axiosClient.post(route("post.comment.reaction", comment.id), {
        reaction: "like",
    })
        .then(({ data }) => {
            comment.current_user_has_reaction = data.current_user_has_reaction;
            comment.number_of_reactions = data.number_of_reactions;
        });
}

function onCommentCreate() {
    if (props.parentComment) {
        props.parentComment.number_of_comments++;
    }
    emit("commentCreate");
}

function onCommentDelete() {
    if (props.parentComment) {
        props.parentComment.number_of_comments--;
    }
    emit("commentDelete");
}

</script>

<template>
    <div class="mb-5">
        <div class="flex flex-1">
            <InputTextarea
                v-model="newComment"
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
            v-for="comment in data.comments" :key="comment.id" class="mb-5"
        >
            <div class="flex gap-2 justify-between">
                <div class="flex gap-2">
                    <Link :href="route('profile', comment.user.username)">
                        <img
                            :src="comment.user.avatar_url"
                            alt="Some avatar"
                            class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500"
                        />
                    </Link>
                    <div>
                        <h4 class="font-bold">
                            <Link :href="route('profile', comment.user.username)" class="hover:underline">
                                {{ comment.user.name }}
                            </Link>
                        </h4>
                        <small class="text-xs text-gray-400">{{ comment.updated_at }}</small>
                    </div>
                </div>
                <EditDeleteDropdown :user="comment.user" @edit="editComment(comment)" @delete="deleteComment(comment.id)" />
            </div>
            <div class="pl-12">
                <div v-if="editingComment && editingComment.id === comment.id">
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
                    :short-content="comment.short_comment"
                    content-class="text-sm flex flex-1"
                />
                <Disclosure v-slot="{ open }">
                    <div class="mt-1 flex gap-2">
                        <button
                            @click="sendCommentReaction(comment)"
                            class="flex items-center text-sm text-indigo-500 py-0.5 px-1 rounded-lg"
                            :class="[
                                comment.current_user_has_reaction ? 'bg-indigo-50 hover:bg-indigo-100' : 'hover:bg-indigo-100'
                            ]"
                        >
                            <HandThumbUpIcon class="w-3 h-3 mr-1" />
                            <span v-if="comment.number_of_reactions !== 0" class="mr-1">
                                {{ comment.number_of_reactions }}
                            </span>
                            {{ comment.current_user_has_reaction ? "unlike" : "like" }}
                        </button>
                        <DisclosureButton class="flex items-center text-sm text-indigo-500 py-0.5 px-1 hover:bg-indigo-100 rounded-lg">
                            <ChatBubbleLeftEllipsisIcon class="w-3 h-3 mr-1" />
                            <span v-if="comment.number_of_comments !== 0"  class="mr-1">
                                {{ comment.number_of_comments }}
                            </span>
                            comments
                        </DisclosureButton>
                    </div>
                    <DisclosurePanel class="mt-5">
                        <CommentList
                            :post="post"
                            :data="{ comments: comment.comments }"
                            :parent-comment="comment"
                            @comment-create="onCommentCreate"
                            @comment-delete="onCommentDelete"
                        />
                    </DisclosurePanel>
                </Disclosure>
            </div>
        </div>
    </div>
</template>
