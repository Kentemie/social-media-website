<script setup>
import PostItem from "@/Components/app/PostItem.vue";

defineProps({
    posts: {
        type: Array,
    }
});
</script>

<template>
    <div class="overflow-auto">
        <PostItem v-for="post in posts" :key="post.id" :post="post" />
    </div>
</template>

<style scoped>

</style>
