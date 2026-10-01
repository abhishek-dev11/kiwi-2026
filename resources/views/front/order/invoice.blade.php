<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #222;
            background: #fff;
            padding: 40px;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header-table { width: 100%; margin-bottom: 30px; }
        .invoice-title { font-size: 34px; font-weight: 700; color: #222; }
        .logo { width: 120px; }

        /* ── Bill To ─────────────────────────────────────────── */
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #222;
            margin-bottom: 10px;
        }
        .bill-table { width: 100%; margin-bottom: 30px; }
        .bill-table td { vertical-align: top; font-size: 14px; line-height: 20px; color: #000; }
        .bill-table .left  { width: 60%; }
        .bill-table .right { width: 40%; }

        /* ── Products ────────────────────────────────────────── */
        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .product-table th {
            font-size: 14px;
            color: #222;
            padding: 8px 0 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .product-table th.center { text-align: center; }
        .product-table th.right  { text-align: right; }
        .product-table td { padding: 12px 0; vertical-align: top; border-bottom: 1px solid #f0f0f0; }
        .product-table td.center { text-align: center; }
        .product-table td.right  { text-align: right; }

        .product-img {
            width: 55px;
            height: 55px;
            border: 1px solid #eee;
        }
        .product-info { font-size: 14px; line-height: 20px; color: #222; }
        .product-info .variant { font-size: 12px; color: #555; margin-top: 4px; }
        .product-info .sku     { font-size: 11px; color: #999; margin-top: 3px; }

        /* ── Totals ──────────────────────────────────────────── */
        .totals-wrapper { width: 100%; margin-top: 20px; }
        .totals-inner   { width: 50%; float: right; }
        .totals-table   { width: 100%; border-collapse: collapse; }
        .totals-table td {
            font-size: 15px;
            color: #222;
            padding: 6px 0;
        }
        .totals-table td.right { text-align: right; }
        .totals-table .border-row td {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }
        .totals-table .amount-due td {
            font-size: 17px;
            font-weight: 700;
            padding-top: 12px;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .footer {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            text-align: center;
            font-size: 12px;
            color: #555;
        }
        .footer a { color: #0066cc; text-decoration: none; }

        /* ── Clearfix ────────────────────────────────────────── */
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <table class="header-table">
        <tr>
            <td align="left" valign="middle">
                <span class="invoice-title">Invoice</span>
            </td>
            <td align="right" valign="middle">
                <img src="{{ public_path('assets/dist/images/head-logo.png') }}"
                     class="logo" alt="Logo">
            </td>
        </tr>
    </table>

    <!-- ── Bill To ─────────────────────────────────────────────────────────── -->
    <p class="section-title">Bill To</p>
    <table class="bill-table">
        <tr>
            <!-- Left: customer address -->
            <td class="left">
                @if($order->billing_company_name)
                    {{ $order->billing_company_name }}<br>
                @endif
                {{ $order->billing_first_name }} {{ $order->billing_last_name }}<br>
                {{ $order->billing_street_address }}<br>
                {{ $billing_city->name ?? '' }}, {{ $billing_state->name ?? '' }}<br>
                {{ $billing_country->name ?? '' }}<br>
                {{ $order->billing_phone }}<br>
                {{ $order->billing_email }}
            </td>

            <!-- Right: invoice meta -->
            <td class="right">
                Invoice No. &nbsp;: {{ $invoice_no }}<br>
                Invoice Date : {{ \Carbon\Carbon::now()->format('d-m-Y') }}<br>
                Order ID &nbsp;&nbsp;&nbsp;: #{{ $order->id }}<br>
                Order Date &nbsp;: {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}<br>
                Payment &nbsp;&nbsp;&nbsp;&nbsp;:
                {{ $order->payment_method == 'stripe' ? 'Credit / Debit Card' : 'Cash on Delivery' }}
            </td>
        </tr>
    </table>

    <!-- ── Product Table ───────────────────────────────────────────────────── -->
    <table class="product-table">
        <thead>
            <tr>
                <th style="width:55%;">Product</th>
                <th class="center" style="width:15%;">Qty</th>
                <th class="right"  style="width:15%;">Price</th>
                <th class="right"  style="width:15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order_products as $item)
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td style="padding-right:10px;vertical-align:top;">
                                <img src="{{ $item->image }}" class="product-img" alt="{{ $item->title }}">
                            </td>
                            <td class="product-info" style="vertical-align:top;">
                                <div>{{ $item->title }}</div>
                                @if($item->attribute_title && $item->product_variant_name)
                                    <div class="variant">
                                        <strong>{{ $item->attribute_title }}</strong>: {{ $item->product_variant_name }}
                                    </div>
                                @endif
                                @if($item->sub_attribute_title && $item->product_sub_variant_name)
                                    <div class="variant">
                                        <strong>{{ $item->sub_attribute_title }}</strong>: {{ $item->product_sub_variant_name }}
                                    </div>
                                @endif
                                @if($item->sku)
                                    <!-- <div class="sku">SKU: {{ $item->sku }}</div> -->
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="right">${{ number_format($item->price, 2) }}</td>
                <td class="right">${{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ── Totals ──────────────────────────────────────────────────────────── -->
    <div class="totals-wrapper clearfix">
        <div class="totals-inner">
            <table class="totals-table">

                <tr class="border-row">
                    <td>Subtotal</td>
                    <td class="right">${{ number_format($order_meta['sub_total'] ?? 0, 2) }}</td>
                </tr>

                @if(!empty($order_meta['discount']) && $order_meta['discount'] > 0)
                <tr class="border-row">
                    <td>Discount</td>
                    <td class="right" style="color:#e74c3c;">
                        -${{ number_format($order_meta['discount'], 2) }}
                    </td>
                </tr>
                @endif

                @if(!empty($order_meta['gst']))
                <tr class="border-row">
                    <td>GST (15%)</td>
                    <td class="right">${{ number_format($order_meta['gst'], 2) }}</td>
                </tr>
                @endif

                <tr class="border-row">
                    <td>Total</td>
                    <td class="right">${{ number_format($order_meta['total'] ?? 0, 2) }}</td>
                </tr>

                <!-- <tr class="amount-due">
                    <td>Amount Due</td>
                    <td class="right">
                        ${{ number_format($order_meta['total'] ?? 0, 2) }}
                        {{ strtoupper($order_meta['currency'] ?? 'USD') }}
                    </td>
                </tr> -->

            </table>
        </div>
    </div>

    <!-- ── Footer ──────────────────────────────────────────────────────────── -->
    <div class="footer">
        <p>{{ config('app.name') }}</p>
        <a href="mailto:{{ config('mail.from.address') }}">
            {{ config('mail.from.address') }}
        </a>
    </div>

</body>
</html>