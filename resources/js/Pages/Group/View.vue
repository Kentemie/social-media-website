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


const authUser = usePage().props.auth.user;

const imagesForm = useForm({
    cover: null,
    thumbnail: null,
});


const props = defineProps({
    success: {
        type: String,
    },
    group: {
        type: Object,
    },
    errors: {
        type: Object,
    },
});


const coverImageSource = ref("");
const thumbnailImageSource = ref("");
const showNotification = ref(true);
const showInviteUserModal = ref(false);

const isCurrentUserAdmin = computed(() => props.group.role === "admin");


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
        onSuccess: () => {
            resetFunction();
            showNotification.value = true;
            setTimeout(() => {
                showNotification.value = false;
            }, 3000);
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <div class="max-w-[1000px] mx-auto h-full overflow-auto">
            <div
                v-show="showNotification && success"
                class="my-2 py-2 px-3 font-medium text-sm bg-emerald-500 text-white"
            >
                {{ success }}
            </div>
            <div
                v-if="errors.cover"
                class="my-2 py-2 px-3 font-medium text-sm bg-red-500 text-white"
            >
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

                        <PrimaryButton @click="showInviteUserModal = true" v-if="isCurrentUserAdmin">Invite users</PrimaryButton>
                        <PrimaryButton v-if="!group.role && group.auto_approval">Join the group</PrimaryButton>
                        <PrimaryButton v-if="!group.role && !group.auto_approval">Request to join</PrimaryButton>
                    </div>
                </div>
            </div>
            <div>
                <TabGroup>
                    <TabList class="pl-[48px] flex bg-white">
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Posts" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Followers" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Friends" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Photos" :selected="selected" />
                        </Tab>

                    </TabList>
                    <TabPanels class="mt-2">
                        <TabPanel class="bg-white p-3 shadow">
                            Posts
                        </TabPanel>
                        <TabPanel class="bg-white p-3 shadow">
                            Followers
                        </TabPanel>
                        <TabPanel class="bg-white p-3 shadow">
                            Friends
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
