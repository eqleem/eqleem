<script setup>
import { computed } from 'vue';
import TopNavEditor from './TopNavEditor.vue';
import FloatLinksEditor from './FloatLinksEditor.vue';
import HeaderEditor from './HeaderEditor.vue';
import FooterEditor from './FooterEditor.vue';
import CtaEditor from './CtaEditor.vue';
import BlockLinkEditor from './BlockLinkEditor.vue';

const props = defineProps({
    payload: { type: Object, required: true },
});

defineEmits(['saved', 'close']);

const type = computed(() => props.payload?.editor?.type ?? props.payload?.block?.type);
const blockId = computed(() => props.payload?.block?.id);
</script>

<template>
    <TopNavEditor
        v-if="type === 'top-nav'"
        :block-id="blockId"
        :editor="payload.editor"
        @saved="$emit('saved', $event)"
    />
    <FloatLinksEditor
        v-else-if="type === 'float-links'"
        :block-id="blockId"
        :editor="payload.editor"
        @saved="$emit('saved', $event)"
    />
    <HeaderEditor
        v-else-if="type === 'header'"
        :block-id="blockId"
        :editor="payload.editor"
        @saved="$emit('saved', $event)"
    />
    <FooterEditor
        v-else-if="type === 'footer'"
        :block-id="blockId"
        :editor="payload.editor"
        @saved="$emit('saved', $event)"
        @close="$emit('close')"
    />
    <CtaEditor
        v-else-if="type === 'cta'"
        :block-id="blockId"
        :editor="payload.editor"
        @saved="$emit('saved', $event)"
        @close="$emit('close')"
    />
    <BlockLinkEditor
        v-else-if="type === 'block-link'"
        :block-id="blockId"
        :editor="payload.editor"
        @saved="$emit('saved', $event)"
        @close="$emit('close')"
    />
    <div v-else class="p-4 text-sm text-stone-400">
        لا يتوفر محرر لهذا النوع من البلوكات حالياً.
    </div>
</template>
