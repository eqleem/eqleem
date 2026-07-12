/**
 * Icon helpers for the tenant analytics dashboard.
 * Section icons use /assets/icons/business; item icons use published
 * request-analytics assets (+ country flags).
 */

const BASE = '/vendor/request-analytics';

export const sectionIcons = {
    views: '/assets/icons/business/009-web browser.svg',
    visitors: '/assets/icons/business/025-team work.svg',
    bounce: '/assets/icons/business/018-arrows.svg',
    duration: '/assets/icons/business/055-stopwatch.svg',
    chart: '/assets/icons/business/030-growth-chart.svg',
    pages: '/assets/icons/business/035-file.svg',
    referrers: '/assets/icons/business/054-signpost.svg',
    browsers: '/assets/icons/business/009-web browser.svg',
    operatingSystems: '/assets/icons/business/008-microprocessor.svg',
    devices: '/assets/icons/business/015-cloud-network.svg',
    countries: '/assets/icons/business/010-location.svg',
};

function normalize(value) {
    return String(value ?? '')
        .toLowerCase()
        .replace(/[\s_-]+/g, '');
}

export function browserIcon(name) {
    const key = normalize(name);

    if (key.includes('chrome') || key.includes('chromium')) {
        return `${BASE}/browsers/chrome.png`;
    }

    if (key.includes('firefox')) {
        return `${BASE}/browsers/firefox.png`;
    }

    if (key.includes('safari')) {
        return `${BASE}/browsers/safari.png`;
    }

    if (key.includes('edge') || key.includes('edg')) {
        return `${BASE}/browsers/ms-edge.png`;
    }

    return `${BASE}/browsers/unknown.png`;
}

export function operatingSystemIcon(name) {
    const key = normalize(name);

    if (key.startsWith('windows') || key.includes('windows')) {
        return `${BASE}/operating-systems/windows-logo.png`;
    }

    if (key.includes('linux')) {
        return `${BASE}/operating-systems/linux.png`;
    }

    if (key.includes('macos') || key.includes('macox') || key.includes('osx') || key === 'mac') {
        return `${BASE}/operating-systems/mac-logo.png`;
    }

    if (key.includes('android')) {
        return `${BASE}/operating-systems/android-os.png`;
    }

    if (key.includes('ios') || key.includes('iphone') || key.includes('ipad')) {
        return `${BASE}/operating-systems/iphone.png`;
    }

    return `${BASE}/operating-systems/unknown.png`;
}

export function deviceIcon(name) {
    const key = normalize(name);

    if (key.includes('iphone') || key.includes('android') || key.includes('mobile') || key.includes('smartphone') || key.includes('phone')) {
        return `${BASE}/devices/smartphone.png`;
    }

    if (key.includes('ipad') || key.includes('tablet')) {
        return `${BASE}/devices/ipad.png`;
    }

    if (key.includes('desktop') || key.includes('laptop') || key.includes('mac') || key.includes('pc') || key.includes('computer')) {
        return `${BASE}/devices/laptop.png`;
    }

    if (key.includes('tv') || key.includes('television')) {
        return `${BASE}/devices/tv.png`;
    }

    return `${BASE}/devices/unknown.png`;
}

export function countryIcon(item) {
    const code = String(item?.code ?? '').toLowerCase();

    if (code.length === 2) {
        return `https://www.worldatlas.com/r/w236/img/flag/${code}-flag.jpg`;
    }

    return sectionIcons.countries;
}

/**
 * @param {'browser'|'os'|'device'|'country'|null} kind
 * @param {Record<string, mixed>} item
 * @param {string} labelKey
 */
export function itemIconSrc(kind, item, labelKey = 'label') {
    const label = item?.[labelKey] ?? item?.name ?? item?.browser ?? item?.path ?? item?.domain ?? '';

    if (kind === 'browser') {
        return browserIcon(label);
    }

    if (kind === 'os') {
        return operatingSystemIcon(label);
    }

    if (kind === 'device') {
        return deviceIcon(label);
    }

    if (kind === 'country') {
        return countryIcon(item);
    }

    return null;
}
