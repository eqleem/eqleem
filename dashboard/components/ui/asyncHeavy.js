import { defineAsyncComponent } from 'vue';

export const CkEditor = defineAsyncComponent(() => import('./CkEditor.vue'));
export const MediaGallery = defineAsyncComponent(() => import('./MediaGallery.vue'));
export const FileGallery = defineAsyncComponent(() => import('./FileGallery.vue'));
export const FileCrop = defineAsyncComponent(() => import('./FileCrop.vue'));
export const UploadCover = defineAsyncComponent(() => import('./UploadCover.vue'));
export const BrandMarkField = defineAsyncComponent(() => import('./BrandMarkField.vue'));
export const CountrySelect = defineAsyncComponent(() => import('./CountrySelect.vue'));
