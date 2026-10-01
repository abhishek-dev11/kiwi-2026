@php

    if(!isset($ProductVariant)){
       $ProductVariant = getTableColumn('product_variants');
    }

    if(isset($request_data['count_product_variant'])){
        $count_product_variant = $request_data['count_product_variant'];
    }elseif(!isset($count_product_variant)){
        $count_product_variant = 0;
    }
@endphp

<div class="variation_row">
    <input type="hidden" name="product_variant_id[]" value="{{$ProductVariant['id']}}" />
    <input
        type="text"
        name="variant_name[]"
        class="form-control variant_name_cls"
        placeholder="Variant Name"
        id="variant_name_<?php echo $count_product_variant; ?>"
        value="{{$ProductVariant['value']}}"
    >
    <div class="variation_img_col">

        <img src="{{ asset('assets/images/add-gallery.svg') }}"  class="variant_preview">

        <!-- preview container -->
        <div class="preview_container">
            @if(!empty($ProductVariant['id']) && $ProductVariant->images->isNotEmpty())
                @foreach($ProductVariant->images as $img)
                    <div class="img_wrapper existing_img" data-id="{{ $img->id }}" style="display:inline-block; position:relative; margin:5px;">
                        
                        <img src="{{ preg_match('/^https?:\/\//i', $img->imageUrl) ? $img->imageUrl : get_image_upload('ProductVariant/'.$img->imageUrl) }}" style="width:70px; height:70px; border-radius:5px;">
                        
                        <span class="remove_existing_img" style="position:absolute; top:-5px; right:-5px; cursor:pointer; background:black; color:#fff; border-radius:50%; padding:2px 6px;"> ❌</span>

                        <input type="hidden" name="existing_variant_image_id[{{$count_product_variant}}][]" value="{{ $img->id }}">
                    </div>
                @endforeach
            @endif
        </div>
        
        <input type="file" name="variant_image[{{$count_product_variant}}][]" class="variant_image_input" multiple >
    </div>
    <button type="button" class="remove_variant btn btn-danger">Remove</button>
</div>