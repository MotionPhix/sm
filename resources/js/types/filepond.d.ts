/**
 * FilePond TypeScript type definitions for FileUploader component
 */

export interface MediaFile {
    id: number;
    url: string;
    name: string;
    size?: number;
    type?: string;
}

export interface FileUploaderProps {
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
    instantUpload?: boolean;
}

export interface FileUploaderEmits {
    (e: 'update:modelValue', value: File | File[] | null): void;
    (e: 'files-added', files: File[]): void;
    (e: 'file-removed', file: File): void;
    (e: 'media-deleted', mediaId: number): void;
    (e: 'validation-error', error: string): void;
}

export interface FilePondServerConfig {
    process?: (
        fieldName: string,
        file: File,
        metadata: Record<string, unknown>,
        load: (uniqueFileId: string) => void,
        error: (errorText: string) => void,
        progress: (lengthComputable: boolean, loadedBytes: number, totalBytes: number) => void,
        abort: () => void
    ) => void;
    revert?: (uniqueFileId: string, load: () => void, error: (errorText: string) => void) => void;
    load?: (source: string, load: (file: Blob) => void, error: (errorText: string) => void, progress: (lengthComputable: boolean, loadedBytes: number, totalBytes: number) => void, abort: () => void, headers: () => Record<string, string>) => void;
}
