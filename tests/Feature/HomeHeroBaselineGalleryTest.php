<?php

it('renders the hero baseline gallery with labeled unsplash shots', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('hero-shots', false)
        ->assertSee('hero-shot-frame', false)
        ->assertSee('images.unsplash.com', false)
        ->assertSee('متجر أزياء', false)
        ->assertSee('صالون عناية', false)
        ->assertSee('حلويات فاخرة', false)
        ->assertSee('arcticons:emoji-arrow-pointing-rightwards-then-curving-downwards', false)
        ->assertSee('--visible: .90', false)
        ->assertSee('height: calc(var(--shot-height) * var(--visible))', false)
        ->assertSee('height: var(--shot-height)', false)
        ->assertDontSee(':has(.hero-shot:hover)', false);
});
