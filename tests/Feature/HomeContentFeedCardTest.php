<?php

it('renders the floating content feed card on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('محتوى جديد نُشر', false)
        ->assertSee('أحدث المقالات', false)
        ->assertSee('كيف تختار الهوية البصرية؟', false)
        ->assertSee('فيديو جديد', false)
        ->assertSee('كيف تزيد مبيعاتك في رمضان؟', false)
        ->assertSee('12K مشاهدة', false)
        ->assertSee('اشترك الآن', false)
        ->assertSee('18,000 مشترك', false)
        ->assertSee('المدونة', false)
        ->assertSee('النشرة البريدية', false)
        ->assertSee('البودكاست', false)
        ->assertSee('آخر الأخبار', false)
        ->assertSee('المقالات والدلائل', false)
        ->assertSee('تحديثات النشاط', false)
        ->assertSee('hugeicons:quill-write-01', false)
        ->assertSee('hugeicons:video-01', false);
});
