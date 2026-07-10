<?php

it('renders the floating trust and reviews card on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('نشاط موثق', false)
        ->assertSee('كل شيء كان واضحًا، من الدفع حتى استلام الطلب.', false)
        ->assertSee('4.9', false)
        ->assertSee('+1,280', false)
        ->assertSee('97%', false)
        ->assertSee('يوصون بالخدمة', false)
        ->assertSee('+850', false)
        ->assertSee('عميل سعيد', false)
        ->assertSee('سجل تجاري', false)
        ->assertSee('وثيقة عمل حر', false)
        ->assertSee('تقييمات العملاء', false)
        ->assertSee('الاعتمادات', false)
        ->assertSee('متابعة الطلب', false)
        ->assertSee('معلومات التواصل', false)
        ->assertSee('hugeicons:star', false)
        ->assertSee('hugeicons:checkmark-badge-02', false);
});
