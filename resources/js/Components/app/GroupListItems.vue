<script setup>
import { ref } from "vue";

import GroupModal from "@/Components/app/GroupModal.vue";
import GroupItem from "@/Components/app/GroupItem.vue";
import TextInput from "@/Components/TextInput.vue";

const searchKeyword = ref("");
const showNewGroupModal = ref(false)


const props = defineProps({
    groups: {
        type: Array,
    }
});


function onGroupCreate(group) {
    props.groups.unshift(group);
}

</script>

<template>
    <div class="flex gap-2 mt-4">
        <TextInput :model-value="searchKeyword" placeholder="Type to search" class="w-full" />
        <button @click="showNewGroupModal = true" class="text-sm bg-indigo-500 hover:bg-indigo-600 text-white rounded py-1 px-2">
            new group
        </button>
    </div>
    <div class="mt-3 h-[300px] lg:flex-1 overflow-auto">
        <div v-if="false" class="text-gray-400 text-center p-3">
            You are not a member of any group and never will be!
        </div>
        <div v-else>
            <GroupItem v-for="group in groups" :key="group.id" :group="group" />
        </div>
    </div>
    <GroupModal @create="onGroupCreate" v-model="showNewGroupModal" />
</template>
