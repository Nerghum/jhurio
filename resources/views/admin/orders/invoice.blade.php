<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 14px;
        }
        .header {
            width: 100%;
            margin-bottom: 40px;
        }
        .header td {
            vertical-align: top;
        }
        .company-info h1 {
            margin: 0;
            font-size: 28px;
            color: #111;
        }
        .company-info p {
            margin: 5px 0 0 0;
            color: #555;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            margin: 0;
            font-size: 24px;
            color: #111;
        }
        .invoice-info p {
            margin: 5px 0 0 0;
            color: #555;
        }
        .address-container {
            width: 100%;
            margin-bottom: 40px;
        }
        .address-container td {
            vertical-align: top;
            width: 50%;
        }
        .address-block h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #111;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .address-block p {
            margin: 0;
            color: #444;
            line-height: 1.6;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        .items-table th.right, .items-table td.right {
            text-align: right;
        }
        .items-table th.center, .items-table td.center {
            text-align: center;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .product-name {
            font-weight: bold;
            margin: 0;
        }
        .variation-name {
            font-size: 12px;
            color: #6b7280;
            margin: 2px 0 0 0;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            text-align: right;
        }
        .totals-table .label {
            color: #6b7280;
        }
        .totals-table .amount {
            width: 120px;
            font-weight: bold;
        }
        .totals-table .total-row td {
            font-size: 18px;
            font-weight: bold;
            color: #111;
            border-top: 2px solid #e5e7eb;
            padding-top: 15px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td class="company-info">
                <h1>Jhurio Store</h1>
                <p>123 Commerce Avenue<br>Dhaka, Bangladesh<br>support@jhurio.com<br>+880 1234 567890</p>
            </td>
            <td class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Order #:</strong> {{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}<br>
                <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                <strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            </td>
        </tr>
    </table>

    <table class="address-container">
        <tr>
            <td class="address-block" style="padding-right: 20px;">
                <h3>Billed To</h3>
                <p>
                    <strong>{{ $order->name }}</strong><br>
                    Phone: {{ $order->phone }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->district }}
                </p>
            </td>
            <td class="address-block">
                <h3>Shipped To</h3>
                <p>
                    <strong>{{ $order->name }}</strong><br>
                    Phone: {{ $order->phone }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->district }}
                </p>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th class="center">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <p class="product-name">{{ $item->product_name }}</p>
                    @if($item->variation_name)
                    <p class="variation-name">Variation: {{ $item->variation_name }}</p>
                    @endif
                </td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="right">{{ number_format($item->price, 2) }} Tk</td>
                <td class="right">{{ number_format($item->price * $item->quantity, 2) }} Tk</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table width="100%">
        <tr>
            <td width="50%" style="vertical-align: top; padding-right: 30px;">
                @if($order->notes)
                <div style="background-color: #f9fafb; padding: 15px; border-radius: 5px; border: 1px solid #e5e7eb;">
                    <strong style="display: block; margin-bottom: 5px; color: #374151;">Order Notes:</strong>
                    <span style="color: #4b5563; font-size: 13px;">{{ $order->notes }}</span>
                </div>
                @endif
            </td>
            <td width="50%" style="vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="amount">{{ number_format($order->total_amount + $order->discount_amount, 2) }} Tk</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td class="label">Discount ({{ $order->coupon_code }}):</td>
                        <td class="amount" style="color: #059669;">-{{ number_format($order->discount_amount, 2) }} Tk</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Shipping:</td>
                        <td class="amount">0.00 Tk</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Total Paid:</td>
                        <td class="amount">{{ number_format($order->total_amount, 2) }} Tk</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        Thank you for shopping with Jhurio Store! If you have any questions about this invoice, please contact our support team.
    </div>

</body>
</html>
