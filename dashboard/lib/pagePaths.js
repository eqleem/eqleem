export function pageEditPath(page) {
    const uuid = page?.uuid;

    if (!uuid) {
        return '/manage/pages';
    }

    if (page.template === 'contact') {
        return `/manage/pages/contact/${uuid}`;
    }

    if (page.template === 'faq') {
        return `/manage/pages/faq/${uuid}`;
    }

    if (page.template === 'about') {
        return `/manage/pages/about/${uuid}`;
    }

    return `/manage/pages/detail/${uuid}`;
}
