<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import InputTextarea from "@/Components/InputTextarea.vue";

const postCreating = ref(false);
const newPostForm = useForm({
    body: ''
})

function submitPostCreation() {
    newPostForm.post(route('post.store'), {
        onSuccess() {
            newPostForm.reset();
        }
    });
}
</script>

<template>
    <div class="p-4 bg-white rounded-lg border shadow mb-3">
        <InputTextarea
            @click="postCreating = !postCreating"
            placeholder="Click here to create a new post"
            class="mb-3 w-full"
            v-model="newPostForm.body"
            rows="1"
        />
        <div v-if="postCreating" class="flex gap-2 justify-between">
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 relative">
                Attach files
                <input type="file" class="absolute top-0 bottom-0 left-0 right-0 opacity-0">
            </button>
            <button @click="submitPostCreation" type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Submit
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>
