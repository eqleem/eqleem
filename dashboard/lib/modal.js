// Mirrors the blade Alpine contract: $dispatch('openmodal', { modal: name }).
export function openModal(name) {
    window.dispatchEvent(new CustomEvent('openmodal', { detail: { modal: name } }));
}

export function closeModal(name = null) {
    window.dispatchEvent(new CustomEvent('closemodal', { detail: { modal: name } }));
}
