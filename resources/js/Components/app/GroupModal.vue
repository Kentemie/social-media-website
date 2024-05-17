<script setup>
import { computed, ref } from "vue";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import {
    XMarkIcon,
    BookmarkIcon,
} from "@heroicons/vue/24/solid";
import { useForm } from "@inertiajs/vue3";

import axiosClient from "@/axiosClient.js";

import IndigoButton from "@/Components/IndigoButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputTextarea from "@/Components/InputTextarea.vue";


const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(
    [
        "update:modelValue",
        "hide"
    ],
);


const form = useForm({
    name: "",
    description: "",
    auto_approval: true,
});


const formErrors = ref({});

const show = computed({
    get() { return props.modelValue; },
    set(value) { emit("update:modelValue", value); }
});


function closeModal() {
    show.value = false;
    emit("hide");
    resetModal();
}

function resetModal() {
    form.reset();
    formErrors.value = {};
}

function submit() {
    axiosClient.post(route("group.store"), form)
        .then(() => {
            closeModal();
        });
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
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded bg-white text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3" class="flex items-center justify-between py-3 px-4 font-medium bg-gray-100 text-gray-900">
                                    Create a new group

                                    <button @click="closeModal" class="w-8 h-8 rounded-full hover:bg-black/10 transition flex items-center justify-center">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </DialogTitle>

                                <div class="p-4">
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
                                </div>

                                <div class="flex gap-2 py-3 px-4 justify-end">
                                    <button class="text-gray-800 flex items-center justify-center rounded-md py-2 px-3 bg-gray-100 hover:bg-gray-200">
                                        <XMarkIcon class="w-4 h-4 mr-2" />
                                        Cancel
                                    </button>
                                    <IndigoButton @click="submit">
                                        <BookmarkIcon class="w-4 h-4 mr-2" />
                                        Submit
                                    </IndigoButton>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </teleport>
</template>
