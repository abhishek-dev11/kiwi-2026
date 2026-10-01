@if( count($get_carts) > 0 )
    @foreach( $get_carts as $key => $value)
    <div class="cart_item">
        <?php
            $attribute_title = '';
            $sub_attribute_title = '';
            $product_variant_name = '';
            $product_sub_variant_name = '';
            $product = App\Models\Products::where('id', $value->product_id)->first();
            $originalPrice = $product->regular_price;
            $salePrice     = $product->discount_price;
            $finalPrice = calculateProductPrice( $originalPrice, $salePrice);

            if( $product->product_type == 'variable'  ){

                $variant_item = App\Models\ProducSubtVariantItems::where([ 'product_id' => $value->product_id]);
                if( $value->product_variant_id != '' ){
                    $variant_item->where([ 'product_variant_id' => $value->product_variant_id]);
                }
                if( $value->product_sub_variant_id != '' ){
                    $variant_item->where([ 'product_sub_variant_id' => $value->product_sub_variant_id]);
                }
                $variant_item = $variant_item->first();
                if( $variant_item ){
                    $finalPrice = calculateProductPrice($variant_item->price);
                }

                $get_attribute = App\Models\Attributes::where([ 'id' => $product->attribute_id ])->first();
                $attribute_title = $get_attribute->title ?? '';

                $get_variant_name = App\Models\ProductVariants::where(['id' => $value->product_variant_id ])->first();
                $product_variant_name = $get_variant_name->value ?? '';


                $get_sub_attribute = App\Models\Attributes::where([ 'id' => $product->sub_variant_attribute_id ])->first();
                $sub_attribute_title = $get_sub_attribute->title ?? '';

                $get_sub_variant_name = App\Models\ProductSubVariants::where([ 'id' => $value->product_sub_variant_id])->first();
                $product_sub_variant_name = $get_sub_variant_name->value ?? '';
            }
        ?>
        <div class="item_image">
            <a href="{{ route('product-detail', $product->slug) }}">
                <img src="{{ resolve_cart_item_image($value->product_id, $value->product_variant_id, $product->image) }}">
            </a>
        </div>
        <div class="cart_info">
            <div class="product_details">
                <div class="product_size">
                    <h4><a href="{{ route('product-detail', $product->slug) }}">{{ $product->title }}</a></h4>
                    @if( $product->product_type == 'variable' )
                        <div class="cart_variant_group">
                            @if( $attribute_title != '' )
                                <p>{{$attribute_title}} : <span>{{$product_variant_name}}</span></p>
                            @endif
                            @if( $sub_attribute_title != '' )
                                <p>{{$sub_attribute_title}} : <span>{{$product_sub_variant_name}}</span></p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="delete_icon confirm-delete--" cart-id="{{ encrypt($value->id) }}"  href="javascript:void(0);" style="cursor: pointer;">
                    <img src="{{ asset('assets/dist/images/delete.svg') }}">
                </div>
            </div>
            <div class="product_bottominfo">
                <div class="left_info">
                    @if( $product->product_type == 'simple' )
                        @if($salePrice != '')
                            <p> ${{ number_format($originalPrice, 2) }} </p>
                        @endif
                        @endif
                    <h6>
                        <span>$</span>
                        {{ number_format($finalPrice, 2) }}
                    </h6>
                </div>
                <div class="right_info">
                    <p>Quantity</p>
                    <h6>{{ $value->quantity }}</h6>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="not_found">
        Empty Cart
    </div>
@endif
