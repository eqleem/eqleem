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
        ->assertSee('حجز المواعيد', false)
        ->assertSee('الدفع الإلكتروني', false)
        ->assertSee('المنتجات الرقمية', false)
        ->assertSee('طلب خدمة', false)
        ->assertSee('زر "احجز الآن"', false)
        ->assertSee('دومين مخصص', false)
        ->assertSee('منطقة العميل', false)
        ->assertSee('المدونة', false)
        ->assertSee('أضف أقسامًا جديدة', false)
        ->assertSee('hugeicons:shopping-cart-01', false)
        ->assertSee('bg-primary-500', false);

    // Hidden on mobile (last two tags per section)
    $response
        ->assertSee('تواصل مباشر', false)
        ->assertSee('عرض خاص', false)
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
