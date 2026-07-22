<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كود الدخول</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; direction: rtl; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1 style="color: #333; text-align: center; margin-bottom: 20px;">كود الدخول إلى لوحة التحكم</h1>

        <p style="color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
            مرحباً {{ $name }}، استخدم الكود التالي لإتمام تسجيل الدخول:
        </p>

        <div style="text-align: center; margin: 20px 0 30px;">
            <span style="display: inline-block; background-color: #f8fafc; color: #111827; padding: 18px 32px; border-radius: 12px; font-weight: bold; font-size: 32px; letter-spacing: 8px; border: 1px solid #e5e7eb;">
                {{ $code }}
            </span>
        </div>

        <p style="color: #999; font-size: 14px; line-height: 1.6; margin-top: 20px;">
            إذا لم تطلب هذا الكود، يمكنك تجاهل هذه الرسالة.
        </p>

        <p style="color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
            الكود صالح لمدة 10 دقائق فقط.
        </p>
    </div>
</body>
</html>
