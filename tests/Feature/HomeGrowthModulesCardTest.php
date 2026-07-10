<?php

it('renders the floating growth modules card on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('قسم جديد مفعّل', false)
        ->assertSee('باقتك تنمو معك', false)
        ->assertSee('الأقسام المفعّلة', false)
        ->assertSee('6 / 12', false)
        ->assertSee('أضف قسمًا جديدًا', false)
        ->assertSee('+42% نمو', false)
        ->assertSee('أضف أقسامًا جديدة', false)
        ->assertSee('توسّع بخدمات جديدة', false)
        ->assertSee('إدارة الفريق', false)
        ->assertSee('إحصاءات الأداء', false)
        ->assertSee('تكاملات خارجية', false)
        ->assertSee('ربط التطبيقات', false)
        ->assertSee('تحديثات مستمرة', false)
        ->assertSee('hugeicons:dashboard-square-add', false)
        ->assertSee('hugeicons:analytics-01', false);
});
