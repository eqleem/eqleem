<?php

return [
    'bank-transfer' => [
        'order' => 1,
        'slug' => 'bank-transfer',
        'name' => 'التحويل البنكي',
        'description' => 'اسمح لعملائك بالدفع بواسطة التحويل البنكي إلى حساباتك مباشرة.',
        'icon' => 'assets/images/bank-transfer.png',
        'components' => [
            'modal' => 'admin::settings.payment-options.modals.bank-transfer',
            'checkout' => 'tenant-theme::components.checkout.payment.bank-transfer',
        ],
        'defaults' => [
            'accounts' => [],
        ],
    ],
    'credit-card' => [
        'order' => 2,
        'slug' => 'credit-card',
        'name' => 'البطاقة الإئتمانية',
        'description' => 'مدفوعات كتالوج: الدفع بواسطة البطاقات الإئتمانية، فيز وماستركارد ومدى.',
        'icon' => 'assets/images/credit-card-payment.svg',
        'components' => [
            'modal' => 'admin::settings.payment-options.modals.credit-card',
            'checkout' => 'tenant-theme::components.checkout.payment.credit-card',
        ],
        'defaults' => [
            'label' => '',
            'description' => '',
        ],
    ],
    'cash-on-delivery' => [
        'order' => 3,
        'slug' => 'cash-on-delivery',
        'name' => 'الدفع عند الاستلام',
        'description' => 'اسمح لعملائك بالدفع نقداً عند استلام الشحنة. متاح للمنتجات القابلة للشحن فقط.',
        'icon' => 'assets/images/cod-payment.webp',
        'components' => [
            'modal' => 'admin::settings.payment-options.modals.cash-on-delivery',
            'checkout' => 'tenant-theme::components.checkout.payment.cash-on-delivery',
        ],
        'defaults' => [
            'min_limit' => null,
            'label' => '',
            'description' => '',
        ],
    ],
    'tabby' => [
        'order' => 4,
        'slug' => 'tabby',
        'name' => 'تابي',
        'description' => 'قسّم مشتريات عملائك على 4 دفعات بدون رسوم.',
        'icon' => 'assets/images/tabby-payment.webp',
        'components' => [
            'modal' => 'admin::settings.payment-options.modals.tabby',
            'checkout' => 'tenant-theme::components.checkout.payment.tabby',
        ],
        'defaults' => [
            'public_key' => '',
            'secret_key' => '',
            'min_limit' => null,
            'max_limit' => null,
            'label' => '',
            'description' => '',
        ],
    ],
    'tamara' => [
        'order' => 5,
        'slug' => 'tamara',
        'name' => 'تمارا',
        'description' => 'قسّم مشتريات عملائك على دفعات مريحة.',
        'icon' => 'assets/images/tamara_installment_mini.webp',
        'components' => [
            'modal' => 'admin::settings.payment-options.modals.tamara',
            'checkout' => 'tenant-theme::components.checkout.payment.tamara',
        ],
        'defaults' => [
            'api_token' => '',
            'notification_token' => '',
            'min_limit' => null,
            'label' => '',
            'description' => '',
        ],
    ],
    'custom' => [
        'order' => 6,
        'slug' => 'custom',
        'name' => 'مخصص',
        'description' => 'أضف وسيلة دفع مخصصة باسم وتعليمات خاصة بك.',
        'icon' => 'assets/icons/tabler/cash-banknote.svg',
        'components' => [
            'modal' => 'admin::settings.payment-options.modals.custom',
            'checkout' => 'tenant-theme::components.checkout.payment.custom',
        ],
        'defaults' => [
            'label' => '',
            'description' => '',
            'instructions' => '',
        ],
    ],
];
