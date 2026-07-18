<?php

return [
    'structure' => [
        'order' => 1,
        'slug' => 'structure',
        'name' => 'أقسام الصفحة',
        'description' => 'قم بترتيب وتنظيف أقسام صفحتك',
        'icon' => 'assets/icons/tabler/puzzle-2.svg',
        'component' => 'admin::page.tabs.structure',
    ],
    'design' => [
        'order' => 2,
        'slug' => 'design',
        'name' => 'تصميم الصفحة',
        'description' => 'تخصيص الألوان والخطوط والمظهر العام للصفحة.',
        'icon' => 'assets/icons/tabler/color-swatch.svg',
        'component' => 'admin::page.tabs.design',
    ],
];
