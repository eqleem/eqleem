<?php

it('renders the terms page', function () {
    $this->get(route('terms'))
        ->assertSuccessful()
        ->assertSee('الشروط والأحكام', false)
        ->assertSee('القبول بالشروط', false);
});

it('renders the privacy page', function () {
    $this->get(route('privacy'))
        ->assertSuccessful()
        ->assertSee('سياسة الخصوصية', false)
        ->assertSee('البيانات التي نجمعها', false);
});

it('links legal pages from the home footer', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('terms'), false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('contact'), false);
});
