<script setup>
import TextInput from "@/Components/TextInput.vue";
import UserListItem from "@/Components/app/UserListItem.vue";
import { ref } from "vue";

const searchKeyword = ref('');
</script>

<template>
    <TextInput :model-value="searchKeyword" placeholder="Type to search" class="w-full mt-4" />
    <div class="mt-3 h-[300px] lg:flex-1 overflow-auto">
        <div v-if="false" class="text-gray-400 text-center p-3">
            You have no friends, you are a lonely pathetic slug
        </div>
        <div v-else>

        </div>
    </div>
</template>

<style scoped>

</style>
