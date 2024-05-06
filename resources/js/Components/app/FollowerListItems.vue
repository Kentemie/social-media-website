<script setup>
import TextInput from "@/Components/TextInput.vue";
import FollowerItem from "@/Components/app/FollowerItem.vue";
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
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Jon Snow"
            />
            <FollowerItem image="https://picsum.photos/100"
                          title="Daenerys Targaryen"
            />

        </div>
    </div>
</template>

<style scoped>

</style>
