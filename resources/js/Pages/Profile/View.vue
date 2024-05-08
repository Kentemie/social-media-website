<script setup>
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import { useForm, usePage } from "@inertiajs/vue3";
import { XMarkIcon, CheckCircleIcon, CameraIcon } from "@heroicons/vue/24/solid";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabItem from "@/Pages/Profile/Partials/TabItem.vue";
import Edit from "@/Pages/Profile/Edit.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

import { computed, ref, watch } from "vue";

const authUser = usePage().props.auth.user;
const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    success: {
        type: String,
    },
    user: {
        type: Object,
    },
    errors: {
        type: Object,
    },
});
const coverImageSource = ref("");
const avatarImageSource = ref("");
const showNotification = ref(false);
const imagesForm = useForm({
    cover: null,
    avatar: null,
});


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

function onAvatarChange(event) {
    imagesForm.avatar = event.target.files[0];

    if (imagesForm.avatar) {
        const reader = new FileReader();
        reader.onload = () => {
            avatarImageSource.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.avatar);
    }
}

function resetAvatarImageUpdate() {
    avatarImageSource.value = "";
    imagesForm.avatar = null;
}

function submitImageUpdate(resetFunction) {
    imagesForm.post(route("profile.updateImage"), {
        onSuccess: () => {
            resetFunction();
            showNotification.value = true;
        },
    });
}

watch(showNotification, (newValue) => {
    if (newValue) {
        setTimeout(() => {
            showNotification.value = false;
        }, 3000);
    }
})

const isMyProfile = computed(() => authUser && props.user.id === authUser.id);
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
                    :src="coverImageSource || user.cover_url || '/images/default_cover_image.jpg'"
                    alt="Some cover image"
                    class="w-full h-[200px] object-cover"
                >
                <div class="absolute top-2 right-2">
                    <button v-if="!coverImageSource" class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
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
                    <div class="flex items-center justify-center group/avatar relative ml-[48px] w-[128px] h-[128px] -mt-[64px]">
                        <img
                            :src="avatarImageSource || user.avatar_url || '/images/default_avatar_image.webp'"
                            alt="Some avatar"
                            class="w-full h-full object-cover rounded-full"
                        >
                        <button v-if="!avatarImageSource"
                                class="absolute left-0 top-0 right-0 bottom-0 bg-black/50 text-gray-200 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-all rounded-full"
                        >
                            <CameraIcon class="w-8 h-8" />
                            <input
                                type="file"
                                class="absolute left-0 right-0 top-0 bottom-0 opacity-0"
                                @change="onAvatarChange"
                            />
                        </button>
                        <div v-else class="absolute top-1 right-0 flex flex-col gap-2">
                            <button
                                @click="resetAvatarImageUpdate"
                                class="w-7 h-7 flex items-center justify-center bg-red-500/80 text-white rounded-full"
                            >
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                            <button
                                @click="submitImageUpdate(resetAvatarImageUpdate)"
                                class="w-7 h-7 flex items-center justify-center bg-emerald-500/80 text-white rounded-full"
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-1 justify-between items-center p-4">
                        <h2 class="font-bold text-lg">{{ user.name }}</h2>
                        <PrimaryButton  v-if="isMyProfile">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" />
                            </svg>
                            Edit profile
                        </PrimaryButton>
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
                        <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                            <TabItem text="My profile" :selected="selected" />
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
                        <TabPanel v-if="isMyProfile">
                            <Edit :must-verify-email="mustVerifyEmail" :status="status"/>
                        </TabPanel>
                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
