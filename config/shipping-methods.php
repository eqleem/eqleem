<?php

return [
    'eqleem-ship' => [
        'order' => 1,
        'slug' => 'eqleem-ship',
        'name' => 'إقليم شيب - شحن عادي',
        'description' => 'خيار الشحن المفضل، شحن داخلي ودولي، للشحن العادي الجاف.',
        'icon' => 'assets/icons/ecommerce/009-cargo ship.svg',
        'components' => [
            'modal' => 'admin::settings.shipping-options.modals.eqleem-ship',
        ],
        'defaults' => [
            'label' => '',
            'domestic_price' => null,
            'gulf_price' => null,
            'international_price' => null,
        ],
    ],
];
