<?php

it('renders the pre-footer cta section on the home page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('استثمر في حضور رقمي يبقى معك، لا في حملة إعلانية تنتهي غداً', false)
        ->assertSee('ماذا لو كانت صفحتك', false)
        ->assertSee('أفضل موظف مبيعات عندك؟', false)
        ->assertSee('كل ما يحتاجه عملاؤك في مكان واحد، وكل ما تحتاجه لإدارة حضورك الرقمي في منصة واحدة.', false)
        ->assertSee('صفحة تبيع، وتحجز، وتعزز الثقة، وتنمو معك... من أول يوم.', false)
        ->assertSee('أنشئ صفحتي الآن، مجاناً', false);
});
