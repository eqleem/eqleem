import { reactive } from 'vue';

export const payments = reactive([
    { id: 101, uuid: 'pay-101', amount: 480, currency: 'SAR', status: 'paid', reason: 'order', gateway: 'moyasar', source_type: 'creditcard', card: '•••• 4242', payer: 'محمد العتيبي', email: 'mohammed@example.com', order_number: '000001', order_uuid: 'ord-1', created: '12 يناير 2026', time: '10:24 ص' },
    { id: 102, uuid: 'pay-102', amount: 1250, currency: 'SAR', status: 'pending', reason: 'subscription', gateway: 'moyasar', source_type: 'applepay', card: null, payer: 'سارة القحطاني', email: 'sara@example.com', order_number: null, order_uuid: null, created: '11 يناير 2026', time: '02:10 م' },
    { id: 103, uuid: 'pay-103', amount: 90, currency: 'SAR', status: 'refunded', reason: 'order', gateway: 'moyasar', source_type: 'mada', card: '•••• 1122', payer: 'عبدالله الشمري', email: null, order_number: '000003', order_uuid: 'ord-3', created: '9 يناير 2026', time: '08:00 م' },
]);

const statusMap = {
    paid: { label: 'مدفوع', color: 'green' },
    pending: { label: 'قيد المعالجة', color: 'yellow' },
    failed: { label: 'فشلت', color: 'red' },
    refunded: { label: 'مسترجع', color: 'gray' },
};
export const paymentStatusLabel = (status) => statusMap[status]?.label ?? status;
export const paymentStatusColor = (status) => statusMap[status]?.color ?? 'gray';

const reasonMap = { order: 'طلب', subscription: 'اشتراك', refund: 'استرجاع' };
export const reasonLabel = (reason) => reasonMap[reason] ?? reason;

const sourceMap = { creditcard: 'بطاقة ائتمان', applepay: 'Apple Pay', mada: 'مدى', cash: 'نقداً' };
export const sourceTypeLabel = (source) => sourceMap[source] ?? source;

const gatewayMap = { moyasar: 'ميسر', cash: 'نقداً' };
export const gatewayLabel = (gateway) => gatewayMap[gateway] ?? gateway;

export const getPayment = (uuid) => payments.find((payment) => payment.uuid === uuid) ?? payments[0];
