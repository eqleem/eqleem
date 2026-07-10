<?php

it('renders the floating brand identity browser card on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('تم تطبيق هوية علامتك', false)
        ->assertSee('yourbrand.sa', false)
        ->assertSee('email@yourbrand.sa', false)
        ->assertSee('SSL · Secure', false)
        ->assertSee('ألوان الهوية', false)
        ->assertSee('بدون شعار المنصة', false)
        ->assertSee('صور أغلفة مخصصة', false)
        ->assertSee('hugeicons:globe', false)
        ->assertSee('hugeicons:security-check', false);
});
