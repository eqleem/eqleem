<?php

it('renders partner logos on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('assets/images/partners/tabby.svg', false)
        ->assertSee('assets/images/partners/tamara.svg', false)
        ->assertSee('assets/images/partners/mada.svg', false)
        ->assertSee('assets/images/partners/aramex.svg', false)
        ->assertSee('assets/images/partners/fedex.svg', false)
        ->assertSee('assets/images/partners/visa.svg', false)
        ->assertSee('assets/images/partners/mastercard.svg', false)
        ->assertSee('alt="تابي"', false)
        ->assertSee('alt="تمارا"', false)
        ->assertSee('alt="مدى"', false)
        ->assertSee('alt="أرامكس"', false)
        ->assertSee('alt="فيديكس"', false)
        ->assertSee('alt="فيزا"', false)
        ->assertSee('alt="ماستركارد"', false);
});
