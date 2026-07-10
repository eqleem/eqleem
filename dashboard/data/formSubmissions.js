import { reactive } from 'vue';

export const formSubmissions = reactive([
    { id: 9001, unread: true, status: 'new', form_title: 'نموذج تواصل', client: { name: 'نورة الدوسري', email: 'noura@example.com', phone: '0544455667' }, preview: 'أرغب بالاستفسار عن الأسعار', submitted: '12 يناير 2026', time: '11:00 ص' },
    { id: 9002, unread: false, status: 'read', form_title: 'طلب عرض سعر', client: null, preview: 'مرحباً، أحتاج عرض سعر لخدمة التصميم', submitted: '10 يناير 2026', time: '09:30 ص' },
]);

const statusMap = {
    new: { label: 'جديد', color: 'blue' },
    read: { label: 'مقروء', color: 'gray' },
    archived: { label: 'مؤرشف', color: 'gray' },
};
export const submissionStatusLabel = (status) => statusMap[status]?.label ?? status;
export const submissionStatusColor = (status) => statusMap[status]?.color ?? 'gray';
