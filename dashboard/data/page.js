// Fixed page tabs — from config/page-tabs.php.
export const fixedTabs = [
    { id: 'structure', slug: 'structure', label: 'صفحتي', icon: 'assets/icons/tabler/puzzle-2.svg' },
    { id: 'design', slug: 'design', label: 'تصميم الصفحة', icon: 'assets/icons/tabler/color-swatch.svg' },
];

// Static metadata catalog for dedicated section pages/stores.
// Dashboard nav tabs are loaded dynamically from /api/page/content-types
// (config/content-types.php via ContentTypeRegistry) — see stores/contentTypes.js.
export const contentTypeCatalog = [
    { slug: 'pages', name: 'الصفحات', description: 'إنشاء وإدارة صفحات الموقع الثابتة والمخصصة.', icon: 'assets/icons/ecommerce/031-content.svg', color: 'blue' },
    { slug: 'blog', name: 'المدونة', description: 'كتابة ونشر المقالات وتنظيم التصنيفات.', icon: 'assets/icons/stationery/002-book.svg', color: 'orange' },
    { slug: 'portfolio', name: 'معرض الأعمال', description: 'عرض وإدارة مشاريعك وأعمالك السابقة.', icon: 'assets/icons/business/047-portfolio.svg', color: 'violet' },
    { slug: 'forms', name: 'النماذج', description: 'إنشاء وإدارة نماذج التواصل وجمع البيانات.', icon: 'assets/icons/stationery/005-clipboard.svg', color: 'yellow' },
    { slug: 'store', name: 'المتجر الإلكتروني', description: 'إدارة المنتجات والتصنيفات وإعدادات المتجر.', icon: 'assets/icons/ecommerce/018-cart.svg', color: 'green' },
    { slug: 'digital-products', name: 'المنتجات الرقمية', description: 'بيع وإدارة المنتجات الرقمية القابلة للتحميل والوصول الفوري.', icon: 'assets/icons/business/035-file.svg', color: 'red' },
    { slug: 'digital-services', name: 'الخدمات الرقمية', description: 'بيع وإدارة الخدمات الرقمية مع تحديد مدة التسليم والسعر.', icon: 'assets/icons/business/015-cloud-network.svg', color: 'blue' },
    { slug: 'services', name: 'الخدمات', description: 'عرض وإدارة الخدمات المقدمة.', icon: 'assets/icons/business/025-team work.svg', color: 'teal' },
    { slug: 'newsletter', name: 'النشرة البريدية', description: 'إنشاء وإرسال النشرات البريدية للمشتركين.', icon: 'assets/icons/business/045-message.svg', color: 'rose' },
    { slug: 'menu', name: 'قائمة الطعام', description: 'إدارة أصناف وعناصر قائمة الطعام.', icon: 'assets/icons/business/059-teacup.svg', color: 'amber' },
    { slug: 'unit-rental', name: 'تأجير الوحدات', description: 'إدارة الوحدات المتاحة للتأجير.', icon: 'assets/icons/business/010-location.svg', color: 'lime' },
    { slug: 'courses', name: 'الدورات التدريبية', description: 'إنشاء وإدارة الدورات التدريبية والدروس والمحتوى التعليمي.', icon: 'assets/icons/business/011-presentation.svg', color: 'pink' },
    { slug: 'reviews', name: 'التقييمات', description: 'عرض قائمة التقييمات وآراء العملاء.', icon: 'assets/icons/ecommerce/006-badge.svg', color: 'amber' },
];

/** @deprecated Prefer useContentTypesStore().contentTypes for nav — kept for section stores. */
export const contentTypes = contentTypeCatalog;

export const contentTypeBySlug = (slug) => contentTypeCatalog.find((type) => type.slug === slug);

// Structure tab — live data comes from /api/page/structure (see stores/pageStructure.js).
// Design tab — live data comes from /api/page/design (see stores/pageDesign.js).

// Generic dummy content items for a content-type index.
export function itemsFor(slug) {
    return [
        { id: `${slug}-1`, title: 'عنصر تجريبي أول', status: 'published', date: '12 يناير 2026' },
        { id: `${slug}-2`, title: 'عنصر تجريبي ثانٍ', status: 'draft', date: '10 يناير 2026' },
        { id: `${slug}-3`, title: 'عنصر تجريبي ثالث', status: 'published', date: '8 يناير 2026' },
    ];
}

// Generic dummy categories for a content-type.
export function categoriesFor(slug) {
    return [
        { id: `${slug}-cat-1`, name: 'التصنيف الأول', count: 4, active: true },
        { id: `${slug}-cat-2`, name: 'التصنيف الثاني', count: 2, active: true },
        { id: `${slug}-cat-3`, name: 'تصنيف مؤرشف', count: 0, active: false },
    ];
}
