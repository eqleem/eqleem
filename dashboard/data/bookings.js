export const walkingClientLabel = 'عميل نقدي';

export const bookingTypeOptions = {
    service: 'خدمة',
    unit_rental: 'وحدة تأجير',
};

export const bookingTypeSearchPlaceholders = {
    service: 'ابحث باسم الخدمة ..',
    unit_rental: 'ابحث باسم الوحدة ..',
};

// From Booking::statuses().
export const bookingStatusOptions = {
    new: 'جديد',
    awaiting_payment: 'بانتظار الدفع',
    confirmed: 'مؤكد',
    completed: 'مكتمل',
    cancelled: 'ملغي',
};

// From Booking::statusIcons() + statusBadgeColorFor().
export const statusFilters = [
    { value: 'new', label: 'جديد', icon: 'sparkles', color: 'blue' },
    { value: 'awaiting_payment', label: 'بانتظار الدفع', icon: 'coin', color: 'amber' },
    { value: 'confirmed', label: 'مؤكد', icon: 'check', color: 'teal' },
    { value: 'completed', label: 'مكتمل', icon: 'package', color: 'green' },
    { value: 'cancelled', label: 'ملغي', icon: 'x', color: 'red' },
];

export const statusFilterColors = {
    blue: {
        idle: 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100',
        active: 'border-blue-400 bg-blue-100 text-blue-800 ring-0 ring-blue-300',
    },
    amber: {
        idle: 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
        active: 'border-amber-400 bg-amber-100 text-amber-800 ring-0 ring-amber-300',
    },
    teal: {
        idle: 'border-teal-200 bg-teal-50 text-teal-700 hover:bg-teal-100',
        active: 'border-teal-400 bg-teal-100 text-teal-800 ring-0 ring-teal-300',
    },
    green: {
        idle: 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100',
        active: 'border-green-400 bg-green-100 text-green-800 ring-0 ring-green-300',
    },
    red: {
        idle: 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100',
        active: 'border-red-400 bg-red-100 text-red-800 ring-0 ring-red-300',
    },
    gray: {
        idle: 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50',
        active: 'border-gray-400 bg-gray-100 text-gray-900 ring-0 ring-gray-300',
    },
};
