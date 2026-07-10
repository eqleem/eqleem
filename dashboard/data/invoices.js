import { reactive } from 'vue';

export const invoices = reactive([
    {
        id: 501, uuid: 'inv-501', s_number: 'INV-000501', status: 'paid', type: 'sale', currency: 'SAR',
        total_before_vat: 417, total_after_vat: 480, subtotal_after_vat: 480, amount_paid: 480,
        issued: '12 يناير 2026', time: '10:24 ص', order_label: 'طلب #000001', order_uuid: 'ord-1', user: 'أحمد الأحمدي', note: null,
        items: [
            { id: 1, name: 'قميص قطني', type: 'منتج', quantity: 2, amount_after_vat: 138, total_after_vat: 276, note: null },
            { id: 2, name: 'شحن سريع', type: 'خدمة', quantity: 1, amount_after_vat: 35, total_after_vat: 35, note: null },
        ],
        payments: [{ id: 101, uuid: 'pay-101', amount: 480, status: 'paid', created: '12 يناير 2026 10:24 ص' }],
    },
    {
        id: 502, uuid: 'inv-502', s_number: 'INV-000502', status: 'partial', type: 'subscription', currency: 'SAR',
        total_before_vat: 1087, total_after_vat: 1250, subtotal_after_vat: 1250, amount_paid: 600,
        issued: '11 يناير 2026', time: '02:10 م', order_label: null, order_uuid: null, user: 'أحمد الأحمدي', note: 'دفعة أولى من اشتراك سنوي.',
        items: [{ id: 1, name: 'اشتراك سنوي', type: 'اشتراك', quantity: 1, amount_after_vat: 1250, total_after_vat: 1250, note: null }],
        payments: [{ id: 102, uuid: 'pay-102', amount: 600, status: 'pending', created: '11 يناير 2026 02:10 م' }],
    },
]);

const statusMap = {
    paid: { label: 'مدفوعة', color: 'green' },
    unpaid: { label: 'غير مدفوعة', color: 'yellow' },
    partial: { label: 'مدفوعة جزئياً', color: 'yellow' },
    cancelled: { label: 'ملغاة', color: 'red' },
};
export const invoiceStatusLabel = (status) => statusMap[status]?.label ?? status;
export const invoiceStatusColor = (status) => statusMap[status]?.color ?? 'gray';

const typeMap = { sale: 'فاتورة بيع', refund: 'إشعار دائن', subscription: 'اشتراك' };
export const invoiceTypeLabel = (type) => typeMap[type] ?? type;

export const invoiceDue = (invoice) => Math.max(0, invoice.total_after_vat - invoice.amount_paid);

export const getInvoice = (uuid) => invoices.find((invoice) => invoice.uuid === uuid) ?? invoices[0];
