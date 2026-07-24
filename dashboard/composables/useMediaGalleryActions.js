import { ref } from 'vue';
import { ApiError } from '../lib/api.js';

/**
 * Gallery upload / reorder / remove helpers for content Detail pages.
 *
 * @param {{
 *   uuid: import('vue').Ref<string> | import('vue').ComputedRef<string>,
 *   form: { images: any[] },
 *   errors: { form: string | null },
 *   uploadImage: (uuid: string, file: File) => Promise<any[]>,
 *   reorderImages: (uuid: string, order: any) => Promise<any[]>,
 *   deleteImage: (uuid: string, mediaId: any) => Promise<any[]>,
 *   fallbackImages?: () => any[],
 * }} options
 */
export function useMediaGalleryActions({
    uuid,
    form,
    errors,
    uploadImage,
    reorderImages,
    deleteImage,
    fallbackImages = () => [],
}) {
    const uploading = ref(false);

    async function uploadFiles(files) {
        uploading.value = true;

        try {
            for (const file of files) {
                form.images = await uploadImage(uuid.value, file);
            }
        } catch (error) {
            errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الصورة.';
        } finally {
            uploading.value = false;
        }
    }

    async function reorder(order) {
        try {
            form.images = await reorderImages(uuid.value, order);
        } catch (error) {
            errors.form = error instanceof ApiError ? error.message : 'تعذر إعادة ترتيب الصور.';
            form.images = [...fallbackImages()];
        }
    }

    async function remove(mediaId) {
        try {
            form.images = await deleteImage(uuid.value, mediaId);
        } catch (error) {
            errors.form = error instanceof ApiError ? error.message : 'تعذر حذف الصورة.';
        }
    }

    return {
        uploading,
        uploadFiles,
        reorderImages: reorder,
        removeImage: remove,
    };
}
