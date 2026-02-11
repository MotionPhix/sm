<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import vueFilePond from 'vue-filepond';
import { useFilePond } from '@/composables/useFilePond';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import type { MediaFile } from '@/types/filepond';

// Register FilePond with plugins
const FilePond = vueFilePond(
    ...useFilePond().getPlugins()
);

interface Props {
    modelValue?: File | File[] | null;
    accept?: string | string[];
    maxFiles?: number;
    maxFileSize?: string;
    existingMedia?: MediaFile[];
    collectionName?: string;
    label?: string;
    disabled?: boolean;
    required?: boolean;
    allowImageEdit?: boolean;
    imagePreviewHeight?: number;
    error?: string;
}

const props = withDefaults(defineProps<Props>(), {
    accept: () => ['image/*'],
    maxFiles: 1,
    maxFileSize: '10MB',
    existingMedia: () => [],
    collectionName: 'default',
    label: '',
    disabled: false,
    required: false,
    allowImageEdit: true,
    imagePreviewHeight: 256,
    error: '',
});

interface Emits {
    (e: 'update:modelValue', value: File | File[] | null): void;
    (e: 'files-added', files: File[]): void;
    (e: 'file-removed', file: File): void;
    (e: 'media-deleted', mediaId: number): void;
    (e: 'validation-error', error: string): void;
}

const emit = defineEmits<Emits>();

const pond = ref<InstanceType<typeof FilePond> | null>(null);
const deletedMediaIds = ref<number[]>([]);
const isInitialized = ref(false);
const fileSizeError = ref<string>('');

// Handle FilePond warnings (file size exceeded, etc.)
const handleWarning = (error: any) => {
    if (error && error.body) {
        fileSizeError.value = error.body;
        emit('validation-error', error.body);
    }
};

// Server config with load function that fetches images from URL
const serverConfig = {
    load: (source: string, load: (file: Blob) => void, error: (message: string) => void, progress: (isComputable: boolean, loaded: number, total: number) => void, abort: () => void, headers: (headers: Record<string, string>) => void) => {
        const request = new Request(source);
        fetch(request)
            .then((res) => {
                if (!res.ok) throw new Error('Failed to load');
                return res.blob();
            })
            .then(load)
            .catch(() => error('Could not load file'));
    },
    process: null,
    revert: null,
    restore: null,
    fetch: null,
};

// Get FilePond options from composable
const filePondOptions = computed(() => {
    return useFilePond().getOptions(
        props.accept,
        props.maxFileSize,
        props.maxFiles,
        props.allowImageEdit
    );
});

// Set files after FilePond is mounted
onMounted(() => {
    if (props.existingMedia && props.existingMedia.length > 0 && pond.value) {
        // Wait for next tick to ensure FilePond is ready
        setTimeout(() => {
            loadExistingFiles();
        }, 100);
    }
});

// Load existing files into FilePond
const loadExistingFiles = () => {
    if (!pond.value || isInitialized.value) return;
    
    const files = props.existingMedia
        .filter(m => m.id && m.url)
        .map(m => ({
            source: m.url,
            options: {
                type: 'local',
            },
        }));

    if (files.length > 0) {
        // Use the FilePond method to add files
        (pond.value as any).addFiles(files.map(f => ({
            source: f.source,
            options: f.options,
        })));
        isInitialized.value = true;
    }
};

// Watch for changes in existingMedia prop
watch(
    () => props.existingMedia,
    (newMedia) => {
        if (newMedia && newMedia.length > 0 && pond.value && !isInitialized.value) {
            loadExistingFiles();
        }
    },
    { deep: true }
);

// Track existing media IDs for removal detection
const existingMediaIds = computed(() => props.existingMedia.map(m => m.id));

// Handle file added
const handleAddFile = (error: any, file: any) => {
    // Clear any previous file size error when a new file is successfully added
    if (!error) {
        fileSizeError.value = '';
    }
    
    if (error) {
        // Handle file size validation error
        if (error.main && error.sub) {
            fileSizeError.value = `${error.main}: ${error.sub}`;
            emit('validation-error', fileSizeError.value);
        } else {
            emit('validation-error', error.message || error.body || 'Error adding file');
        }
        return;
    }

    // Skip if this is an existing file being loaded (has URL source)
    if (file.source && typeof file.source === 'string' && file.source.startsWith('http')) {
        return;
    }

    const fileObj = file.file as File;

    if (props.maxFiles === 1) {
        emit('update:modelValue', fileObj);
        emit('files-added', [fileObj]);
    } else {
        emit('files-added', [fileObj]);
    }
};

// Handle file removed
const handleRemoveFile = (error: any, file: any) => {
    if (error) {
        return;
    }

    // Check if this is an existing media file (loaded from URL)
    if (file.source && typeof file.source === 'string' && file.source.startsWith('http')) {
        // Find the media ID from the existingMedia prop by matching URL
        const media = props.existingMedia.find(m => m.url === file.source);
        if (media) {
            deletedMediaIds.value.push(media.id);
            emit('media-deleted', media.id);
        }
        return;
    }

    const fileObj = file.file as File;
    emit('file-removed', fileObj);

    if (props.maxFiles === 1) {
        emit('update:modelValue', null);
    }
};

// Handle file processing (we don't auto-upload, just track state)
const handleProcessFile = (error: any, file: any) => {
    if (error) {
        emit('validation-error', error.message || 'Error processing file');
    }
};

// Update FilePond files when modelValue changes externally
watch(() => props.modelValue, (newValue) => {
    if (!newValue && pond.value) {
        // Don't clear if we have existing media files
        if (props.existingMedia.length === 0) {
            pond.value.removeFiles();
        }
    }
});
</script>

<template>
    <div class="grid gap-2">
        <Label 
            v-if="label" 
            :for="`filepond-${collectionName}`" 
            :class="{ 'after:content-[\'*\'] after:ml-0.5 after:text-destructive': required }">
            {{ label }}
        </Label>

        <FilePond
            :id="`filepond-${collectionName}`"
            ref="pond" credits="false"
            v-bind="filePondOptions"
            :server="serverConfig"
            :disabled="disabled"
            :image-preview-height="imagePreviewHeight"
            :allow-revert="false"
            :allow-process="false"
            :instant-upload="false"
            @addfile="handleAddFile"
            @removefile="handleRemoveFile"
            @processfile="handleProcessFile"
            @warning="handleWarning"
        />

        <InputError v-if="error || fileSizeError" class="mt-2" :message="error || fileSizeError" />
    </div>
</template>