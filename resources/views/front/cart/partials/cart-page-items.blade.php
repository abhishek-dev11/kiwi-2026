@if(count($cart_arrs) > 0)
    @foreach($cart_arrs as $value)
        <tr class="cart-row">
            <td class="product-info align_center">
                <input type="hidden" name="cart_id[]" value="{{ $value['id'] }}">
                <a href="{{ route('product-detail', $value['slug']) }}">
                    <img src="{{ $value['image'] }}">
                </a>
                <div>
                    <a href="{{ route('product-detail', $value['slug']) }}">
                        <p class="title">{{ $value['title'] }}</p>
                    </a>
                    @if($value['product_type'] == 'variable')
                        <div class="cart_variant_group">
                            @if($value['attribute_title'] != '')
                                <p class="size">{{ $value['attribute_title'] }} : <span>{{ $value['product_variant_name'] }}</span></p>
                            @endif
                            @if($value['sub_attribute_title'] != '')
                                <p class="size">{{ $value['sub_attribute_title'] }} : <span>{{ $value['product_sub_variant_name'] }}</span></p>
                            @endif
                        </div>
                    @endif
                </div>
            </td>
            <td class="price align_center"><span>$</span>{{ number_format($value['finalPrice'], 2) }}</td>
            <td class="align_centerstepper">
                <div class="stepper">
                    <button class="minus">−</button>
                    <span>|</span>
                    <input type="number" name="cart_qty[]" class="qty" value="{{ $value['quantity'] }}" min="1" />
                    <span>|</span>
                    <button class="plus">+</button>
                </div>
            </td>
            <td class="subtotal align_center"><span>$</span>{{ number_format($value['item_sub_total'], 2) }}</td>
            <td>
                <div class="delete delete_icon" cart-id="{{ encrypt($value['id']) }}" style="cursor:pointer;">
                    <img src="{{ asset('assets/dist/images/delete.svg') }}">
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="5">
            <div class="not_found">Empty Cart</div>
        </td>
    </tr>
@endif