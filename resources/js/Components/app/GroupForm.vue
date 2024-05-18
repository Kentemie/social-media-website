<script setup>
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputTextarea from "@/Components/InputTextarea.vue";


defineProps({
    form: Object
});
</script>

<template>
    <div class="mb-3">
        <label>Group name</label>
        <TextInput
            type="text"
            class="mt-1 block w-full"
            v-model="form.name"
            required
            autofocus
        />
    </div>
    <div class="mb-3">
        <label>
            Enable automatic approval
            <Checkbox
                v-model:checked="form.auto_approval"
                name="remember"
            />
        </label>
    </div>
    <div class="mb-3">
        <label>Group description</label>
        <InputTextarea
            v-model="form.description"
            class="w-full"
        />
    </div>
</template>
