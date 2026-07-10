<?php

it('renders the hero phone mockup with floating product cues on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('assets/images/hero-phone-screen.webp', false)
        ->assertSee('مثال لصفحة أعمال على الجوال', false)
        ->assertSee('طلب جديد', false)
        ->assertSee('باقة التصميم', false)
        ->assertSee('حجز موعد', false)
        ->assertSee('زوار الآن', false)
        ->assertSee('تواصل مباشر', false)
        ->assertSee('تقييم العملاء', false)
        ->assertSee('mdi:whatsapp', false)
        ->assertSee('hugeicons:calendar-03', false)
        ->assertSee('hugeicons:shopping-bag-01', false)
        ->assertSee('animate-[home-float_7s_ease-in-out_infinite]', false);
});
