<?php

it('renders the hero phone mockup with a designed mini business page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('assets/images/hero-phone-screen.webp', false)
        ->assertSee('استوديو أُفق', false)
        ->assertSee('الرياض', false)
        ->assertSee('نصمّم وننفّذ مساحات أنيقة وعملية', false)
        ->assertSee('حجز موعد', false)
        ->assertSee('تواصل الآن', false)
        ->assertSee('المتجر', false)
        ->assertSee('الخدمات', false)
        ->assertSee('أعمالنا', false)
        ->assertSee('الباقات', false)
        ->assertSee('أنشئ صفحتك مثل هذه مجاناً', false)
        ->assertSee('طلب جديد', false)
        ->assertSee('تقييم العملاء', false)
        ->assertSee('animate-[home-float_7s_ease-in-out_infinite]', false);
});
