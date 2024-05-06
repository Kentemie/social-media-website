<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import FollowerListItems from "@/Components/app/FollowerListItems.vue";
</script>

<template>
    <div class="px-3 bg-white rounded border shadow py-3 h-full">
        <div class="block lg:hidden">
            <Disclosure v-slot="{ open }">
                <DisclosureButton class="w-full">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">
                            My friends
                        </h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 transition-all" :class="open ? 'rotate-90 transform' : ''">
                            <path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </DisclosureButton>
                <DisclosurePanel>
                    <FollowerListItems />
                </DisclosurePanel>
            </Disclosure>
        </div>
        <div class="h-full hidden lg:flex lg:flex-col">
            <h2 class="text-xl font-bold text-center">
                My friends
            </h2>
            <FollowerListItems />
        </div>
    </div>
</template>

<style scoped>

</style>
