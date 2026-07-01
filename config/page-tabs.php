<?php

return [
    'structure' => [
        'order' => 1,
        'slug' => 'structure',
        'name' => 'هيكل الصفحة',
        'description' => 'إدارة ترتيب الأقسام وإضافة أو إزالة الكتل من الصفحة الرئيسية.',
        'icon' => 'assets/icons/business/036-Jigsaw.svg',
        'component' => 'admin::page.tabs.structure',
    ],
    'design' => [
        'order' => 2,
        'slug' => 'design',
        'name' => 'تصميم الصفحة',
        'description' => 'تخصيص الألوان والخطوط والمظهر العام للصفحة.',
        'icon' => 'assets/icons/stationery/007-paint-swatch.svg',
        'component' => 'admin::page.tabs.design',
    ],
];
