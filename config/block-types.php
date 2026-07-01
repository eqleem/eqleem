<?php

return [
    'link' => [
        'order' => 1,
        'slug' => 'link',
        'name' => 'رابط',
        'description' => 'رابط بسيط مع عنوان ووجهة.',
        'icon' => 'assets/icons/tabler/Link.svg',
        'component' => 'tenant::components.link',
    ],
    'block-link' => [
        'order' => 2,
        'slug' => 'block-link',
        'name' => 'بلوك رابط',
        'description' => 'بطاقة رابط مع أيقونة وعنوان ووصف.',
        'icon' => 'assets/icons/tabler/ExternalLink.svg',
        'component' => 'tenant::components.block-link',
    ],
];
