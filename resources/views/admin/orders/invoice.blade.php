<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} — Jhurio</title>
    <style>
        /* ═══════════════════════════════════════════
           DomPDF-safe: table-only layout, no flex/grid,
           no external fonts/URLs, base64 logo embedded.
           Renders identically in browser and PDF.
        ═══════════════════════════════════════════ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.55;
        }

        .page {
            max-width: 860px;
            margin: 0 auto;
            padding: 44px 52px 40px;
            background: #fff;
        }

        /* ─── DIVIDER ─── */
        .divider { height: 3px; background: #7c3aed; margin: 0; border-radius: 2px; }
        .divider-thin { height: 1px; background: #e5e7eb; margin: 0; }
        .spacer-sm { height: 18px; }
        .spacer-md { height: 26px; }
        .spacer-lg { height: 36px; }

        /* ─── HEADER BAND ─── */
        .header-band {
            background: #ffffff;
            padding: 20px 0 18px;
            border-top: 4px solid #7c3aed;
        }

        .header-band table { width: 100%; border-collapse: collapse; }
        .header-band td { vertical-align: middle; }

        .brand-logo { height: 38px; width: auto; display: block; }

        .invoice-label-pill {
            display: inline-block;
            background: #ede9fe;
            color: #5b21b6;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 20px;
        }

        .invoice-number {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            letter-spacing: -0.5px;
            display: block;
            margin-top: 4px;
        }

        .invoice-number span { color: #7c3aed; }

        /* ─── META ROW ─── */
        .meta-band {
            background: #f9fafb;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .meta-band table { width: 100%; border-collapse: collapse; }
        .meta-band td {
            font-size: 10.5px;
            color: #6b7280;
            padding: 0;
            vertical-align: middle;
        }
        .meta-band .meta-val { color: #111827; font-weight: bold; }

        .status-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: bold;
        }
        .s-pending    { background: #fef3c7; color: #92400e; }
        .s-processing { background: #dbeafe; color: #1e40af; }
        .s-shipped    { background: #ede9fe; color: #5b21b6; }
        .s-delivered,
        .s-completed  { background: #d1fae5; color: #065f46; }
        .s-cancelled  { background: #fee2e2; color: #991b1b; }

        /* ─── SECTION HEADING ─── */
        .section-heading {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #9ca3af;
            border-left: 3px solid #7c3aed;
            padding-left: 8px;
            margin-bottom: 10px;
            display: block;
        }

        /* ─── ADDRESS CARDS ─── */
        .addr-table { width: 100%; border-collapse: collapse; }
        .addr-table td { width: 50%; vertical-align: top; padding-right: 14px; }
        .addr-table td:last-child { padding-right: 0; padding-left: 14px; }

        .addr-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            background: #f9fafb;
        }

        .addr-name {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            display: block;
            margin-bottom: 4px;
        }

        .addr-detail {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.65;
        }

        .addr-phone {
            font-size: 12px;
            font-weight: bold;
            color: #7c3aed;
            display: block;
            margin-top: 6px;
        }

        /* ─── ITEMS TABLE ─── */
        .items-table { width: 100%; border-collapse: collapse; }

        .items-table thead tr { background: #f3f0ff; }

        .items-table thead th {
            padding: 10px 13px;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #5b21b6;
            text-align: left;
            border-bottom: 2px solid #ddd6fe;
        }
        .items-table thead th.c { text-align: center; }
        .items-table thead th.r { text-align: right; }

        .items-table tbody tr { border-bottom: 1px solid #f3f4f6; }
        .items-table tbody tr.stripe { background: #f9fafb; }

        .items-table tbody td {
            padding: 11px 13px;
            font-size: 11.5px;
            vertical-align: middle;
        }

        .td-num { text-align: center; color: #9ca3af; font-size: 11px; width: 30px; }
        .td-c   { text-align: center; }
        .td-r   { text-align: right; }

        .item-name  { font-weight: bold; color: #111827; display: block; }
        .item-var   {
            display: inline-block;
            font-size: 9.5px;
            color: #6b7280;
            background: #e5e7eb;
            border-radius: 4px;
            padding: 1px 7px;
            margin-top: 3px;
        }

        .price-cell { color: #6b7280; font-weight: 600; text-align: right; width: 88px; }
        .total-cell { font-weight: bold; color: #111827; text-align: right; width: 100px; }

        /* ─── TOTALS + NOTES ─── */
        .bottom-outer { width: 100%; border-collapse: collapse; }
        .bottom-outer td { vertical-align: top; }
        .notes-td { width: 54%; padding-right: 18px; }
        .totals-td { width: 46%; }

        .notes-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 13px 15px;
            background: #f9fafb;
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
            min-height: 80px;
        }

        .totals-wrap { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }

        .totals-inner { width: 100%; border-collapse: collapse; }

        .totals-inner td {
            padding: 8px 15px;
            font-size: 11.5px;
            border-bottom: 1px solid #f3f4f6;
        }

        .t-lbl { color: #6b7280; font-weight: 500; }
        .t-val { text-align: right; font-weight: bold; color: #111827; }

        .disc-lbl { color: #059669; font-weight: 600; }
        .disc-val { text-align: right; font-weight: bold; color: #059669; }

        .grand-row { background: #7c3aed; }
        .grand-row td { padding: 13px 15px; border-bottom: none; }
        .grand-lbl { color: #ddd6fe; font-size: 11px; font-weight: 600; }
        .grand-val { text-align: right; color: #ffffff; font-size: 18px; font-weight: bold; }

        /* ─── FOOTER ─── */
        .footer-outer { width: 100%; border-collapse: collapse; }
        .footer-outer td { vertical-align: bottom; }

        .footer-left { width: 60%; }
        .footer-right { width: 40%; text-align: right; }

        .footer-thanks { font-size: 13px; font-weight: bold; color: #111827; margin-bottom: 5px; }
        .footer-contact { font-size: 10.5px; color: #9ca3af; line-height: 1.7; }

        .stamp-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #9ca3af; font-weight: bold; margin-bottom: 6px; text-align: right; }

        .stamp-box {
            border: 1.5px dashed #d1d5db;
            border-radius: 8px;
            height: 52px;
            width: 136px;
            text-align: center;
            padding-top: 16px;
            font-size: 10px;
            color: #d1d5db;
            display: inline-block;
        }

        .footer-note {
            text-align: center;
            font-size: 10px;
            color: #d1d5db;
            margin-top: 22px;
            border-top: 1px dashed #e5e7eb;
            padding-top: 14px;
        }

        /* ─── PRINT ONLY ─── */
        @media print {
            html, body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { padding: 30px 38px; }
        }
    </style>
    @if(isset($autoPrint) && $autoPrint)
    <script>window.onload = function() { window.print(); };</script>
    @endif
</head>
<body>
<div class="page">

    {{-- ══════ BRANDED HEADER BAND ══════ --}}
    @php
        $logoData = base64_encode(file_get_contents(public_path('assets/images/logo.png')));
        $logoSrc  = 'data:image/png;base64,' . $logoData;
    @endphp
    <div class="header-band">
        <table>
            <tr>
                <td style="width:55%;">
                    <img src="{{ $logoSrc }}" alt="Jhurio" class="brand-logo">
                </td>
                <td style="text-align:right;">
                    <span class="invoice-label-pill">Invoice</span>
                    <span class="invoice-number">
                        #ORD-<span>{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-band">
        <table>
            <tr>
                <td>
                    <span style="color:#6b7280; font-size:10px;">Dhaka, Bangladesh &nbsp;|&nbsp; +880 1924-301474 &nbsp;|&nbsp; support@jhurio.com</span>
                </td>
                <td style="text-align:right;">
                    <span style="margin-right:16px;">
                        <span class="meta-val">{{ $order->created_at->format('d M Y') }}</span>
                        <span style="color:#6b7280; font-size:10px;">&nbsp;at&nbsp;</span>
                        <span class="meta-val">{{ $order->created_at->format('h:i A') }}</span>
                    </span>
                    <span style="margin-right:16px;">
                        <span style="color:#6b7280; font-size:10px;">Payment: </span>
                        <span class="meta-val" style="font-size:10.5px;">Cash on Delivery</span>
                    </span>
                    <span>
                        <span class="status-pill s-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="spacer-md"></div>

    {{-- ══════ ADDRESSES ══════ --}}
    <span class="section-heading">Customer &amp; Shipping Details</span>
    <table class="addr-table">
        <tr>
            <td>
                <div class="addr-card">
                    <span class="section-heading" style="font-size:8px; margin-bottom:6px;">Billed To</span>
                    <span class="addr-name">{{ $order->name }}</span>
                    <span class="addr-detail">{{ $order->shipping_address }}{{ $order->district ? ', ' . $order->district : '' }}</span>
                    <span class="addr-phone">&#128222; {{ $order->phone }}</span>
                </div>
            </td>
            <td>
                <div class="addr-card">
                    <span class="section-heading" style="font-size:8px; margin-bottom:6px;">Shipped To</span>
                    <span class="addr-name">{{ $order->name }}</span>
                    <span class="addr-detail">
                        {{ $order->shipping_address }}{{ $order->district ? ', ' . $order->district : '' }}
                        @if($order->deliveryMethod)
                            <br>Via: {{ $order->deliveryMethod->name }}
                        @endif
                    </span>
                    <span class="addr-phone">&#128222; {{ $order->phone }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="spacer-md"></div>

    {{-- ══════ ITEMS TABLE ══════ --}}
    <span class="section-heading">Ordered Items</span>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30px; text-align:center;">#</th>
                <th>Item Description</th>
                <th class="c" style="width:55px;">Qty</th>
                <th class="r" style="width:92px;">Unit Price</th>
                <th class="r" style="width:104px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
            <tr class="{{ $index % 2 === 1 ? 'stripe' : '' }}">
                <td class="td-num">{{ $index + 1 }}</td>
                <td>
                    <span class="item-name">{{ $item->product_name }}</span>
                    @if($item->variation_name)
                        <span class="item-var">{{ $item->variation_name }}</span>
                    @endif
                </td>
                <td class="td-c" style="font-weight:bold; color:#111827; font-size:13px;">{{ $item->quantity }}</td>
                <td class="price-cell">&#2547;{{ number_format($item->price, 2) }}</td>
                <td class="total-cell">&#2547;{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="spacer-md"></div>

    {{-- ══════ NOTES + TOTALS ══════ --}}
    @php $itemsSubtotal = $order->items->sum(fn($i) => $i->price * $i->quantity); @endphp
    <table class="bottom-outer">
        <tr>
            <td class="notes-td">
                <span class="section-heading">Order Notes</span>
                <div class="notes-box">
                    @if($order->notes)
                        {{ $order->notes }}
                    @else
                        <span style="color:#d1d5db; font-style:italic;">No special instructions provided.</span>
                    @endif
                </div>
            </td>
            <td class="totals-td">
                <span class="section-heading">Payment Summary</span>
                <div class="totals-wrap">
                    <table class="totals-inner">
                        <tr>
                            <td class="t-lbl">Items Subtotal</td>
                            <td class="t-val">&#2547;{{ number_format($itemsSubtotal, 2) }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                        <tr>
                            <td class="disc-lbl">
                                Discount
                                @if($order->coupon_code)
                                    ({{ $order->coupon_code }})
                                @endif
                            </td>
                            <td class="disc-val">&#8722;&#2547;{{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="t-lbl">
                                Delivery
                                @if($order->deliveryMethod)
                                    ({{ $order->deliveryMethod->name }})
                                @endif
                            </td>
                            <td class="t-val">&#2547;{{ number_format($order->delivery_cost, 2) }}</td>
                        </tr>
                        <tr class="grand-row">
                            <td class="grand-lbl">Total Payable (COD)</td>
                            <td class="grand-val">&#2547;{{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ══════ FOOTER ══════ --}}
    <div class="spacer-lg"></div>
    <div class="divider-thin"></div>
    <div class="spacer-sm"></div>

    <table class="footer-outer">
        <tr>
            <td class="footer-left">
                <p class="footer-thanks">Thank you for shopping with Jhurio!</p>
                <p class="footer-contact">
                    Questions about your order? Contact us anytime:<br>
                    &#128222; +880 1924-301474 &nbsp;&nbsp; &#9993; support@jhurio.com &nbsp;&nbsp; &#127760; www.jhurio.com
                </p>
            </td>
            <td class="footer-right">
                <p class="stamp-label">Authorized Signature</p>
                <div class="stamp-box">&#8212; Jhurio Store &#8212;</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        This is a computer-generated invoice and does not require a physical signature.
        &nbsp;&middot;&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}
    </div>

</div>
</body>
</html>
