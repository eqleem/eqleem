<?php

return [
    'general-info' => [
        'order' => 1,
        'slug' => 'general-info',
        'name' => 'معلومات المشروع',
        'description' => 'بيانات الصفحة الأساسية',
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
];
