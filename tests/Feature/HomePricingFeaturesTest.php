<?php

it('renders pricing plan features with included and excluded items', function () {
    $response = $this->get(route('home'))
        ->assertSuccessful()
        // بداية — included
        ->assertSee('صفحة احترافية', false)
        ->assertSee('رابط ثابت مجاني', false)
        ->assertSee('كيو آر كود QR Code', false)
        ->assertSee('استقبل الطلبات والمشتريات لمنتجاتك وخدماتك', false)
        ->assertSee('خيارات شحن مخصصة', false)
        // بداية — excluded (faded)
        ->assertSee('دومين مخصص', false)
        ->assertSee('ايميل رسمي', false)
        ->assertSee('تفعيل بوابات الدفع الرقمية', false)
        // انطلاق
        ->assertSee('كل مزايا باقة بداية، بالإضافة إلى', false)
        ->assertSee('ايميل رسمي عدد 2 ايميلات رسمية', false)
        ->assertSee('استقبال جميع بوابات الدفع + تابي + تمارا', false)
        ->assertSee('ربط 240+ بوابة شحن لمنتجاتك الملموسة', false)
        ->assertSee('إحصاءات متقدمة', false)
        ->assertSee('تكاملات أساسية Integrations', false)
        ->assertSee('إدارة الفريق والصلاحيات', false)
        // نمو
        ->assertSee('كل مزايا باقة انطلاق، بالإضافة إلى', false)
        ->assertSee('إدارة فريق العمل حتى 5 أعضاء', false)
        ->assertSee('ايميل رسمي عدد 25', false)
        ->assertSee('قوالب مخصصة', false)
        ->assertSee('إزالة شعار إقليم', false)
        ->assertSee('جميع التكاملات Integrations', false)
        ->assertSee('hugeicons:tick-02', false)
        ->assertSee('hugeicons:cancel-01', false)
        ->assertSee('ابدأ الآن', false)
        ->assertSee('اختر انطلاق', false)
        ->assertSee('اختر نمو', false)
        ->assertSee('hugeicons:arrow-left-02', false);

    expect(substr_count($response->getContent(), 'hugeicons:cancel-01'))->toBeGreaterThanOrEqual(5);
    expect(substr_count($response->getContent(), 'text-zinc-400/70'))->toBeGreaterThanOrEqual(3);
});
