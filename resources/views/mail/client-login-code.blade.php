<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط تسجيل الدخول</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; direction: rtl; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #333; text-align: center; margin-bottom: 20px;">دخول / تسجيل العملاء</h1>

        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
            مرحباً، اضغط على الزر التالي لتسجيل الدخول أو إنشاء حساب عميل في <strong>{{ $tenant->name }}</strong>:
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a
                href="{{ $loginUrl }}"
                style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 14px 28px; border-radius: 12px; font-weight: bold; font-size: 16px; text-decoration: none;"
            >
                الدخول أو إنشاء حساب
            </a>
        </div>

        <p style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 12px; text-align: center;">
            أو أدخل هذا الكود في صفحة الدخول:
        </p>

        <div style="text-align: center; margin: 20px 0 30px;">
            <span style="display: inline-block; background-color: #f8fafc; color: #111827; padding: 18px 32px; border-radius: 12px; font-weight: bold; font-size: 32px; letter-spacing: 8px; border: 1px solid #e5e7eb;">
                {{ $code }}
            </span>
        </div>

        <p style="color: #999; font-size: 14px; line-height: 1.6; margin-top: 20px;">
            إذا لم تطلب هذا الرابط، يمكنك تجاهل هذه الرسالة.
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            الرابط والكود صالحان لمدة 10 دقائق فقط.
        </p>
    </div>
</body>
</html>
