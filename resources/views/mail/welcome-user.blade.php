<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في إقليم</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; direction: rtl; background-color: #f0f4f8; padding: 24px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #219EBD 0%, #176B8A 100%); border-radius: 16px 16px 0 0; padding: 40px 30px; text-align: center;">
            <p style="color: rgba(255,255,255,0.85); font-size: 14px; margin: 0 0 8px;">مرحباً بك في</p>
            <h1 style="color: #ffffff; font-size: 28px; margin: 0 0 12px; font-weight: bold;">إقليم</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 18px; margin: 0;">أهلاً {{ $user->name }} 👋</p>
        </div>

        <div style="background-color: #ffffff; padding: 32px 30px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <p style="color: #374151; font-size: 16px; line-height: 1.8; margin: 0 0 20px;">
                يسعدنا انضمامك إلى <strong>إقليم</strong> — المنصة التي تمنحك صفحة احترافية لعرض أعمالك، خدماتك، ومنتجاتك في مكان واحد أنيق وسهل الإدارة.
            </p>

            <p style="color: #374151; font-size: 16px; line-height: 1.8; margin: 0 0 28px;">
                صفحتك <strong>{{ $tenant->name }}</strong> جاهزة الآن ويمكنك مشاركتها مع عملائك فوراً. كل ما تحتاجه لبناء حضورك الرقمي بين يديك — بدون تعقيد، وبدون خبرة تقنية.
            </p>

            <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 28px; text-align: center;">
                <p style="color: #0369a1; font-size: 14px; margin: 0 0 8px; font-weight: bold;">رابط صفحتك الحالية</p>
                <p style="color: #0c4a6e; font-size: 13px; margin: 0 0 16px; word-break: break-all; direction: ltr;">{{ $pageUrl }}</p>
                <a href="{{ $pageUrl }}" style="display: inline-block; background-color: #ffffff; color: #219EBD; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; border: 2px solid #219EBD;">
                    زيارة صفحتي
                </a>
            </div>

            <h2 style="color: #111827; font-size: 18px; margin: 0 0 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;">
                خطواتك التالية لإكمال صفحتك
            </h2>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px;">
                <tr>
                    <td style="padding: 10px 0; vertical-align: top; width: 32px;">
                        <span style="display: inline-block; background-color: #219EBD; color: #fff; width: 24px; height: 24px; border-radius: 50%; text-align: center; line-height: 24px; font-size: 13px; font-weight: bold;">١</span>
                    </td>
                    <td style="padding: 10px 0; color: #374151; font-size: 15px; line-height: 1.6;">
                        <strong>أكمل بياناتك الأساسية</strong> — أضف شعارك، وصف نشاطك، ووسائل التواصل.
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; vertical-align: top;">
                        <span style="display: inline-block; background-color: #219EBD; color: #fff; width: 24px; height: 24px; border-radius: 50%; text-align: center; line-height: 24px; font-size: 13px; font-weight: bold;">٢</span>
                    </td>
                    <td style="padding: 10px 0; color: #374151; font-size: 15px; line-height: 1.6;">
                        <strong>خصّص تصميم صفحتك</strong> — اختر الألوان والأقسام التي تعكس هويتك.
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; vertical-align: top;">
                        <span style="display: inline-block; background-color: #219EBD; color: #fff; width: 24px; height: 24px; border-radius: 50%; text-align: center; line-height: 24px; font-size: 13px; font-weight: bold;">٣</span>
                    </td>
                    <td style="padding: 10px 0; color: #374151; font-size: 15px; line-height: 1.6;">
                        <strong>أضف محتواك</strong> — خدماتك، منتجاتك، مدونتك، أو أي قسم يناسب عملك.
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; vertical-align: top;">
                        <span style="display: inline-block; background-color: #219EBD; color: #fff; width: 24px; height: 24px; border-radius: 50%; text-align: center; line-height: 24px; font-size: 13px; font-weight: bold;">٤</span>
                    </td>
                    <td style="padding: 10px 0; color: #374151; font-size: 15px; line-height: 1.6;">
                        <strong>شارك رابط صفحتك</strong> — وابدأ باستقبال زوارك وعملائك من اليوم.
                    </td>
                </tr>
            </table>

            <div style="text-align: center; margin: 32px 0 24px;">
                <a href="{{ $dashboardUrl }}" style="display: inline-block; background-color: #219EBD; color: #ffffff; padding: 16px 36px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 17px; margin-bottom: 12px;">
                    ادخل لوحة التحكم وابدأ الآن
                </a>
                <br>
                <a href="{{ $managePageUrl }}" style="display: inline-block; color: #219EBD; font-size: 14px; text-decoration: underline; margin-top: 8px;">
                    أو انتقل مباشرةً لإدارة صفحتك
                </a>
            </div>

            <p style="color: #6b7280; font-size: 15px; line-height: 1.7; margin: 0; text-align: center; font-style: italic;">
                «مع إقليم، صفحتك ليست مجرد رابط — بل واجهة احترافية تروّج لك وتبني ثقة عملائك.»
            </p>
        </div>

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin-top: 20px; line-height: 1.6;">
            إذا لم تقم بإنشاء هذا الحساب، يمكنك تجاهل هذه الرسالة بأمان.<br>
            © {{ date('Y') }} إقليم — جميع الحقوق محفوظة
        </p>
    </div>
</body>
</html>
