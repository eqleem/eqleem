<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب جديد</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; direction: rtl; background-color: #f0f4f8; padding: 24px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 16px 16px 0 0; padding: 36px 30px; text-align: center;">
            <p style="color: rgba(255,255,255,0.85); font-size: 14px; margin: 0 0 8px;">إشعار جديد لـ</p>
            <h1 style="color: #ffffff; font-size: 26px; margin: 0 0 12px; font-weight: bold;">{{ $tenant->name }}</h1>
            <p style="color: rgba(255,255,255,0.95); font-size: 18px; margin: 0; font-weight: bold;">🛒 لديك طلب جديد!</p>
        </div>

        <div style="background-color: #ffffff; padding: 32px 30px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <p style="color: #374151; font-size: 16px; line-height: 1.8; margin: 0 0 24px;">
                مرحباً {{ $owner->name }}، تم استلام طلب جديد من متجرك. فيما يلي التفاصيل:
            </p>

            <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: center;">
                <p style="color: #047857; font-size: 14px; margin: 0 0 8px; font-weight: bold;">رقم الطلب</p>
                <p style="color: #064e3b; font-size: 28px; margin: 0; font-weight: bold; letter-spacing: 2px; direction: ltr;">#{{ $order->number }}</p>
                <p style="color: #6b7280; font-size: 13px; margin: 12px 0 0;">{{ $order->issued_at?->translatedFormat('l j F Y — H:i') }}</p>
            </div>

            <div style="background-color: #f9fafb; border-radius: 12px; padding: 18px; margin-bottom: 28px;">
                <h2 style="color: #111827; font-size: 16px; margin:0 0 14px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;">
                    بيانات العميل
                </h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px; width: 35%;">الاسم</td>
                        <td style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: bold;">{{ $customerName ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">الهاتف</td>
                        <td style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: bold; direction: ltr; text-align: right;">{{ $customerPhone ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">البريد الإلكتروني</td>
                        <td style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: bold; direction: ltr; text-align: right;">{{ $customerEmail ?: '—' }}</td>
                    </tr>
                </table>
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

            @php
                $paymentStatus = $order->payment_status;
                $paymentBoxBg = match ($paymentStatus) {
                    'paid' => '#ecfdf5',
                    'partial' => '#fffbeb',
                    default => '#fef2f2',
                };
                $paymentBoxBorder = match ($paymentStatus) {
                    'paid' => '#a7f3d0',
                    'partial' => '#fde68a',
                    default => '#fecaca',
                };
                $paymentStatusColor = match ($paymentStatus) {
                    'paid' => '#047857',
                    'partial' => '#b45309',
                    default => '#b91c1c',
                };
            @endphp

            <div style="background-color: {{ $paymentBoxBg }}; border: 1px solid {{ $paymentBoxBorder }}; border-radius: 12px; padding: 20px; margin-bottom: 28px;">
                <h2 style="color: #111827; font-size: 16px; margin: 0 0 14px; font-weight: bold;">
                    💳 تفاصيل الدفع
                </h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px; width: 40%;">طريقة الدفع</td>
                        <td style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: bold;">{{ $order->paymentMethodLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">حالة الدفع</td>
                        <td style="padding: 6px 0; color: {{ $paymentStatusColor }}; font-size: 14px; font-weight: bold;">{{ $order->paymentStatusLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">المبلغ المدفوع</td>
                        <td style="padding: 6px 0; color: #111827; font-size: 14px; font-weight: bold; direction: ltr; text-align: right;">{{ money_format($order->paid_total, currency: $order->currency_code) }}</td>
                    </tr>
                    @if ($order->due_total > 0)
                        <tr>
                            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">المبلغ المتبقي</td>
                            <td style="padding: 6px 0; color: #b45309; font-size: 14px; font-weight: bold; direction: ltr; text-align: right;">{{ money_format($order->due_total, currency: $order->currency_code) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">طريقة الشحن</td>
                        <td style="padding: 6px 0; color: #374151; font-size: 14px; font-weight: bold;">{{ $order->shippingMethodLabel() }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">قناة الطلب</td>
                        <td style="padding: 6px 0; color: #374151; font-size: 14px; font-weight: bold;">{{ $order->channelLabel() }}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 28px;">
                <a href="{{ $orderDetailUrl }}" style="display: inline-block; background-color: #059669; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px;">
                    عرض الطلب في لوحة التحكم
                </a>
            </div>

            <p style="color: #6b7280; font-size: 14px; line-height: 1.7; margin: 24px 0 0; text-align: center;">
                يُنصح بمراجعة الطلب وتحديث حالة التنفيذ في أقرب وقت.
            </p>
        </div>

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin-top: 20px; line-height: 1.6;">
            إشعار تلقائي من إقليم — {{ $tenant->name }}<br>
            © {{ date('Y') }} إقليم
        </p>
    </div>
</body>
</html>
