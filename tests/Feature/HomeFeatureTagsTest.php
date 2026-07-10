<?php

it('renders compact mobile feature tags across all home sections', function () {
    $response = $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('px-2 py-1 lg:px-4 lg:py-2', false)
        ->assertSee('text-xs lg:text-sm', false)
        ->assertSee('gap-1.5 lg:gap-2', false);

    // Visible on mobile across sections
    $response
        ->assertSee('متجر إلكتروني', false)
        ->assertSee('دومين مخصص', false)
        ->assertSee('منطقة العميل', false)
        ->assertSee('المدونة', false)
        ->assertSee('أضف أقسامًا جديدة', false);

    // Hidden on mobile (last two tags per section)
    $response
        ->assertSee('تأجير الوحدات', false)
        ->assertSee('أزرار اتخاذ الإجراء (CTA)', false)
        ->assertSee('رابط واحد دائم', false)
        ->assertSee('دعم لغات متعددة', false)
        ->assertSee('الفواتير والمشتريات', false)
        ->assertSee('الأسئلة الشائعة', false)
        ->assertSee('آخر الأخبار', false)
        ->assertSee('تحديثات النشاط', false)
        ->assertSee('ربط التطبيقات', false)
        ->assertSee('تحديثات مستمرة', false);

    expect(substr_count($response->getContent(), 'hidden lg:flex'))->toBeGreaterThanOrEqual(10);
});
