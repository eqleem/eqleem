<?php

it('renders the floating conversion order card on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('طلب جديد الآن', false)
        ->assertSee('طلب جديد', false)
        ->assertSee('مدفوع', false)
        ->assertSee('حالة الطلب', false)
        ->assertSee('قيد التجهيز', false)
        ->assertSee('تجهيز', false)
        ->assertSee('مكتمل', false)
        ->assertSee('تم استلام الدفع', false)
        ->assertSee('باقة التصميم الاحترافي', false)
        ->assertSee('animate-[home-float_6s_ease-in-out_infinite]', false)
        ->assertSee('lg:col-span-5 order-2', false);
});
