<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الطلب</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; direction: rtl; background-color: #f0f4f8; padding: 24px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #219EBD 0%, #176B8A 100%); border-radius: 16px 16px 0 0; padding: 36px 30px; text-align: center;">
            <p style="color: rgba(255,255,255,0.85); font-size: 14px; margin: 0 0 8px;">شكراً لطلبك من</p>
            <h1 style="color: #ffffff; font-size: 26px; margin: 0 0 12px; font-weight: bold;">{{ $tenant->name }}</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 17px; margin: 0;">أهلاً {{ $customerName }} 👋</p>
        </div>

        <div style="background-color: #ffffff; padding: 32px 30px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <p style="color: #374151; font-size: 16px; line-height: 1.8; margin: 0 0 24px;">
                تم استلام طلبك بنجاح. فيما يلي تفاصيل طلبك:
            </p>

            <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 28px; text-align: center;">
                <p style="color: #0369a1; font-size: 14px; margin: 0 0 8px; font-weight: bold;">رقم الطلب</p>
                <p style="color: #0c4a6e; font-size: 28px; margin: 0; font-weight: bold; letter-spacing: 2px; direction: ltr;">#{{ $order->number }}</p>
                <p style="color: #6b7280; font-size: 13px; margin: 12px 0 0;">{{ $order->issued_at?->translatedFormat('l j F Y — H:i') }}</p>
            </div>

            <h2 style="color: #111827; font-size: 17px; margin: 0 0 14px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;">
                تفاصيل الطلب
            </h2>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                @foreach ($items as $item)
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top;">
                            <p style="color: #111827; font-size: 15px; font-weight: bold; margin: 0 0 4px;">{{ $item->name }}</p>
                            <p style="color: #6b7280; font-size: 13px; margin: 0;">
                                {{ $item->type_label }} — الكمية: {{ $item->qty }}
                            </p>
                        </td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f3f4f6; text-align: left; vertical-align: top; white-space: nowrap; direction: ltr;">
                            <span style="color: #111827; font-size: 15px; font-weight: bold;">{{ money_format($item->line_total, currency: $order->currency_code) }}</span>
                        </td>
                    </tr>
                @endforeach
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px;">
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">المجموع الفرعي</td>
                    <td style="padding: 8px 0; color: #374151; font-size: 14px; text-align: left; direction: ltr;">{{ money_format($order->subtotal, currency: $order->currency_code) }}</td>
                </tr>
                @if (data_get($order->meta, 'shipping_fee', 0) > 0)
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">رسوم الشحن ({{ $order->shippingMethodLabel() }})</td>
                        <td style="padding: 8px 0; color: #374151; font-size: 14px; text-align: left; direction: ltr;">{{ money_format(data_get($order->meta, 'shipping_fee'), currency: $order->currency_code) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding: 12px 0 0; color: #111827; font-size: 16px; font-weight: bold; border-top: 2px solid #e5e7eb;">الإجمالي</td>
                    <td style="padding: 12px 0 0; color: #111827; font-size: 18px; font-weight: bold; text-align: left; direction: ltr; border-top: 2px solid #e5e7eb;">{{ money_format($order->grand_total, currency: $order->currency_code) }}</td>
                </tr>
            </table>

            <div style="background-color: #f9fafb; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px; width: 40%;">طريقة الشحن</td>
                        <td style="padding: 6px 0; color: #374151; font-size: 14px; font-weight: bold;">{{ $order->shippingMethodLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">طريقة الدفع</td>
                        <td style="padding: 6px 0; color: #374151; font-size: 14px; font-weight: bold;">{{ $order->paymentMethodLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">حالة الدفع</td>
                        <td style="padding: 6px 0; color: #374151; font-size: 14px; font-weight: bold;">{{ $order->paymentStatusLabel() }}</td>
                    </tr>
                </table>
            </div>

            <p style="color: #6b7280; font-size: 15px; line-height: 1.7; margin: 0; text-align: center;">
                سنقوم بمعالجة طلبك في أقرب وقت. إذا كان لديك أي استفسار، لا تتردد في التواصل معنا.
            </p>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{{ $tenant->url }}" style="display: inline-block; background-color: #219EBD; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px;">
                    زيارة المتجر
                </a>
            </div>
        </div>

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin-top: 20px; line-height: 1.6;">
            هذه رسالة تأكيد تلقائية من {{ $tenant->name }}.<br>
            © {{ date('Y') }} {{ $tenant->name }}
        </p>
    </div>
</body>
</html>
