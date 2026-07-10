// Port of config/settings.php (+ dummy form data for each settings page).

export const settingsList = [
    { order: 1, slug: 'general-info', name: 'معلومات النشاط', description: 'بيانات النشاط التجاري', icon: 'assets/icons/stationery/011-id-card.svg' },
    { order: 2, slug: 'domain', name: 'الدومين', description: 'تخصيص نطاق الموقع', icon: 'assets/icons/business/015-cloud-network.svg' },
    { order: 3, slug: 'analytics', name: 'ربط الإحصائيات', description: 'لتتبع إحصائيات صفحتك', icon: 'assets/icons/business/030-growth-chart.svg' },
    { order: 4, slug: 'verification', name: 'توثيق الحساب', description: 'بيانات التوثيق بالمستندات الرسمية', icon: 'assets/icons/business/051-shield.svg' },
    { order: 5, slug: 'language-currency', name: 'اللغة والعملة', description: 'اللغة والعملة الافتراضية والمتاحة لصفحتك', icon: 'assets/icons/business/009-web browser.svg' },
    { order: 7, slug: 'branches', name: 'الفروع', description: 'الفروع والمستودعات.', icon: 'assets/icons/business/010-location.svg' },
    { order: 10, slug: 'payment-options', name: 'وسائل الدفع', description: 'تخصيص وسائل الدفع.', icon: 'assets/icons/business/017-atm-card.svg' },
    { order: 11, slug: 'shipping-option', name: 'وسائل الشحن', description: 'تخصيص طرق الشحن المتاحة.', icon: 'assets/icons/ecommerce/018-cart.svg' },
];

export const settingBySlug = (slug) => settingsList.find((item) => item.slug === slug);

export const socialNetworks = {
    twitter: 'X (تويتر)',
    instagram: 'إنستغرام',
    snapchat: 'سناب شات',
    youtube: 'يوتيوب',
    facebook: 'فيسبوك',
    tiktok: 'تيك توك',
    linkedin: 'لينكدإن',
    whatsapp: 'واتساب',
    telegram: 'تيليجرام',
    website: 'الموقع الإلكتروني',
};

export const analyticsProviders = [
    { key: 'google_tag_manager', name: 'Google Tag Manager', label: 'Container ID', placeholder: 'GTM-XXXXXXX' },
    { key: 'google_analytics', name: 'Google Analytics', label: 'Measurement ID', placeholder: 'G-XXXXXXXXXX' },
    { key: 'tiktok', name: 'TikTok Pixel', label: 'Pixel ID', placeholder: '' },
    { key: 'meta', name: 'Meta Pixel', label: 'Pixel ID', placeholder: '' },
    { key: 'snapchat', name: 'Snapchat Pixel', label: 'Pixel ID', placeholder: '' },
];

export const languages = {
    ar: 'العربية',
    en: 'English',
    fr: 'Français',
    tr: 'Türkçe',
    ur: 'اردو',
};

export const currencies = {
    SAR: 'ريال سعودي (SAR)',
    AED: 'درهم إماراتي (AED)',
    KWD: 'دينار كويتي (KWD)',
    BHD: 'دينار بحريني (BHD)',
    QAR: 'ريال قطري (QAR)',
    OMR: 'ريال عماني (OMR)',
    JOD: 'دينار أردني (JOD)',
    EGP: 'جنيه مصري (EGP)',
    USD: 'دولار أمريكي (USD)',
    EUR: 'يورو (EUR)',
};

export const identityTypes = {
    individual: 'فرد',
    llc: 'مؤسسة',
    company: 'شركة',
    charity: 'جمعية خيرية',
};

export const verificationCountries = {
    SA: 'المملكة العربية السعودية',
};

export const appDomain = 'eqleem.test';

export const paymentMethods = [
    { slug: 'bank-transfer', name: 'التحويل البنكي', description: 'اسمح لعملائك بالدفع بواسطة التحويل البنكي إلى حساباتك مباشرة.', icon: 'assets/images/bank-transfer.png', active: true },
    { slug: 'credit-card', name: 'البطاقة الإئتمانية', description: 'مدفوعات كتالوج: الدفع بواسطة البطاقات الإئتمانية، فيز وماستركارد ومدى.', icon: 'assets/images/credit-card-payment.svg', active: false },
    { slug: 'cash-on-delivery', name: 'الدفع عند الاستلام', description: 'اسمح لعملائك بالدفع نقداً عند استلام الشحنة. متاح للمنتجات القابلة للشحن فقط.', icon: 'assets/images/cod-payment.webp', active: true },
    { slug: 'tabby', name: 'تابي', description: 'قسّم مشتريات عملائك على 4 دفعات بدون رسوم.', icon: 'assets/images/tabby-payment.webp', active: false },
    { slug: 'tamara', name: 'تمارا', description: 'قسّم مشتريات عملائك على دفعات مريحة.', icon: 'assets/images/tamara_installment_mini.webp', active: false },
];

export const shippingMethods = [
    { slug: 'eqleem-ship', name: 'شحن إقليم - شحن عادي', description: 'خيار الشحن المفضل، شحن داخلي ودولي، للشحن العادي الجاف.', icon: 'assets/icons/ecommerce/009-cargo ship.svg', active: true },
];

export const weekdayLabels = {
    sunday: 'الأحد',
    monday: 'الإثنين',
    tuesday: 'الثلاثاء',
    wednesday: 'الأربعاء',
    thursday: 'الخميس',
    friday: 'الجمعة',
    saturday: 'السبت',
};
