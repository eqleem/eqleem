<x-mail::message>
# رسالة جديدة من نموذج اتصل بنا

@if (filled($contact['name'] ?? null))
**الاسم:** {{ $contact['name'] }}
@endif

@if (filled($contact['email'] ?? null))
**البريد:** {{ $contact['email'] }}
@endif

@if (filled($contact['phone'] ?? null))
**الجوال:** {{ $contact['phone'] }}
@endif

@if (filled($contact['address'] ?? null))
**العنوان:** {{ $contact['address'] }}
@endif

@if (filled($contact['subject'] ?? null))
**الموضوع:** {{ $contact['subject'] }}
@endif

@if (filled($contact['message'] ?? null))
**الرسالة:**

{{ $contact['message'] }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
