<?php

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('renders the contact page', function () {
    $this->get(route('contact'))
        ->assertSuccessful()
        ->assertSee('اتصل بنا', false)
        ->assertSee('إرسال الرسالة', false);
});

it('sends a contact message', function () {
    Mail::fake();

    Livewire::test('contact')
        ->set('name', 'أحمد')
        ->set('email', 'ahmad@example.com')
        ->set('subject', 'استفسار عن الباقات')
        ->set('message', 'أرغب بمعرفة المزيد عن باقة الانطلاق.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Mail::assertQueued(ContactMessage::class, function (ContactMessage $mail): bool {
        return $mail->contact['email'] === 'ahmad@example.com'
            && $mail->contact['subject'] === 'استفسار عن الباقات'
            && $mail->managePageUrl === route('admin.home');
    });
});

it('validates the contact form', function () {
    Livewire::test('contact')
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->set('subject', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'subject', 'message']);
});
