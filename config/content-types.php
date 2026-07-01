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
];
