<script setup>
// Minimal stand-in for ui:icon — the subset of names used by the detail pages/tables.
defineProps({ name: { type: String, required: true } });

const icons = {
    receipt: '<path d="M5 21V5a1 1 0 0 1 1.5-.9L8 5l1.5-1 1.5 1L12 5l1.5-1 1.5 1L16 5l1.5-.9A1 1 0 0 1 19 5v16l-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M9 9h6M9 13h4"/>',
    user: '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
    note: '<path d="M5 4h14v16H5zM9 8h6M9 12h6M9 16h3"/>',
    package: '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3z"/><path d="M12 12 4 7.5M12 12l8-4.5M12 12v9"/>',
    refresh: '<path d="M20 11a8 8 0 0 0-14-5M4 6v4h4M4 13a8 8 0 0 0 14 5M20 18v-4h-4"/>',
    cart: '<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M2 3h2l2.4 12.4a1 1 0 0 0 1 .8h9.2a1 1 0 0 0 1-.8L21 7H5"/>',
    'cart-off': '<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M4 4 2 3M6 6l.6 3M8 12h11l1-5H7M3 3l18 18"/>',
    store: '<path d="M4 9h16l-1-5H5L4 9zM4 9v11h16V9M9 20v-6h6v6"/>',
    card: '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    pin: '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    coin: '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M9.5 9.5c0-1 1-1.5 2.5-1.5s2.5.5 2.5 1.5S15 11 12 11s-2.5.5-2.5 1.5S10 15 12 15s2.5-.5 2.5-1.5"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    history: '<path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 4v4h4M12 8v4l3 2"/>',
    invoice: '<path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6M9 16h6"/>',
    list: '<path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
    bank: '<path d="M3 10 12 4l9 6M4 10v9h16v-9M8 12v5M12 12v5M16 12v5M3 21h18"/>',
    info: '<circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>',
    link: '<path d="M14 4h6v6M20 4l-9 9M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/>',
    clipboard: '<rect x="8" y="4" width="8" height="4" rx="1"/><path d="M9 4H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3M9 12h6M9 16h4"/>',
    message: '<path d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z"/>',
    check: '<path d="M5 12l5 5 9-9"/>',
};
</script>

<template>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="icons[name] ?? icons.info"></svg>
</template>
