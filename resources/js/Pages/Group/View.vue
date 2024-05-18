<script setup>
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import { useForm, usePage } from "@inertiajs/vue3";
import { XMarkIcon, CheckCircleIcon, CameraIcon } from "@heroicons/vue/24/solid";
import { CameraIcon as CameraIconOutline } from "@heroicons/vue/24/outline";
import { computed, ref } from "vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabItem from "@/Pages/Profile/Partials/TabItem.vue";

import PrimaryButton from "@/Components/PrimaryButton.vue";
import InviteUserModal from "@/Pages/Group/Partials/InviteUserModal.vue";
import UserListItem from "@/Components/app/UserListItem.vue";
import TextInput from "@/Components/TextInput.vue";


const authUser = usePage().props.auth.user;

const imagesForm = useForm({
    cover: null,
    thumbnail: null,
});


const props = defineProps({
    group: {
        type: Object,
    },
    requests: {
        type: Array,
    },
    users: {
        type: Array,
    },
    success: {
        type: String,
    },
    errors: {
        type: Object,
    },
});


const coverImageSource = ref("");
const thumbnailImageSource = ref("");
const searchKeyword = ref("");
const showNotification = ref(true);
const showInviteUserModal = ref(false);

const isCurrentUserAdmin = computed(() => props.group.role === "admin");
const isInGroup = computed(() => !!props.group.role && props.group.status === "approved");


function onCoverChange(event) {
    imagesForm.cover = event.target.files[0];

    if (imagesForm.cover) {
        const reader = new FileReader();
        reader.onload = () => {
            coverImageSource.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.cover);
    }
}

function resetCoverImageUpdate() {
    coverImageSource.value = "";
    imagesForm.cover = null;
}

function onThumbnailChange(event) {
    imagesForm.thumbnail = event.target.files[0];

    if (imagesForm.thumbnail) {
        const reader = new FileReader();
        reader.onload = () => {
            thumbnailImageSource.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.thumbnail);
    }
}

function resetThumbnailImageUpdate() {
    thumbnailImageSource.value = "";
    imagesForm.thumbnail = null;
}

function submitImageUpdate(resetFunction) {
    imagesForm.post(route("group.updateImage", props.group.slug), {
        preserveScroll: true,
        onSuccess: () => {
            resetFunction();
            showNotification.value = true;
            setTimeout(() => {
                showNotification.value = false;
            }, 3000);
        },
    });
}

function joinToGroup() {
    const form = useForm({});
    form.post(route("group.joinGroup", props.group.slug), {
        preserveScroll: true,
    });
}

function approveRequest(userId) {
    const form = useForm({
        user_id: userId,
        action: "approve"
    });
    form.post(route("group.processRequest", props.group.slug), {
        preserveScroll: true,
    });
}

function rejectRequest(userId) {
    const form = useForm({
        user_id: userId,
        action: "reject"
    });
    form.post(route("group.processRequest", props.group.slug), {
        preserveScroll: true,
    });
}

function onRoleChange(userId, role) {
    const form = useForm({
        user_id: userId,
        role: role
    });
    form.post(route("group.changeRole", props.group.slug), {
        preserveScroll: true,
    });
}

</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-[1000px] mx-auto h-full overflow-auto">
            <div class="px-4">
                <div v-show="showNotification && success" class="my-2 py-2 px-3 font-medium text-sm bg-emerald-500 text-white">
                    {{ success }}
                </div>
                <div v-if="errors.cover" class="my-2 py-2 px-3 font-medium text-sm bg-red-500 text-white">
                    {{ errors.cover }}
                </div>
                <div class="group relative bg-white">
                    <img
                        :src="coverImageSource || group.cover_url"
                        alt="Some cover image"
                        class="w-full h-[200px] object-cover"
                    >
                    <div v-if="isCurrentUserAdmin" class="absolute top-2 right-2">
                        <button v-if="!coverImageSource" class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 transition-all">
                            <CameraIconOutline class="w-3 h-3 mr-2" />
                            Update cover image
                            <input
                                type="file"
                                class="absolute left-0 right-0 top-0 bottom-0 opacity-0"
                                @change="onCoverChange"
                            />
                        </button>
                        <div v-else class="flex gap-2">
                            <button
                                @click="resetCoverImageUpdate"
                                class="bg-gray-50 hover:bg-gray-150 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 transition-all"
                            >
                                <XMarkIcon class="w-3 h-3 mr-1" />
                                Cancel
                            </button>
                            <button
                                @click="submitImageUpdate(resetCoverImageUpdate)"
                                class="bg-gray-800 hover:bg-gray-900 text-gray-100 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 transition-all"
                            >
                                <CheckCircleIcon class="w-3 h-3 mr-1" />
                                Submit
                            </button>
                        </div>
                    </div>
                    <div class="flex">
                        <div class="flex items-center justify-center group/thumbnail relative ml-[48px] w-[128px] h-[128px] -mt-[64px]">
                            <img
                                :src="thumbnailImageSource || group.thumbnail_url"
                                alt="Some thumbnail"
                                class="w-full h-full object-cover rounded-full"
                            >
                            <button v-if="isCurrentUserAdmin && !thumbnailImageSource"
                                    class="absolute left-0 top-0 right-0 bottom-0 bg-black/50 text-gray-200 flex items-center justify-center opacity-0 group-hover/thumbnail:opacity-100 transition-all rounded-full"
                            >
                                <CameraIcon class="w-8 h-8" />
                                <input
                                    type="file"
                                    class="absolute left-0 right-0 top-0 bottom-0 opacity-0"
                                    @change="onThumbnailChange"
                                />
                            </button>
                            <div v-else-if="isCurrentUserAdmin" class="absolute top-1 right-0 flex flex-col gap-2">
                                <button
                                    @click="resetThumbnailImageUpdate"
                                    class="w-7 h-7 flex items-center justify-center bg-red-500/80 text-white rounded-full"
                                >
                                    <XMarkIcon class="w-5 h-5" />
                                </button>
                                <button
                                    @click="submitImageUpdate(resetThumbnailImageUpdate)"
                                    class="w-7 h-7 flex items-center justify-center bg-emerald-500/80 text-white rounded-full"
                                >
                                    <CheckCircleIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-1 justify-between items-center p-4">
                            <h2 class="font-bold text-lg">{{ group.name }}</h2>
                            <PrimaryButton v-if="!authUser" :href="route('login')">
                                Login to join
                            </PrimaryButton>
                            <PrimaryButton v-if="isCurrentUserAdmin" @click="showInviteUserModal = true">
                                Invite users
                            </PrimaryButton>
                            <PrimaryButton v-if="authUser && !group.role && group.auto_approval" @click="joinToGroup">
                                Join the group
                            </PrimaryButton>
                            <PrimaryButton v-if="authUser && !group.role && !group.auto_approval" @click="joinToGroup">
                                Request to join
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 pt-0">
                <TabGroup>
                    <TabList class="pl-[48px] flex bg-white">
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Posts" :selected="selected" />
                        </Tab>
                        <Tab v-if="isInGroup" v-slot="{ selected }" as="template">
                            <TabItem text="Users" :selected="selected" />
                        </Tab>
                        <Tab v-if="isCurrentUserAdmin" v-slot="{ selected }" as="template">
                            <TabItem text="Pending requests" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Photos" :selected="selected" />
                        </Tab>
                    </TabList>
                    <TabPanels class="mt-2">
                        <TabPanel class="bg-white p-3 shadow">
                            Posts
                        </TabPanel>
                        <TabPanel v-if="isInGroup">
                            <div class="mb-3">
                                <TextInput :model-value="searchKeyword" placeholder="Type to search" class="w-full" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <UserListItem
                                    v-for="user in users"
                                    :user="user"
                                    :key="user.id"
                                    :show-role-dropdown="isCurrentUserAdmin"
                                    :disable-role-dropdown="group.user_id === user.id"
                                    class="shadow rounded-lg"
                                    @role-change="onRoleChange"
                                />
                            </div>
                        </TabPanel>
                        <TabPanel v-if="isCurrentUserAdmin">
                            <div v-if="requests.length" class="grid grid-cols-2 gap-3">
                                <UserListItem
                                    v-for="user in requests"
                                    :user="user"
                                    :key="user.id"
                                    :for-approve-or-reject="true"
                                    class="shadow rounded-lg"
                                    @approve="approveRequest(user.id)"
                                    @reject="rejectRequest(user.id)"
                                />
                            </div>
                            <div v-else class="py-8 text-center">
                                There are currently no pending requests
                            </div>
                        </TabPanel>
                        <TabPanel class="bg-white p-3 shadow">
                            Photos
                        </TabPanel>

                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>
    <InviteUserModal v-model="showInviteUserModal" />
</template>

<style scoped>

</style>
