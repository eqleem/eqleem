import { defineAsyncComponent } from 'vue';

export const CkEditor = defineAsyncComponent(() => import('./CkEditor.vue'));
export const MediaGallery = defineAsyncComponent(() => import('./MediaGallery.vue'));
export const FileGallery = defineAsyncComponent(() => import('./FileGallery.vue'));
