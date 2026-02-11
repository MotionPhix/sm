/**
 * FilePond configuration composable
 *
 * Provides centralized configuration and initialization for FilePond file uploads
 */

import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';

/**
 * Get all FilePond plugins that should be registered
 */
export function getFilePondPlugins() {
    return [
        FilePondPluginImagePreview,
        FilePondPluginImageCrop,
        FilePondPluginImageTransform,
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize,
        FilePondPluginImageEdit,
    ];
}

/**
 * Convert file size string (e.g., '10MB', '5GB') to bytes
 *
 * @param size - Size string with unit (KB, MB, GB)
 * @returns Size in bytes
 */
export function parseFileSize(size: string): number {
    const units: Record<string, number> = {
        B: 1,
        KB: 1024,
        MB: 1024 * 1024,
        GB: 1024 * 1024 * 1024,
    };

    const match = size.match(/^(\d+(?:\.\d+)?)\s*([A-Z]+)$/i);
    if (!match) {
        return 0;
    }

    const [, value, unit] = match;
    const multiplier = units[unit.toUpperCase()] || 1;

    return parseFloat(value) * multiplier;
}

/**
 * Get default FilePond options
 *
 * @param accept - Accepted file types
 * @param maxFileSize - Maximum file size (e.g., '10MB')
 * @param maxFiles - Maximum number of files
 * @param allowImageEdit - Enable image editing
 * @returns FilePond options object
 */
export function getDefaultFilePondOptions(
    accept: string | string[] = ['image/*'],
    maxFileSize: string = '10MB',
    maxFiles: number = 1,
    allowImageEdit: boolean = true
) {
    const acceptedTypes = Array.isArray(accept) ? accept : [accept];

    return {
        name: 'file',
        allowMultiple: maxFiles > 1,
        maxFiles,
        maxFileSize: parseFileSize(maxFileSize),
        acceptedFileTypes: acceptedTypes,
        labelIdle: `Drag & Drop your file${maxFiles > 1 ? 's' : ''} or <span class="filepond--label-action">Browse</span>`,
        labelFileProcessing: 'Uploading',
        labelFileProcessingComplete: 'Upload complete',
        labelFileProcessingAborted: 'Upload cancelled',
        labelFileProcessingError: 'Error during upload',
        labelTapToCancel: 'tap to cancel',
        labelTapToRetry: 'tap to retry',
        labelTapToUndo: 'tap to undo',
        labelButtonRemoveItem: 'Remove',
        labelButtonAbortItemLoad: 'Abort',
        labelButtonRetryItemLoad: 'Retry',
        labelButtonAbortItemProcessing: 'Cancel',
        labelButtonUndoItemProcessing: 'Undo',
        labelButtonRetryItemProcessing: 'Retry',
        labelButtonProcessItem: 'Upload',
        allowImagePreview: true,
        imagePreviewHeight: 256,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 1024,
        imageResizeTargetHeight: 1024,
        imageResizeMode: 'contain',
        imageResizeUpscale: false,
        stylePanelLayout: 'compact',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        // Only enable image editing for image files
        allowImageEdit: allowImageEdit && acceptedTypes.some(type => type.startsWith('image')),
        imageEditIconEdit: '<svg width="26" height="26" viewBox="0 0 26 26" xmlns="http://www.w3.org/2000/svg"><path d="m13.9 18.7-5.4 1.2 1.2-5.4 9.3-9.3c.4-.4 1-.4 1.4 0l2.8 2.8c.4.4.4 1 0 1.4z" fill="currentColor" fill-rule="nonzero"/></svg>',
    };
}

/**
 * Use FilePond composable
 *
 * @example
 * const { getPlugins, getOptions, parseSize } = useFilePond()
 * const plugins = getPlugins()
 * const options = getOptions(['image/*'], '10MB', 1, true)
 */
export function useFilePond() {
    return {
        getPlugins: getFilePondPlugins,
        getOptions: getDefaultFilePondOptions,
        parseSize: parseFileSize,
    };
}
