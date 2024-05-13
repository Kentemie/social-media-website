<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { PencilIcon, TrashIcon, EllipsisVerticalIcon, DocumentIcon } from '@heroicons/vue/24/solid';
import { HandThumbUpIcon, ChatBubbleLeftRightIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import { router } from "@inertiajs/vue3";
import { isImage } from '@/helpers.js';

import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import axiosClient from "@/axiosClient.js";

const props = defineProps({
    post: Object,
});
const emit = defineEmits(
    ['editClick', 'attachmentClick'],
);


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

</script>

<template>
    <div class="bg-white border rounded p-4 shadow mb-3">
        <div class="flex items-center justify-between">
            <PostUserHeader :post="post" />
            <Menu as="div" class="relative inline-block text-left z-30">
                <div>
                    <MenuButton
                        class="w-8 h-8 rounded-full hover:bg-black/10 transition flex items-center justify-center"
                    >
                        <EllipsisVerticalIcon
                            class="h-5 w-5"
                            aria-hidden="true"
                        />
                    </MenuButton>
                </div>

                <transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <MenuItems
                        class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
                    >
                        <div class="px-1 py-1">
                            <MenuItem v-slot="{ active }">
                                <button
                                    @click="openEditModal"
                                    :class="[
                                        active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                    ]"
                                >
                                    <PencilIcon
                                        class="mr-2 h-5 w-5"
                                        aria-hidden="true"
                                    />
                                    Edit
                                </button>
                            </MenuItem>
                            <MenuItem v-slot="{ active }">
                                <button
                                    @click="deletePost"
                                    :class="[
                                        active ? 'bg-indigo-600 text-white' : 'text-gray-900',
                                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                                    ]"
                                >
                                    <TrashIcon
                                        class="mr-2 h-5 w-5"
                                        aria-hidden="true"
                                    />
                                    Delete
                                </button>
                            </MenuItem>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </div>
        <div class="mb-2">
            <Disclosure v-slot="{ open }">
                <div v-if="!open" v-html="post.body.substring(0, 200)" class="ck-content-output" />
                <div v-if="post.body.length > 200">
                    <DisclosurePanel>
                        <div v-html="post.body" class="ck-content-output" />
                    </DisclosurePanel>
                    <div class="flex justify-end">
                        <DisclosureButton class="text-blue-500 hover:underline">
                            {{ open ? "Read less" : "Read more" }}
                        </DisclosureButton>
                    </div>
                </div>
            </Disclosure>
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
        <div class="flex gap-2">
            <button
                @click="sendReaction"
                class="text-gray-800 flex flex-1 gap-1 items-center justify-center rounded-lg py-2 px-4"
                :class="[
                    post.current_user_has_reaction ? 'bg-gray-100 hover:bg-gray-200' : 'bg-sky-100 hover:bg-sky-200'
                ]"
            >
                <HandThumbUpIcon class="w-6 h-6" />
                <span class="mr-1">
                    {{ post.number_of_reactions }}
                </span>
                Like
            </button>
            <button class="text-gray-800 flex flex-1 gap-1 items-center justify-center rounded-lg py-2 px-4 bg-gray-100 hover:bg-gray-200">
                <ChatBubbleLeftRightIcon class="w-6 h-6 mr-1" />
                Comment
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>
