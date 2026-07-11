<script setup>
import BlockLinksPanel from './BlockLinksPanel.vue';

defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

defineEmits(['saved', 'close']);
</script>

<template>
    <BlockLinksPanel
        :block-id="blockId"
        :editor="editor"
        content-type="footer-link"
        show-settings
        @close="$emit('close')"
        @saved="$emit('saved', $event)"
    />
</template>
