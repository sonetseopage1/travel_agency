<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>বুকিং রসিদ #{{ $booking->id }}</title>
    <style>
        /* The bundled font, declared explicitly so the Bengali text renders
           rather than falling back to a font with no Bengali glyphs. */
        @font-face {
            font-family: 'notosansbengali';
            font-style: normal;
            font-weight: 400;
            src: url('{{ resource_path('fonts/NotoSansBengali-Regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'notosansbengali';
            font-style: normal;
            font-weight: 700;
            src: url('{{ resource_path('fonts/NotoSansBengali-Bold.ttf') }}') format('truetype');
        }

        /* dompdf has no rem support and a limited flexbox, so the layout is
           plain tables and fixed spacing. */
        body {
            font-family: 'notosansbengali', sans-serif;
            font-size: 11px;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }

        .head { border-bottom: 3px solid #0f766e; padding-bottom: 10px; }
        .site { font-size: 18px; font-weight: 700; color: #0f766e; }
        .doc-title { font-size: 15px; font-weight: 700; }
        .muted { color: #64748b; }

        .stamp { border: 2px solid #0f766e; color: #0f766e; padding: 4px 10px; }
        .stamp.void { border-color: #b91c1c; color: #b91c1c; }

        .section { margin-top: 16px; }
        .section-title {
            font-size: 12px; font-weight: 700; color: #0f766e;
            border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 8px;
        }

        /* label/value pairs: the label column stays a fixed width so the
           values line up down the page. */
        .kv td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
        .kv td.k { width: 32%; color: #475569; }

        .num { text-align: right; }
        .totals td { padding: 4px 6px; }
        .totals td.k { text-align: left; color: #475569; }
        .grand td {
            border-top: 2px solid #0f766e; border-bottom: 2px solid #0f766e;
            font-size: 13px; font-weight: 700; padding-top: 7px; padding-bottom: 7px;
        }

        .note { border: 1px solid #cbd5e1; background: #f8fafc; padding: 8px; }
        .foot { margin-top: 18px; border-top: 1px solid #cbd5e1; padding-top: 8px; font-size: 9px; }

        /* Chrome drives the pagination, so the page geometry has to be
           declared here rather than passed to a renderer. */
        @page {
            size: A4;
            margin: 12mm 10mm;
        }

        /* Keep the coloured rules, stamps and shaded note box in the PDF;
           browsers otherwise drop background colours when printing. */
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* A section split across a page boundary is hard to read, so blocks
           stay whole and the totals stay on one page. */
        .section, .note, table, tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        h1, h2, h3, h4 {
            page-break-after: avoid;
            break-after: avoid;
        }
    </style>
</head>
<body>

<table class="head">
    <tr>
        <td style="width: 60%;">
            <div class="site">{{ \App\Models\Setting::string('site_name') }}</div>
            <div class="muted">
                {{ \App\Models\Setting::string('site_address') ?: 'ঢাকা, বাংলাদেশ' }}<br>
                ফোন: {{ \App\Models\Setting::string('site_phone') ?: '—' }}
                @if (\App\Models\Setting::string('site_email'))
                    · ইমেইল: {{ \App\Models\Setting::string('site_email') }}
                @endif
            </div>
        </td>
        <td class="num">
            <div class="doc-title">বুকিং রসিদ</div>
            <div class="muted">রেফারেন্স নম্বর: #{{ $booking->id }}</div>
            <div class="muted">তারিখ: {{ $booking->created_at?->format('d M Y, h:i A') }}</div>
            <div style="margin-top: 6px;">
                <span class="stamp {{ $booking->is_approved ? '' : 'void' }}">
                    {{ $booking->is_approved ? 'অনুমোদিত' : 'অনুমোদনের অপেক্ষায়' }}
                </span>
            </div>
        </td>
    </tr>
</table>

{{-- Who booked it --}}
<div class="section">
    <div class="section-title">গ্রাহকের তথ্য</div>
    <table class="kv">
        <tr>
            <td class="k">নাম</td>
            <td>{{ $booking->customer_name }}</td>
            <td class="k" style="width: 22%;">বুকিং সময়</td>
            <td>{{ $booking->created_at?->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="k">মোবাইল</td>
            <td>{{ $booking->customer_phone }}</td>
            <td class="k">স্ট্যাটাস</td>
            <td>{{ ucfirst($booking->status) }} / {{ ucfirst($booking->payment_status) }}</td>
        </tr>
        <tr>
            <td class="k">ইমেইল</td>
            <td>{{ $booking->customer_email }}</td>
            <td class="k">অনুমোদনের সময়</td>
            <td>{{ $booking->approved_at?->format('d M Y, h:i A') ?: '—' }}</td>
        </tr>
        @if ($booking->approver)
            <tr>
                <td class="k">অনুমোদনকারী</td>
                <td>{{ $booking->approver->name }}</td>
                <td class="k">যোগাযোগের ধরন</td>
                <td>{{ $booking->payment_method ?: '—' }}</td>
            </tr>
        @endif
    </table>
</div>

{{-- What they booked --}}
<div class="section">
    <div class="section-title">ট্যুরের তথ্য</div>
    <table class="kv">
        <tr>
            <td class="k">ট্যুর</td>
            <td>{{ $booking->tour?->title ?? '—' }}</td>
            <td class="k" style="width: 22%;">ভ্রমণের তারিখ</td>
            <td>{{ $booking->tour?->travel_date_label ?? '—' }}</td>
        </tr>
        <tr>
            <td class="k">গন্তব্য</td>
            <td>{{ $booking->tour?->destination ?: '—' }}</td>
            <td class="k">স্থায়িত্ব</td>
            <td>{{ $booking->tour?->duration_days ?? '—' }} দিন</td>
        </tr>
        <tr>
            <td class="k">যাত্রার ধরন</td>
            <td>
                {{ $booking->pricing_tier_label ?: ucfirst($booking->pricing_tier_type ?: '—') }}
                @if ($booking->pricing_tier_type)
                    <span class="muted">({{ $booking->pricing_tier_type }})</span>
                @endif
            </td>
            <td class="k">প্রাপ্তবয়স্কর হার</td>
            <td>৳{{ number_format((float) $booking->adult_rate, 2) }} / জন</td>
        </tr>
    </table>
</div>

{{-- The party --}}
<div class="section">
    <div class="section-title">যাত্রীর সংখ্যা</div>
    <table>
        <tr>
            <th class="k" style="text-align: left; border-bottom: 1px solid #cbd5e1; padding: 4px 6px;">ধরন</th>
            <th style="text-align: right; border-bottom: 1px solid #cbd5e1; padding: 4px 6px;">সংখ্যা</th>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">প্রাপ্তবয়স্ক</td>
            <td class="num" style="padding: 4px 6px;">{{ $booking->adult_count ?? 0 }}</td>
        </tr>
        @if (($booking->child_count ?? 0) > 0)
            <tr>
                <td style="padding: 4px 6px;">শিশু</td>
                <td class="num" style="padding: 4px 6px;">{{ $booking->child_count }}</td>
            </tr>
        @endif
        @if (($booking->infant_count ?? 0) > 0)
            <tr>
                <td style="padding: 4px 6px;">বিনামূল্যে শিশু</td>
                <td class="num" style="padding: 4px 6px;">{{ $booking->infant_count }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 4px 6px; font-weight: 700;">মোট যাত্রী</td>
            <td class="num" style="padding: 4px 6px; font-weight: 700;">{{ $booking->guest_count }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">কেবিন প্রয়োজন</td>
            <td class="num" style="padding: 4px 6px;">{{ $booking->cabin_count ?? 0 }}</td>
        </tr>
    </table>

    @if ($booking->guests->isNotEmpty())
        <div class="muted" style="margin-top: 6px;">শিশুদের বয়স:</div>
        <table>
            @foreach ($booking->guests as $index => $child)
                <tr>
                    <td style="padding: 2px 6px; width: 50%;">
                        শিশু {{ $index + 1 }} — বয়স {{ $child->age }}
                        {{ $child->type === 'infant' ? '(বিনামূল্যে)' : '' }}
                    </td>
                    <td class="num" style="padding: 2px 6px;">৳{{ number_format((float) $child->line_total, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>

{{-- Money, broken out so the total can be checked --}}
<div class="section">
    <div class="section-title">খরচের হিসাব</div>
    <table class="totals">
        <tr>
            <td class="k">যাত্রীর খরচ</td>
            <td class="num">৳{{ number_format((float) $booking->subtotal, 2) }}</td>
        </tr>
        @if ((float) $booking->tier_discount_amount > 0)
            <tr>
                <td class="k">প্যাকেজ ছাড়</td>
                <td class="num">−৳{{ number_format((float) $booking->tier_discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="k">অতিরিক্ত কেবিন খরচ</td>
            <td class="num">৳{{ number_format((float) $booking->extra_cabin_amount, 2) }}</td>
        </tr>
        @if ((float) $booking->discount_amount > 0)
            <tr>
                <td class="k">
                    প্রোমো ছাড়
                    @if ($booking->promo_code)
                        <span class="muted">({{ $booking->promo_code }})</span>
                    @endif
                </td>
                <td class="num">−৳{{ number_format((float) $booking->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="k">সর্বমোট</td>
            <td class="num">৳{{ number_format((float) $booking->total_price, 2) }}</td>
        </tr>
    </table>
</div>

{{-- Payment --}}
<div class="section">
    <div class="section-title">পেমেন্টের তথ্য</div>
    <table class="kv">
        <tr>
            <td class="k">পেমেন্ট স্ট্যাটাস</td>
            <td>{{ ucfirst($booking->payment_status) }}</td>
            <td class="k" style="width: 22%;">পেমেন্ট পদ্ধতি</td>
            <td>{{ $booking->payment_method ?: '—' }}</td>
        </tr>
        @if ($booking->transaction_id)
            <tr>
                <td class="k">ট্রানজেকশন আইডি</td>
                <td colspan="3">{{ $booking->transaction_id }}</td>
            </tr>
        @endif
        @if ($booking->special_notes)
            <tr>
                <td class="k">গ্রাহকের নোট</td>
                <td colspan="3">{{ $booking->special_notes }}</td>
            </tr>
        @endif
        @if ($booking->admin_note)
            <tr>
                <td class="k">প্রতিষ্ঠানের নোট</td>
                <td colspan="3">{{ $booking->admin_note }}</td>
            </tr>
        @endif
    </table>
</div>

@if ($booking->is_approved)
    <div class="section">
        <div class="note">
            এই রসিদটি কম্পিউটারে তৈরি এবং কোনো স্বাক্ষরের প্রয়োজন নেই। ভ্রমণের তারিখের আগে যেকোনো সময়
            সম্পর্কিত নম্বরে যোগাযোগ করে আপনার বুকিং নিশ্চিত করে নিতে পারেন।
        </div>
    </div>
@else
    <div class="section">
        <div class="note">
            এই আবেদনটি এখনো অনুমোদিত হয়নি। আমাদের প্রতিনিধি আপনার সাথে যোগাযোগ করে অনুমোদন জানাবেন।
        </div>
    </div>
@endif

<div class="foot muted">
    {{ \App\Models\Setting::string('site_name') }} · রেফারেন্স #{{ $booking->id }} ·
    এই ডকুমেন্টটি {{ now()->format('d M Y, h:i A') }} তারিখে তৈরি হয়েছে।
</div>

</body>
</html>
