<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

defineProps({
    placeholder: String
})

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });

function onInputChange() {
    input.value.style.height = 'auto';

    const currentHeight = input.value.scrollHeight;

    const maxHeight = 300; // Примерное значение

    if (currentHeight <= maxHeight) {
        input.value.style.height = currentHeight + 'px';
    } else {
        input.value.style.height = maxHeight + 'px';
    }
}
</script>

<template>
    <textarea
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        v-model="model"
        ref="input"
        :placeholder="placeholder"
        @input="onInputChange"
    ></textarea>
</template>
