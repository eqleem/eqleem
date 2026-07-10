<?php

it('renders compact mobile feature tags across all home sections', function () {
    $response = $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('px-3 py-1.5 lg:px-4 lg:py-2', false)
        ->assertSee('gap-1.5 lg:gap-2', false);

    // Visible on mobile across sections
    $response
        ->assertSee('متجر إلكتروني', false)
        ->assertSee('حجز المواعيد', false)
        ->assertSee('الدفع الإلكتروني', false)
        ->assertSee('المنتجات الرقمية', false)
        ->assertSee('طلب خدمة', false)
        ->assertSee('زر "احجز الآن"', false)
        ->assertSee('دومين مخصص', false)
        ->assertSee('بريد إلكتروني رسمي', false)
        ->assertSee('ألوان هويتك', false)
        ->assertSee('الخطوط والشعار', false)
        ->assertSee('تصميم متوافق مع الجوال', false)
        ->assertSee('دعم لغات متعددة', false)
        ->assertSee('تقييمات العملاء', false)
        ->assertSee('الاعتمادات', false)
        ->assertSee('معرض الأعمال', false)
        ->assertSee('الضمانات', false)
        ->assertSee('الأسئلة الشائعة', false)
        ->assertSee('متابعة الطلب', false)
        ->assertSee('المدونة', false)
        ->assertSee('النشرة البريدية', false)
        ->assertSee('الفيديوهات', false)
        ->assertSee('البودكاست', false)
        ->assertSee('آخر الأخبار', false)
        ->assertSee('المقالات والدلائل', false)
        ->assertSee('أضف أقسامًا جديدة', false)
        ->assertSee('توسّع بخدمات جديدة', false)
        ->assertSee('إدارة الفريق', false)
        ->assertSee('إحصاءات الأداء', false)
        ->assertSee('تكاملات خارجية', false)
        ->assertSee('hugeicons:shopping-cart-01', false)
        ->assertSee('hugeicons:globe', false)
        ->assertSee('hugeicons:star', false)
        ->assertSee('hugeicons:quill-write-01', false)
        ->assertSee('hugeicons:dashboard-square-add', false)
        ->assertSee('bg-primary-500', false);

    // Hidden on mobile (last tags in later sections)
    $response
        ->assertSee('تواصل مباشر', false)
        ->assertSee('بدون شعار المنصة', false)
        ->assertSee('صور أغلفة مخصصة', false)
        ->assertSee('الفواتير', false)
        ->assertSee('معلومات التواصل', false)
        ->assertSee('تحديثات النشاط', false)
        ->assertSee('ربط التطبيقات', false)
        ->assertSee('تحديثات مستمرة', false);

    expect(substr_count($response->getContent(), 'hidden lg:flex'))->toBeGreaterThanOrEqual(7);
});
