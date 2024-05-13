<script setup>

import { onMounted, ref, watch } from 'vue';


const model = defineModel({
    type: String,
    required: false,
});
const props = defineProps({
    placeholder: {
        type: String,
    },
    autoResize: {
        type: Boolean,
        default: true,
    },
})
defineExpose({ focus: () => input.value.focus() });


const input = ref(null);

watch(() => model.value, () => {
    setTimeout(() => {
        adjustHeight();
    }, 10)
})

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
    adjustHeight();
});


function adjustHeight() {
    if (props.autoResize) {
        input.value.style.height = 'auto';
        input.value.style.height = (input.value.scrollHeight + 2) + 'px';
    }
}

</script>

<template>
    <textarea
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        v-model="model"
        ref="input"
        :placeholder="placeholder"
    ></textarea>
</template>
