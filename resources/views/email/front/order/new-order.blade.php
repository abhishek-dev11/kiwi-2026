<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f4f4">
    <tr>
        <td align="center" style="padding:40px 15px;">

            <table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="max-width:600px;">

                <!-- Header -->
                <tr bgcolor="#f16c21">
                    <td style="padding:20px;">
                        <h3 style="margin:0;font-size:25px;color:#ffffff;font-weight:600;">
                            New Order has been received
                        </h3>
                    </td>
                </tr>

                <!-- Title -->
                <tr>
                    <td style="padding:20px 20px 10px;">
                        <h1 style="margin:0;font-size:25px;line-height:42px;color:#222;font-weight:700;">
                            Thank you for your order.
                        </h1>
                    </td>
                </tr>

                <!-- Intro -->
                <tr>
                    <td style="padding:0 20px 20px;">
                        <p style="margin:0;font-size:16px;color:#414141;">
                            Hello {{ $name }},
                        </p>
                        <p style="margin:15px 0 0;font-size:16px;line-height:24px;color:#414141;">
                            Your order has been received and is currently being processed.
                            Delivery tracking information will be sent to you shortly.
                            Below are the details of your order:
                        </p>
                    </td>
                </tr>

                <!-- Order Summary Heading -->
                <tr>
                    <td style="padding:0 20px 10px;">
                        <h2 style="margin:0;font-size:20px;color:#222;">Order summary</h2>
                        <p style="margin:8px 0 20px;font-size:14px;color:#555;">
                            Order No. <strong>#{{ $order->id }}</strong>
                            ({{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }})
                        </p>
                    </td>
                </tr>

                <!-- Product Table Header -->
                <tr>
                    <td style="padding:0 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <th align="left"   style="padding-bottom:15px;font-size:16px;color:#222;border-bottom:1px solid #ddd;">Product</th>
                                <th align="center" style="padding-bottom:15px;font-size:16px;color:#222;border-bottom:1px solid #ddd;">Qty</th>
                                <th align="right"  style="padding-bottom:15px;font-size:16px;color:#222;border-bottom:1px solid #ddd;">Price</th>
                                <th align="right"  style="padding-bottom:15px;font-size:16px;color:#222;border-bottom:1px solid #ddd;">Total</th>
                            </tr>

                            <!-- Products Loop -->
                            @foreach($order_products as $item)
                            <tr>
                                <td style="padding:12px 0;">
                                    <table cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td width="55" style="padding-right:12px;vertical-align:top;">
                                                <img src="{{ $item->image }}"
                                                     width="50" height="50"
                                                     alt="{{ $item->title }}"
                                                     style="display:block;border:1px solid #eee;border-radius:4px;">
                                            </td>
                                            <td style="font-size:15px;color:#222;line-height:20px;vertical-align:top;">
                                                {{ $item->title }}
                                                @if($item->attribute_title && $item->product_variant_name)
                                                    <br>
                                                    <span style="font-size:13px;color:#555;">
                                                        <strong>{{ $item->attribute_title }}</strong>: {{ $item->product_variant_name }}
                                                    </span>
                                                @endif
                                                @if($item->sub_attribute_title && $item->product_sub_variant_name)
                                                    <br>
                                                    <span style="font-size:13px;color:#555;">
                                                        <strong>{{ $item->sub_attribute_title }}</strong>: {{ $item->product_sub_variant_name }}
                                                    </span>
                                                @endif
                                                @if($item->sku)
                                                    <!-- <br>
                                                    <span style="font-size:12px;color:#999;"> <strong>SKU</strong>: {{ $item->sku }}</span> -->
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td align="center" style="font-size:15px;color:#222;padding:12px 0;">
                                    {{ $item->quantity }}
                                </td>

                                <td align="right" style="font-size:15px;color:#222;padding:12px 0;">
                                    ${{ number_format($item->price, 2) }}
                                </td>

                                <td align="right" style="font-size:15px;color:#222;padding:12px 0;">
                                    ${{ number_format($item->price * $item->quantity, 2) }}
                                </td>
                            </tr>
                            @endforeach

                        </table>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:10px 20px;">
                        <hr style="border:none;border-top:1px solid #ddd;">
                    </td>
                </tr>

                <!-- Totals -->
                <tr>
                    <td style="padding:0 20px;">
                        <table width="100%" cellpadding="5" cellspacing="0" border="0">

                            <tr>
                                <td style="font-size:15px;color:#555;">Subtotal:</td>
                                <td align="right" style="font-size:15px;color:#222;">
                                    ${{ number_format($order_meta['sub_total'] ?? 0, 2) }}
                                </td>
                            </tr>

                            @if(!empty($order_meta['discount']) && $order_meta['discount'] > 0)
                            <tr>
                                <td style="font-size:15px;color:#555;">Discount:</td>
                                <td align="right" style="font-size:15px;color:#e74c3c;">
                                    -${{ number_format($order_meta['discount'], 2) }}
                                </td>
                            </tr>
                            @endif

                            @if(!empty($order_meta['gst']))
                            <tr>
                                <td style="font-size:15px;color:#555;">GST (15%):</td>
                                <td align="right" style="font-size:15px;color:#222;">
                                    ${{ number_format($order_meta['gst'], 2) }}
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td style="font-size:15px;color:#555;">Payment Method:</td>
                                <td align="right" style="font-size:15px;color:#222;">
                                    {{ $order->payment_method == 'stripe' ? 'Credit / Debit Card (Stripe)' : 'Cash on Delivery' }}
                                </td>
                            </tr>

                            @if($order->payment_method == 'stripe' && !empty($order_meta['currency']))
                            <tr>
                                <td style="font-size:15px;color:#555;">Currency:</td>
                                <td align="right" style="font-size:15px;color:#222;">
                                    {{ strtoupper($order_meta['currency']) }}
                                </td>
                            </tr>
                            @endif

                            <!-- Total -->
                            <tr style="border-top:2px solid #ddd;">
                                <td style="font-size:17px;color:#222;font-weight:bold;padding-top:10px;">Total:</td>
                                <td align="right" style="font-size:20px;color:#1c5478;font-weight:bold;padding-top:10px;">
                                    ${{ number_format($order_meta['total'] ?? 0, 2) }}
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                <!-- Divider -->
                <tr>
                    <td style="padding:15px 20px;">
                        <hr style="border:none;border-top:1px solid #ddd;">
                    </td>
                </tr>

                <!-- Billing + Shipping Addresses -->
                <tr>
                    <td style="padding:0 20px 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <!-- Billing Address -->
                                <td width="48%" valign="top">
                                    <h3 style="margin:0 0 10px;font-size:16px;color:#222;font-weight:bold;">
                                        Billing Address
                                    </h3>
                                    <p style="margin:0;font-size:15px;color:#444;line-height:24px;">
                                        @if($order->billing_company_name)
                                            {{ $order->billing_company_name }}<br>
                                        @endif
                                        {{ $order->billing_first_name }} {{ $order->billing_last_name }}<br>
                                        {{ $order->billing_street_address }}<br>
                                        {{ $billing_city->name ?? '' }},
                                        {{ $billing_state->name ?? '' }}<br>
                                        {{ $billing_country->name ?? '' }}<br>
                                        {{ $order->billing_phone }}<br>
                                        <a href="mailto:{{ $order->billing_email }}"
                                           style="color:#0066cc;text-decoration:none;">
                                            {{ $order->billing_email }}
                                        </a>
                                    </p>
                                </td>

                                <td width="4%"></td>

                                <!-- Shipping Address -->
                                <td width="48%" valign="top">
                                    <h3 style="margin:0 0 10px;font-size:16px;color:#222;font-weight:bold;">
                                        Delivery Address
                                    </h3>
                                    <p style="margin:0;font-size:15px;color:#444;line-height:24px;">
                                        @if($order->shipping_company_name)
                                            {{ $order->shipping_company_name }}<br>
                                        @endif
                                        {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                                        {{ $order->shipping_street_address }}<br>
                                        {{ $shipping_city->name ?? '' }},
                                        {{ $shipping_state->name ?? '' }}<br>
                                        {{ $shipping_country->name ?? '' }}<br>
                                        {{ $order->shipping_phone }}<br>
                                        <a href="mailto:{{ $order->shipping_email }}"
                                           style="color:#0066cc;text-decoration:none;">
                                            {{ $order->shipping_email }}
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Order Notes -->
                @if(!empty($order->order_note))
                <tr>
                    <td style="padding:0 20px 20px;">
                        <hr style="border:none;border-top:1px solid #ddd;margin-bottom:15px;">
                        <h3 style="margin:0 0 8px;font-size:16px;color:#222;">Order Notes</h3>
                        <p style="margin:0;font-size:15px;color:#555;line-height:22px;">
                            {{ $order->order_note }}
                        </p>
                    </td>
                </tr>
                @endif

                <!-- Divider -->
                <tr>
                    <td style="padding:5px 20px 15px;">
                        <hr style="border:none;border-top:1px solid #ddd;">
                    </td>
                </tr>

                <!-- Support -->
                <tr>
                    <td style="padding:0 20px 25px;">
                        <p style="margin:0;font-size:15px;color:#555;line-height:22px;">
                            Thank you again! Contact us at
                            <a href="mailto:{{ config('mail.from.address') }}"
                               style="color:#0066cc;text-decoration:none;">
                                {{ config('mail.from.address') }}
                            </a>
                            if you need any help with your order.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr bgcolor="#f9f9f9">
                    <td align="center" style="padding:15px 20px;">
                        <p style="margin:0;font-size:13px;color:#888;">
                            {{ config('app.name') }}
                        </p>
                        <p style="margin:4px 0 0;font-size:12px;color:#aaa;">
                            This is an automated email, please do not reply directly to this message.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>