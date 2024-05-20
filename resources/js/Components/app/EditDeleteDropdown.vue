<script setup>
import { EllipsisVerticalIcon, PencilIcon, TrashIcon } from "@heroicons/vue/24/solid/index.js";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";


const page = usePage();
const authUser = page.props.auth.user;


defineEmits(["edit", "delete"]);
const props = defineProps({
    post: {
        type: Object,
        default: null,
    },
    comment: {
        type: Object,
        default: null,
    },
});


const user = computed(() => {
    return props.comment?.user || props.post?.user;
});
const editAllowed = computed(() => {
    return user.value.id === authUser.id;
});
const deleteAllowed = computed(() => {
    if (user.value.id === authUser.id) return true;
    if (props.post.user.id === authUser.id) return true;
    return !props.comment && props.post.group?.role === "admin";
});
</script>

<template>
    <Menu
        v-show="deleteAllowed"
        as="div"
        class="relative inline-block text-left"
    >
        <div>
            <MenuButton
                class="w-8 h-8 rounded-full hover:bg-black/10 transition flex items-center justify-center z-10"
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
                class="absolute z-20 right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
            >
                <div class="px-1 py-1">
                    <MenuItem v-if="editAllowed" v-slot="{ active }">
                        <button
                            @click="$emit('edit')"
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
                            @click="$emit('delete')"
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
</template>

<style scoped>

</style>
