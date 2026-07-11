import { reactive } from 'vue';
import { formatMoneyAmount } from '../lib/money.js';

// Shared dummy store — stands in for the Livewire Order model / events (updateOrderList).
let nextId = 4;

export const orders = reactive([
    { id: 1, uuid: 'ord-1', number: '000001', client: 'محمد العتيبي', status: 'completed', payment_status: 'paid', grand_total: 480, created: 'قبل ساعة' },
    { id: 2, uuid: 'ord-2', number: '000002', client: 'سارة القحطاني', status: 'pending', payment_status: 'partial', grand_total: 1250, created: 'قبل ٣ ساعات' },
    { id: 3, uuid: 'ord-3', number: '000003', client: null, status: 'draft', payment_status: 'unpaid', grand_total: 90, created: 'أمس' },
]);

export const walkingClientLabel = 'عميل نقدي';

export const paymentMethodOptions = {
    cash: 'نقداً',
    card: 'بطاقة',
    bank_transfer: 'تحويل بنكي',
    online: 'دفع إلكتروني',
};

// From Order::addItemTypeOptions() / itemTypeOptions().
export const addItemTypeOptions = {
    product: 'أضف منتج',
    digital_product: 'أضف منتج رقمي',
    course: 'أضف دورة',
    digital_service: 'أضف خدمة رقمية',
    menu: 'أضف صنف طعام',
    service: 'أضف خدمة',
    unit_rental: 'أضف وحدة تأجير',
    other: 'أضف عنصر مخصص',
};

export const itemTypeOptions = {
    product: 'منتج',
    digital_product: 'منتج رقمي',
    course: 'دورة',
    digital_service: 'خدمة رقمية',
    menu: 'صنف طعام',
    service: 'خدمة',
    unit_rental: 'وحدة تأجير',
    other: 'عنصر مخصص',
};

export const itemSearchPlaceholders = {
    product: 'ابحث باسم المنتج أو أضف منتج جديد',
    digital_product: 'ابحث باسم المنتج الرقمي أو أضف منتجاً جديداً',
    course: 'ابحث باسم الدورة التدريبية أو أضف دورة جديدة',
    digital_service: 'ابحث باسم الخدمة الرقمية أو أضف خدمة جديدة',
    menu: 'ابحث باسم الصنف أو أضف صنفاً جديداً',
    service: 'ابحث باسم الخدمة أو أضف خدمة جديدة',
    unit_rental: 'ابحث باسم الوحدة أو أضف وحدة جديدة',
    other: 'أضف وصف العنصر المخصص ..',
};

const statusMap = {
    draft: { label: 'مسودة', color: 'gray' },
    pending: { label: 'قيد المعالجة', color: 'yellow' },
    processing: { label: 'قيد التنفيذ', color: 'blue' },
    completed: { label: 'مكتمل', color: 'green' },
    cancelled: { label: 'ملغي', color: 'red' },
};

export const statusLabel = (status) => statusMap[status]?.label ?? status;
export const statusColor = (status) => statusMap[status]?.color ?? 'gray';

const paymentMap = {
    paid: { label: 'حالة الدفع: مدفوع', color: 'gray' },
    unpaid: { label: 'حالة الدفع: لم تتم', color: 'yellow' },
    partial: { label: 'حالة الدفع: مدفوع جزئياً', color: 'yellow' },
    refunded: { label: 'حالة الدفع: مسترجع', color: 'gray' },
};

export const paymentLabel = (status) => paymentMap[status]?.label ?? `حالة الدفع: ${status}`;
export const paymentColor = (status) => paymentMap[status]?.color ?? 'gray';

export function money(value) {
    return formatMoneyAmount(value || 0, { maximumFractionDigits: 0 });
}

export function addOrder(data) {
    orders.unshift({
        id: nextId,
        uuid: `ord-${nextId}`,
        number: String(nextId).padStart(6, '0'),
        created: 'الآن',
        status: 'draft',
        payment_status: 'unpaid',
        ...data,
    });
    nextId += 1;
}

export function removeOrders(ids) {
    const set = new Set(ids);
    for (let i = orders.length - 1; i >= 0; i -= 1) {
        if (set.has(orders[i].id)) {
            orders.splice(i, 1);
        }
    }
}

// Builds a rich (dummy) detail object for an order uuid.
export function getOrderDetail(uuid) {
    const base = orders.find((order) => order.uuid === uuid) ?? orders[0];

    const items = [
        { id: 1, name: 'قميص قطني', type: 'product', type_label: 'منتج', type_color: 'blue', qty: 2, unit_price: 120, discount: 0, line_total: 240 },
        { id: 2, name: 'شحن وخدمة تغليف', type: 'service', type_label: 'خدمة', type_color: 'green', qty: 1, unit_price: 30, discount: 10, line_total: 20 },
    ];

    const subtotal = items.reduce((sum, item) => sum + item.qty * item.unit_price, 0);
    const discount = items.reduce((sum, item) => sum + item.discount, 0);
    const tax = Math.round((subtotal - discount) * 0.15);
    const grand = subtotal - discount + tax;
    const paid = base.payment_status === 'paid' ? grand : base.payment_status === 'partial' ? Math.round(grand / 2) : 0;
    const due = grand - paid;

    const client = base.client
        ? { name: base.client, email: 'client@example.com', phone: '0500000000', uuid: 'clt-1' }
        : null;

    const payments = paid > 0
        ? [{ id: 101, uuid: 'pay-101', method: 'بطاقة ائتمان', status: 'paid', amount: paid, currency: 'SAR', created: `${base.created} ١٠:٢٤ ص` }]
        : [];

    const activity = [
        { key: 'a1', type: 'status', title: 'تغيير حالة الطلب', status: base.status, date: base.created },
        { key: 'a2', type: 'activity', title: 'إنشاء الطلب', date: base.created },
    ];

    return { ...base, items, subtotal, discount, tax, grand, paid, due, client, payments, activity };
}
