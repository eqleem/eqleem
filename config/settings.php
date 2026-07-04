<?php

return [
    'general-info' => [
        'order' => 1,
        'slug' => 'general-info',
        'name' => 'معلومات النشاط',
        'description' => 'بيانات النشاط التجاري',
        'icon' => 'assets/icons/stationery/011-id-card.svg',
        'components' => [
            'index' => 'admin::settings.info.general-info',
        ],
    ],
    'domain' => [
        'order' => 2,
        'slug' => 'domain',
        'name' => 'الدومين',
        'description' => 'تخصيص نطاق الموقع',
        'icon' => 'assets/icons/business/015-cloud-network.svg',
        'components' => [
            'index' => 'admin::settings.info.domain',
        ],
    ],
    'analytics' => [
        'order' => 3,
        'slug' => 'analytics',
        'name' => 'ربط الإحصائيات',
        'description' => 'لتتبع إحصائيات صفحتك',
        'icon' => 'assets/icons/business/030-growth-chart.svg',
        'components' => [
            'index' => 'admin::settings.info.analytics',
        ],
    ],
    'verification' => [
        'order' => 4,
        'slug' => 'verification',
        'name' => 'توثيق الحساب',
        'description' => 'بيانات التوثيق بالمستندات الرسمية',
        'icon' => 'assets/icons/business/051-shield.svg',
        'components' => [
            'index' => 'admin::settings.info.verification',
        ],
    ],
    'branches' => [
        'order' => 7,
        'slug' => 'branches',
        'name' => 'الفروع',
        'description' => 'الفروع والمستودعات.',
        'icon' => 'assets/icons/business/010-location.svg',
        'components' => [
            'index' => 'admin::settings.branches.branches',
        ],
    ],
];
