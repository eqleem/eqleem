/**
 * Patch the cached list row thumbnail so content tables show the image
 * without requiring a full page reload / list refetch.
 *
 * @param {Array<{ uuid: string, image?: string|null }>} items
 * @param {string} uuid
 * @param {Array<{ url?: string }|string>|string|null|undefined} imagesOrUrl
 */
export function syncListImage(items, uuid, imagesOrUrl) {
    const index = items.findIndex((item) => item.uuid === uuid);

    if (index === -1) {
        return;
    }

    let image = null;

    if (Array.isArray(imagesOrUrl)) {
        const first = imagesOrUrl[0];
        image = typeof first === 'string' ? first : (first?.url ?? null);
    } else if (typeof imagesOrUrl === 'string') {
        image = imagesOrUrl;
    }

    items[index] = { ...items[index], image };
}
