<script setup>
import { computed, watch, ref } from "vue";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import {
    XMarkIcon,
    PaperClipIcon,
    BookmarkIcon,
    ArrowUturnLeftIcon,
} from "@heroicons/vue/24/solid";
import { useForm, usePage } from "@inertiajs/vue3";
import { readFile, isImage } from "@/helpers.js";

import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import IndigoButton from "@/Components/IndigoButton.vue";


const editor = ClassicEditor;
const editorConfig = {
    toolbar: [
        "bold", "italic", "|", "bulletedList", "numberedList", "|", "heading", "|", "outdent", "indent", "|", "link", "|", "blockQuote"
    ]
};


const attachmentExtensions = usePage().props.attachmentExtensions;


const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: Boolean,
        default: false,
    },
});
const emit = defineEmits(
    ["update:modelValue", "hide"],
);

const attachmentFiles = ref([]);
const attachmentErrors = ref([]);
const formErrors = ref({});

const show = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    }
});
const attachments = computed(() => {
   return [...attachmentFiles.value, ...(props.post.attachments || [])];
});
const showExtensionsText = computed(() => {
    for (const myFile of attachmentFiles.value) {
        const file = myFile.file;
        const ext = file.name.split(".").pop().toLowerCase();
        if (!attachmentExtensions.includes(ext)) {
            return true;
        }
    }
    return false;
});

watch(() => props.post, () => {
    form.body = props.post.body ?? "";
}, {
    deep: true,
});


const form = useForm({
    body: "",
    attachments: [],
    deleted_attachment_ids: [],
    _method: "POST",
});


function closeModal() {
    show.value = false;
    emit("hide");
    resetModal();
}

function resetModal() {
    form.reset();
    attachmentFiles.value = [];
    attachmentErrors.value = [];
    formErrors.value = {};
    if (props.post.attachments) {
        props.post.attachments.forEach(attachment => attachment.deleted = false);
    }
}

async function onAttachmentChoose(event) {
    for (const file of event.target.files) {
        const myFile = {
            file,
            url: await readFile(file),
        };
        attachmentFiles.value.push(myFile);
    }
    event.target.value = null;
}

function removeFile(myFile) {
    if (myFile.file) {
        attachmentFiles.value = attachmentFiles.value.filter(file => file !== myFile);
    } else {
        form.deleted_attachment_ids.push(myFile.id);
        myFile.deleted = true;
    }
}

function undoFileRemoval(myFile) {
    myFile.deleted = false;
    form.deleted_attachment_ids = form.deleted_attachment_ids.filter(id => myFile.id !== id);
}

function processErrors(errors) {
    formErrors.value = errors
    for (const key in errors) {
        if (key.includes(".")) {
            const [, index] = key.split(".");
            attachmentErrors.value[index] = errors[key];
        }
    }
}

function submit() {
    form.attachments = attachmentFiles.value.map(myFile => myFile.file);
    if (props.post.id) {
        form._method = "PUT";
        form.post(route("post.update", props.post.id), {
            preserveScroll: true,
            onSuccess() {
                closeModal();
            },
            onError(errors) {
                processErrors(errors);
            }
        });
    } else {
        form.post(route("post.store"), {
            preserveScroll: true,
            onSuccess() {
                closeModal();
            },
            onError(errors) {
                processErrors(errors);
            }
        });
    }
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
                        class="flex min-h-full items-center justify-center p-4 text-center"
                    >
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded bg-white text-left align-middle shadow-xl transition-all"
                            >
                                <DialogTitle
                                    as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium bg-gray-100 text-gray-900"
                                >
                                    {{ post.id ? "Update selected post" : "Create a new post" }}
                                    <button
                                        @click="closeModal"
                                        class="w-8 h-8 rounded-full hover:bg-black/10 transition flex items-center justify-center">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </DialogTitle>
                                <div class="p-4">
                                    <PostUserHeader :post="post" :show-time="false" class="mb-4" />
                                    <ckeditor :editor="editor" v-model="form.body" :config="editorConfig"></ckeditor>
                                    <div
                                        v-if="showExtensionsText"
                                        class="border-l-4 border-amber-500 py-2 px-3 mt-3 bg-amber-100 text-gray-800"
                                    >
                                        Files must have one of the following extensions: <br>
                                        <small>
                                            {{ attachmentExtensions.join(", ") }}
                                        </small>
                                    </div>

                                    <div
                                        v-if="formErrors.attachments"
                                        class="border-l-4 border-red-500 py-2 px-3 mt-3 bg-red-100 text-gray-800"
                                    >
                                        <small>
                                            {{ formErrors.attachments }}
                                        </small>
                                    </div>

                                    <div v-if="attachments.length !== 0" class="grid gap-3 my-3" :class="[
                                        attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2 lg:grid-cols-3'
                                    ]">
                                        <div v-for="(myFile, idx) in attachments">
                                            <div
                                                class="group aspect-square bg-blue-100 flex flex-col items-center justify-center text-gray-500 relative border-2"
                                                :class="attachmentErrors[idx] ? 'border-red-500' : ''"
                                            >
                                                <div
                                                    v-if="myFile.deleted"
                                                    class="absolute z-10 left-0 right-0 bottom-0 py-2 px-3 text-sm bg-red-900 text-white flex justify-between items-center"
                                                >
                                                    To be deleted
                                                    <ArrowUturnLeftIcon
                                                        @click="undoFileRemoval(myFile)"
                                                        class="w-4 h-4 cursor-pointer"
                                                    />
                                                </div>
                                                <button
                                                    @click="removeFile(myFile)"
                                                    class="absolute z-20 right-3 top-3 w-7 h-7 flex items-center justify-center bg-black/30 text-white rounded-full"
                                                    :class="myFile.deleted ? '' : 'hover:bg-black/40'"
                                                    :disabled="myFile.deleted"
                                                >
                                                    <XMarkIcon class="w-5 h-5" />
                                                </button>
                                                <img v-if="isImage(myFile.file || myFile)"
                                                     :src="myFile.url"
                                                     alt="Some image"
                                                     class="object-contain aspect-square"
                                                     :class="myFile.deleted ? 'opacity-50' : ''"
                                                />
                                                <div
                                                    v-else
                                                    class="flex flex-col justify-center items-center px-3"
                                                    :class="myFile.deleted ? 'opacity-50' : ''"
                                                >
                                                    <PaperClipIcon class="w-10 h-10 mb-3" />
                                                    <small class="text-center">
                                                        {{ (myFile.file || myFile).name }}
                                                    </small>
                                                </div>
                                            </div>
                                            <small class="text-red-500">
                                                {{ attachmentErrors[idx] }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2 py-3 px-4">
                                    <IndigoButton class="w-full">
                                        <PaperClipIcon class="w-4 h-4 mr-2" />
                                        Attach files
                                        <input @click.stop @change="onAttachmentChoose" type="file" multiple class="absolute left-0 right-0 top-0 bottom-0 opacity-0">
                                    </IndigoButton>
                                    <IndigoButton
                                        @click="submit"
                                        class="w-full"
                                    >
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
