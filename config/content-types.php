<?php

return [
    'pages' => [
        'order' => 1,
        'slug' => 'pages',
        'name' => 'الصفحات',
        'description' => 'إنشاء وإدارة صفحات الموقع الثابتة والمخصصة.',
        'icon' => 'assets/icons/ecommerce/031-content.svg',
        'components' => [
            'index' => 'admin::page.content.pages.index',
            'detail' => 'admin::page.content.pages.detail',
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
    'portfolio' => [
        'order' => 3,
        'slug' => 'portfolio',
        'name' => 'معرض الأعمال',
        'description' => 'عرض وإدارة مشاريعك وأعمالك السابقة.',
        'icon' => 'assets/icons/business/047-portfolio.svg',
        'components' => [
            'index' => 'admin::page.content.portfolio.index',
            'detail' => 'admin::page.content.portfolio.detail',
        ],
    ],
    'forms' => [
        'order' => 4,
        'slug' => 'forms',
        'model_type' => 'form',
        'name' => 'النماذج',
        'description' => 'إنشاء وإدارة نماذج التواصل وجمع البيانات.',
        'icon' => 'assets/icons/stationery/005-clipboard.svg',
        'components' => [
            'index' => 'admin::page.content.forms.index',
            'detail' => 'admin::page.content.forms.detail',
        ],
    ],
    'store' => [
        'order' => 5,
        'slug' => 'store',
        'name' => 'المتجر الإلكتروني',
        'description' => 'إدارة المنتجات والتصنيفات وإعدادات المتجر.',
        'icon' => 'assets/icons/ecommerce/018-cart.svg',
        'components' => [
            'index' => 'admin::page.content.store.index',
            'detail' => 'admin::page.content.store.detail',
        ],
    ],
    'digital-products' => [
        'order' => 6,
        'slug' => 'digital-products',
        'name' => 'المنتجات الرقمية',
        'description' => 'بيع وإدارة المنتجات الرقمية القابلة للتحميل والوصول الفوري.',
        'icon' => 'assets/icons/tabler/file-download.svg',
        'components' => [
            'index' => 'admin::page.content.digital-products.index',
            'detail' => 'admin::page.content.digital-products.detail',
        ],
    ],
    'services' => [
        'order' => 7,
        'slug' => 'services',
        'name' => 'الخدمات',
        'description' => 'عرض وإدارة الخدمات المقدمة.',
        'icon' => 'assets/icons/tabler/hotel-service.svg',
        'components' => [
            'index' => 'admin::page.content.services.index',
            'detail' => 'admin::page.content.services.detail',
        ],
    ],
    'newsletter' => [
        'order' => 8,
        'slug' => 'newsletter',
        'name' => 'النشرة البريدية',
        'description' => 'إنشاء وإرسال النشرات البريدية للمشتركين.',
        'icon' => 'assets/icons/business/045-message.svg',
        'components' => [
            'index' => 'admin::page.content.newsletter.index',
            'detail' => 'admin::page.content.newsletter.detail',
        ],
    ],
    'menu' => [
        'order' => 9,
        'slug' => 'menu',
        'name' => 'قائمة الطعام',
        'description' => 'إدارة أصناف وعناصر قائمة الطعام.',
        'icon' => 'assets/icons/tabler/chef-hat.svg',
        'components' => [
            'index' => 'admin::page.content.menu.index',
            'detail' => 'admin::page.content.menu.detail',
        ],
    ],
    'unit-rental' => [
        'order' => 10,
        'slug' => 'unit-rental',
        'name' => 'تأجير الوحدات',
        'description' => 'إدارة الوحدات المتاحة للتأجير.',
        'icon' => 'assets/icons/tabler/building-estate.svg',
        'components' => [
            'index' => 'admin::page.content.unit-rental.index',
            'detail' => 'admin::page.content.unit-rental.detail',
        ],
    ],
    'cv' => [
        'order' => 11,
        'slug' => 'cv',
        'name' => 'السيرة الذاتية',
        'description' => 'إدارة بيانات السيرة الذاتية والخبرات والمهارات.',
        'icon' => 'assets/icons/tabler/file-cv.svg',
        'components' => [
            'index' => 'admin::page.content.cv.index',
            'detail' => 'admin::page.content.cv.detail',
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
