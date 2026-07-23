<?php

return [
    /*
    | Content types that must link to a specific item (no section/index URL).
    | Example: pages and forms have no tenant index route worth linking to.
    */
    'requires_item' => [
        'pages',
        'forms',
    ],

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
        'courses' => [
            'index' => 'tenant.courses.index',
            'detail' => 'tenant.courses.detail',
        ],
        'digital-products' => [
            'index' => 'tenant.digital-products.index',
            'detail' => 'tenant.digital-products.detail',
        ],
        'digital-services' => [
            'index' => 'tenant.digital-services.index',
            'detail' => 'tenant.digital-services.detail',
        ],
        'on-demand-services' => [
            'index' => 'tenant.on-demand-services.index',
            'detail' => 'tenant.on-demand-services.detail',
        ],
        'reviews' => [
            'index' => 'tenant.pages.reviews',
            'detail' => null,
        ],
        'pages' => [
            'index' => null,
            'detail' => 'tenant.page.detail',
        ],
        'forms' => [
            'index' => null,
            // Forms open in a modal on the tenant site (no dedicated detail route).
            'detail' => null,
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
        'courses' => 'hugeicons:presentation-06',
        'digital-products' => 'hugeicons:file-download',
        'digital-services' => 'hugeicons:customer-service-01',
        'on-demand-services' => 'hugeicons:ruler',
        'reviews' => 'hugeicons:star',
        'pages' => 'hugeicons:file-01',
        'form' => 'hugeicons:file-01',
        'booking' => 'hugeicons:calendar-03',
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
        'courses' => 'دورة محددة',
        'digital-products' => 'منتج رقمي محدد',
        'digital-services' => 'خدمة رقمية محددة',
        'on-demand-services' => 'خدمة حسب الطلب محددة',
        'pages' => 'رابط صفحة',
        'forms' => 'رابط نموذج',
    ],

    'block_link' => [
        'sections' => [
            'store' => [
                'title' => 'المتجر الإلكتروني',
                'description' => 'مجموعة مختارة من المنتجات.',
            ],
            'blog' => [
                'title' => 'المدونة',
                'description' => 'مقالات ونصائح في المدونة.',
            ],
            'newsletter' => [
                'title' => 'النشرة البريدية',
                'description' => 'اشترك في النشرة البريدية وانضم إلى مجتمع المهتمين.',
            ],
            'portfolio' => [
                'title' => 'أعمالنا',
                'description' => 'معرض مختار لأعمالنا المنفّذة.',
            ],
            'menu' => [
                'title' => 'قائمة الطعام',
                'description' => 'قائمة وجبات طازجة مع أحجام وإضافات متنوعة.',
            ],
            'services' => [
                'title' => 'خدماتنا',
                'description' => 'نقدم خدمات باحترافية.',
            ],
            'unit-rental' => [
                'title' => 'تأجير الوحدات',
                'description' => 'اختر وحدتك المناسبة من الاستديوهات والشقق.',
            ],
            'courses' => [
                'title' => 'الدورات',
                'description' => 'دورات عملية لتعلم التشطيبات والديكور خطوة بخطوة مع تمارين وتطبيقات واقعية.',
            ],
            'digital-products' => [
                'title' => 'المنتجات الرقمية',
                'description' => 'منتجات رقمية قابلة للتحميل والوصول الفوري.',
            ],
            'digital-services' => [
                'title' => 'الخدمات الرقمية',
                'description' => 'خدمات رقمية احترافية مع مدة تسليم واضحة.',
            ],
            'on-demand-services' => [
                'title' => 'خدمات حسب الطلب',
                'description' => 'خدمات مسعّرة حسب الوحدة مثل المتر والقطعة والساعة.',
            ],
            'reviews' => [
                'title' => 'التقييمات',
                'description' => 'تقييمات وآراء عملائنا.',
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
            'courses' => [
                'title' => '',
                'description' => 'استعرض دورة تدريبية محددة بالتفصيل.',
            ],
            'digital-products' => [
                'title' => '',
                'description' => 'تصفّح منتجاً رقمياً محدداً بالتفصيل.',
            ],
            'digital-services' => [
                'title' => '',
                'description' => 'تعرّف على خدمة رقمية محددة بالتفصيل.',
            ],
            'on-demand-services' => [
                'title' => '',
                'description' => 'تعرّف على خدمة حسب الطلب محددة بالتفصيل.',
            ],
            'pages' => [
                'title' => '',
                'description' => 'انتقل إلى صفحة داخلية من صفحات الموقع.',
            ],
            'forms' => [
                'title' => '',
                'description' => 'افتح نموذجاً محدداً لجمع البيانات أو التواصل.',
            ],
        ],
    ],
];
