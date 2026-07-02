<?php

return [
    'routes' => [
        'store' => [
            'index' => 'tenant.store.index',
            'detail' => 'tenant.store.detail',
        ],
        'blog' => [
            'index' => 'tenant.blog.index',
            'detail' => 'tenant.blog.detail',
        ],
        'newsletter' => [
            'index' => 'tenant.newsletter.index',
            'detail' => 'tenant.newsletter.detail',
        ],
        'portfolio' => [
            'index' => 'tenant.portfolio.index',
            'detail' => 'tenant.portfolio.detail',
        ],
        'menu' => [
            'index' => 'tenant.menu.index',
            'detail' => null,
        ],
        'services' => [
            'index' => 'tenant.services.index',
            'detail' => 'tenant.services.detail',
        ],
        'unit-rental' => [
            'index' => 'tenant.properties-rental.index',
            'detail' => 'tenant.properties-rental.detail',
        ],
    ],

    'icons' => [
        'store' => 'hugeicons:store-02',
        'blog' => 'hugeicons:book-open-text',
        'newsletter' => 'hugeicons:mail-at-sign-02',
        'portfolio' => 'hugeicons:folder-library',
        'menu' => 'hugeicons:restaurant-01',
        'services' => 'hugeicons:travel-bag',
        'unit-rental' => 'hugeicons:bed-double',
        'form' => 'hugeicons:file-01',
        'external' => 'hugeicons:link-04',
    ],

    'item_labels' => [
        'store' => 'منتج محدد',
        'blog' => 'تدوينة محددة',
        'newsletter' => 'عدد محدد من النشرة',
        'portfolio' => 'عمل محدد',
        'menu' => 'عنصر محدد من المنيو',
        'services' => 'خدمة محددة',
        'unit-rental' => 'وحدة محددة',
    ],

    'block_link' => [
        'sections' => [
            'store' => [
                'title' => 'المتجر',
                'description' => 'مجموعة مختارة من تشطيبات ديكور المنزل، باركيه وبديل الرخام والخشب والشيبورد.',
            ],
            'blog' => [
                'title' => 'المدونة',
                'description' => 'مقالات ونصائح في التشطيبات والديكور: الباركيه، بديل الرخام، بديل الخشب، والشيبورد.',
            ],
            'newsletter' => [
                'title' => 'النشرة البريدية',
                'description' => 'اشترك في النشرة البريدية وانضم إلى مجتمع المهتمين في أعمال الفن والديكور.',
            ],
            'portfolio' => [
                'title' => 'أعمالنا في التنفيذ',
                'description' => 'معرض مختار لأعمالنا المنفّذة في التشطيبات الداخلية، من الباركيه إلى بدائل الرخام والخشب.',
            ],
            'menu' => [
                'title' => 'قائمة الطعام',
                'description' => 'قائمة وجبات طازجة مع أحجام وإضافات متنوعة، وأسرع طريقة لإضافتها إلى السلة.',
            ],
            'services' => [
                'title' => 'خدماتنا',
                'description' => 'نقدم خدمات التشطيبات والديكور الداخلي باحترافية، من التصميم حتى التنفيذ بجودة عالية.',
            ],
            'unit-rental' => [
                'title' => 'تأجير الوحدات',
                'description' => 'اختر وحدتك المناسبة من الاستديوهات والشقق، حدّد تاريخ الدخول والخروج واحجز مباشرة بسهولة.',
            ],
        ],
        'items' => [
            'store' => [
                'title' => '',
                'description' => 'تصفّح منتجاً محدداً من المتجر.',
            ],
            'blog' => [
                'title' => '',
                'description' => 'اقرأ تدوينة محددة من المدونة.',
            ],
            'newsletter' => [
                'title' => '',
                'description' => 'اطّلع على عدد محدد من النشرة البريدية.',
            ],
            'portfolio' => [
                'title' => '',
                'description' => 'استعرض عملاً محدداً من معرض الأعمال.',
            ],
            'menu' => [
                'title' => '',
                'description' => 'اطّلع على عنصر محدد من قائمة الطعام.',
            ],
            'services' => [
                'title' => '',
                'description' => 'تعرّف على خدمة محددة بالتفصيل.',
            ],
            'unit-rental' => [
                'title' => '',
                'description' => 'استعرض وحدة محددة متاحة للتأجير.',
            ],
        ],
    ],
];
