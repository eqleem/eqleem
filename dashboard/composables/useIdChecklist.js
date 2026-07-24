/**
 * Toggle membership of string ids on a reactive form field (categories, calendars, …).
 *
 * @param {Record<string, any>} form
 * @param {string} [field='categoryIds']
 */
export function useIdChecklist(form, field = 'categoryIds') {
    function toggle(id, checked) {
        const key = String(id);

        if (checked) {
            if (!form[field].includes(key)) {
                form[field].push(key);
            }
            return;
        }

        form[field] = form[field].filter((item) => item !== key);
    }

    return { toggle };
}
