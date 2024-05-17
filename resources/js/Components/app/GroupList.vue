<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ChevronRightIcon } from "@heroicons/vue/24/solid";

import GroupListItems from "@/Components/app/GroupListItems.vue";


defineProps({
    groups: {
        type: Array,
    }
});

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
                    <GroupListItems :groups="groups" />
                </DisclosurePanel>
            </Disclosure>
        </div>
        <div class="h-full hidden lg:flex lg:flex-col">
            <div class="flex justify-between">
                <h2 class="text-xl font-bold">My groups</h2>
            </div>
            <GroupListItems :groups="groups" />
        </div>
    </div>
</template>
