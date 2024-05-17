<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ChevronRightIcon } from "@heroicons/vue/24/solid";
import { ref } from "vue";

import GroupListItems from "@/Components/app/GroupListItems.vue";
import GroupModal from "@/Components/app/GroupModal.vue";


const showNewGroupModal = ref(false)

</script>

<template>
    <div class="px-3 bg-white rounded border shadow py-3 h-full">
        <div class="block lg:hidden">
            <Disclosure v-slot="{ open }">
                <DisclosureButton class="w-full">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">My groups</h2>
                        <ChevronRightIcon class="w-5 h-5 transition-all" :class="open ? 'rotate-90 transform' : ''" />
                    </div>
                </DisclosureButton>
                <DisclosurePanel>
                    <button @click="showNewGroupModal = true" class="text-sm bg-indigo-500 hover:bg-indigo-600 text-white rounded py-1 px-2">
                        new group
                    </button>
                    <GroupListItems />
                </DisclosurePanel>
            </Disclosure>
        </div>
        <div class="h-full hidden lg:flex lg:flex-col">
            <div class="flex justify-between">
                <h2 class="text-xl font-bold">My groups</h2>
                <button @click="showNewGroupModal = true" class="text-sm bg-indigo-500 hover:bg-indigo-600 text-white rounded py-1 px-2">
                    new group
                </button>
            </div>
            <GroupListItems />
        </div>
    </div>
    <GroupModal v-model="showNewGroupModal" />
</template>
