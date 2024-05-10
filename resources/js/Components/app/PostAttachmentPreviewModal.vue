<script setup>

import { computed } from 'vue';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';
import {
    XMarkIcon,
    DocumentIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/solid';
import { isImage } from "@/helpers.js";


const props = defineProps({
    attachments: {
        type: Array,
        required: true,
    },
    index: {
        type: Number,
        default: 0,
    },
    modelValue: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(
    [
        'update:modelValue',
        'update:index',
        'hide'
    ],
);


const show = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});
const currentIndex = computed({
    get() {
        return props.index;
    },
    set(value) {
        emit('update:index', value);
    }
});
const attachment = computed(() => {
   return props.attachments[currentIndex.value];
});


function closeModal() {
    show.value = false;
    emit('hide');
}

function prev() {
    if (currentIndex.value === 0) return;
    currentIndex.value--;
}

function next() {
    if (currentIndex.value === props.attachments.length - 1) return;
    currentIndex.value++;
}

</script>

<template>
    <teleport to="body">
        <TransitionRoot appear :show="show" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/25" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div
                        class="h-screen w-screen"
                    >
                        <TransitionChild
                            as="template"
                            class="w-full h-full"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full transform overflow-hidden bg-slate-800 text-left align-middle shadow-xl transition-all"
                            >
                                <button
                                    @click="closeModal"
                                    class="absolute right-1 top-1 z-30 w-10 h-10 rounded-full hover:bg-black/20 transition flex items-center justify-center text-gray-100">
                                    <XMarkIcon class="w-6 h-6" />
                                </button>
                                <div class="relative group h-full">
                                    <div
                                        @click="prev"
                                        class="absolute opacity-0 group-hover:opacity-100 text-gray-100 cursor-pointer flex items-center w-12 h-full left-0 bg-black/5"
                                    >
                                        <ChevronLeftIcon class="w-12" />
                                    </div>
                                    <div
                                        @click="next"
                                        class="absolute opacity-0 group-hover:opacity-100 text-gray-100 cursor-pointer flex items-center w-12 h-full right-0 bg-black/5"
                                    >
                                        <ChevronRightIcon class="w-12" />
                                    </div>
                                    <div class="w-full h-full p-3 flex items-center justify-center">
                                        <img v-if="isImage(attachment)"
                                             :src="attachment.url"
                                             alt="Some image"
                                             class="max-h-full max-w-full"
                                        />
                                        <div v-else class="p-32 flex text-gray-100 flex-col justify-center items-center px-3">
                                            <DocumentIcon class="w-12 h-12" />
                                            <small>{{ attachment.name }}</small>
                                        </div>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </teleport>
</template>
