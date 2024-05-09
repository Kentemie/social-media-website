<script setup>

import { onMounted, ref } from 'vue';


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


onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
    adjustHeight();
});


function onInputChange() {
    input.value.style.height = 'auto';
    adjustHeight();
}

function adjustHeight() {
    if (props.autoResize) {
        const currentHeight = input.value.scrollHeight;

        const maxHeight = 300;

        if (currentHeight > maxHeight) {
            input.value.style.height = maxHeight + 'px';
        }
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
