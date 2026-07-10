<x-mail::message>
# رسالة جديدة من نموذج اتصل بنا

**الاسم:** {{ $contact['name'] }}

**البريد:** {{ $contact['email'] }}

**الموضوع:** {{ $contact['subject'] }}

**الرسالة:**

{{ $contact['message'] }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
