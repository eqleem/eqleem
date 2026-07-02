<?php

return [
    'store' => [
        'order' => 1,
        'slug' => 'store',
        'name' => 'المتجر الإلكتروني',
        'description' => 'إدارة المنتجات والتصنيفات وإعدادات المتجر.',
        'icon' => 'assets/icons/ecommerce/018-cart.svg',
        'components' => [
            'index' => 'admin::page.content.store.index',
            'detail' => 'admin::page.content.store.detail',
        ],
    ],
    'blog' => [
        'order' => 2,
        'slug' => 'blog',
        'name' => 'المدونة',
        'description' => 'كتابة ونشر المقالات وتنظيم التصنيفات.',
        'icon' => 'assets/icons/stationery/002-book.svg',
        'components' => [
            'index' => 'admin::page.content.blog.index',
            'detail' => 'admin::page.content.blog.detail',
        ],
    ],
    'newsletter' => [
        'order' => 3,
        'slug' => 'newsletter',
        'name' => 'النشرة البريدية',
        'description' => 'إنشاء وإرسال النشرات البريدية للمشتركين.',
        'icon' => 'assets/icons/business/045-message.svg',
        'components' => [
            'index' => 'admin::page.content.newsletter.index',
            'detail' => 'admin::page.content.newsletter.detail',
        ],
    ],
    'portfolio' => [
        'order' => 4,
        'slug' => 'portfolio',
        'name' => 'معرض الأعمال',
        'description' => 'عرض وإدارة مشاريعك وأعمالك السابقة.',
        'icon' => 'assets/icons/business/047-portfolio.svg',
        'components' => [
            'index' => 'admin::page.content.portfolio.index',
            'detail' => 'admin::page.content.portfolio.detail',
        ],
    ],
    'menu' => [
        'order' => 5,
        'slug' => 'menu',
        'name' => 'قائمة الطعام',
        'description' => 'إدارة أصناف وعناصر قائمة الطعام.',
        'icon' => 'assets/icons/tabler/chef-hat.svg',
        'components' => [
            'index' => 'admin::page.content.menu.index',
            'detail' => 'admin::page.content.menu.detail',
        ],
    ],
    'services' => [
        'order' => 6,
        'slug' => 'services',
        'name' => 'الخدمات',
        'description' => 'عرض وإدارة الخدمات المقدمة.',
        'icon' => 'assets/icons/tabler/hotel-service.svg',
        'components' => [
            'index' => 'admin::page.content.services.index',
            'detail' => 'admin::page.content.services.detail',
        ],
    ],
    'unit-rental' => [
        'order' => 7,
        'slug' => 'unit-rental',
        'name' => 'تأجير الوحدات',
        'description' => 'إدارة الوحدات المتاحة للتأجير.',
        'icon' => 'assets/icons/tabler/building-estate.svg',
        'components' => [
            'index' => 'admin::page.content.unit-rental.index',
            'detail' => 'admin::page.content.unit-rental.detail',
        ],
    ],
    'pages' => [
        'order' => 8,
        'slug' => 'pages',
        'name' => 'الصفحات',
        'description' => 'إنشاء وإدارة صفحات الموقع الثابتة والمخصصة.',
        'icon' => 'assets/icons/ecommerce/031-content.svg',
        'components' => [
            'index' => 'admin::page.content.pages.index',
            'detail' => 'admin::page.content.pages.detail',
        ],
    ],
    'forms' => [
        'order' => 9,
        'slug' => 'forms',
        'name' => 'النماذج',
        'description' => 'إنشاء وإدارة نماذج التواصل وجمع البيانات.',
        'icon' => 'assets/icons/stationery/005-clipboard.svg',
        'components' => [
            'index' => 'admin::page.content.forms.index',
            'detail' => 'admin::page.content.forms.detail',
        ],
    ],
    'cv' => [
        'order' => 10,
        'slug' => 'cv',
        'name' => 'السيرة الذاتية',
        'description' => 'إدارة بيانات السيرة الذاتية والخبرات والمهارات.',
        'icon' => 'assets/icons/tabler/file-cv.svg',
        'components' => [
            'index' => 'admin::page.content.cv.index',
            'detail' => 'admin::page.content.cv.detail',
        ],
    ],
    'digital-products' => [
        'order' => 11,
        'slug' => 'digital-products',
        'name' => 'المنتجات الرقمية',
        'description' => 'بيع وإدارة المنتجات الرقمية القابلة للتحميل والوصول الفوري.',
        'icon' => 'assets/icons/tabler/file-download.svg',
        'components' => [
            'index' => 'admin::page.content.digital-products.index',
            'detail' => 'admin::page.content.digital-products.detail',
        ],
    ],
    'courses' => [
        'order' => 12,
        'slug' => 'courses',
        'name' => 'الدورات التدريبية',
        'description' => 'إنشاء وإدارة الدورات التدريبية والدروس والمحتوى التعليمي.',
        'icon' => 'assets/icons/tabler/school-bell.svg',
        'components' => [
            'index' => 'admin::page.content.courses.index',
            'detail' => 'admin::page.content.courses.detail',
        ],
    ],
];
