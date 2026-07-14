<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة جديدة من اتصل بنا</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; direction: rtl; background-color: #f0f4f8; padding: 24px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #219EBD 0%, #176B8A 100%); border-radius: 16px 16px 0 0; padding: 36px 30px; text-align: center;">
            @if (filled($tenant?->name))
                <p style="color: rgba(255,255,255,0.85); font-size: 14px; margin: 0 0 8px;">إشعار جديد لـ</p>
                <h1 style="color: #ffffff; font-size: 26px; margin: 0 0 12px; font-weight: bold;">{{ $tenant->name }}</h1>
            @else
                <h1 style="color: #ffffff; font-size: 26px; margin: 0 0 12px; font-weight: bold;">إقليم</h1>
            @endif
            <p style="color: rgba(255,255,255,0.95); font-size: 18px; margin: 0; font-weight: bold;">وصلتكم رسالة جديدة من نموذج اتصل بنا</p>
        </div>

        <div style="background-color: #ffffff; padding: 32px 30px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <p style="color: #374151; font-size: 16px; line-height: 1.8; margin: 0 0 24px;">
                مرحباً، وصلكم استفسار جديد عبر صفحة اتصل بنا. يمكنك الرد مباشرة على المرسل من خلال زر «رد» في بريدك، أو مراجعة الصفحة من لوحة التحكم.
            </p>

            @if (filled($contact['subject'] ?? null))
                <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: center;">
                    <p style="color: #0369a1; font-size: 13px; margin: 0 0 8px; font-weight: bold;">موضوع الرسالة</p>
                    <p style="color: #0c4a6e; font-size: 18px; margin: 0; font-weight: bold; line-height: 1.5;">{{ $contact['subject'] }}</p>
                </div>
            @endif

            <div style="background-color: #f9fafb; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                <h2 style="color: #111827; font-size: 16px; margin: 0 0 14px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;">
                    بيانات المرسل
                </h2>
                <table style="width: 100%; border-collapse: collapse;">
                    @if (filled($contact['name'] ?? null))
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 35%; vertical-align: top;">الاسم</td>
                            <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: bold;">{{ $contact['name'] }}</td>
                        </tr>
                    @endif
                    @if (filled($contact['email'] ?? null))
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">البريد الإلكتروني</td>
                            <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: bold; direction: ltr; text-align: right;">
                                <a href="mailto:{{ $contact['email'] }}" style="color: #219EBD; text-decoration: none;">{{ $contact['email'] }}</a>
                            </td>
                        </tr>
                    @endif
                    @if (filled($contact['phone'] ?? null))
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">الجوال</td>
                            <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: bold; direction: ltr; text-align: right;">{{ $contact['phone'] }}</td>
                        </tr>
                    @endif
                    @if (filled($contact['address'] ?? null))
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280; font-size: 14px; vertical-align: top;">العنوان</td>
                            <td style="padding: 8px 0; color: #111827; font-size: 14px; font-weight: bold;">{{ $contact['address'] }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            @if (filled($contact['message'] ?? null))
                <div style="background-color: #ffffff; border: 1px solid #e5e7eb; border-right: 4px solid #219EBD; border-radius: 12px; padding: 20px; margin-bottom: 28px;">
                    <h2 style="color: #111827; font-size: 16px; margin: 0 0 12px; font-weight: bold;">نص الرسالة</h2>
                    <p style="color: #374151; font-size: 15px; line-height: 1.9; margin: 0; white-space: pre-wrap;">{{ $contact['message'] }}</p>
                </div>
            @endif

            @if (filled($managePageUrl))
                <div style="text-align: center; margin-top: 8px;">
                    <a href="{{ $managePageUrl }}" style="display: inline-block; background-color: #219EBD; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px;">
                        إدارة الصفحة من لوحة التحكم
                    </a>
                </div>

                <p style="color: #6b7280; font-size: 14px; line-height: 1.7; margin: 24px 0 0; text-align: center;">
                    يمكنك من لوحة التحكم تعديل صفحة اتصل بنا، ومتابعة الرسائل الواردة لصفحتك.
                </p>
            @endif
        </div>

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin-top: 20px; line-height: 1.6;">
            إشعار تلقائي من إقليم@if (filled($tenant?->name)) — {{ $tenant->name }}@endif<br>
            © {{ date('Y') }} إقليم
        </p>
    </div>
</body>
</html>
