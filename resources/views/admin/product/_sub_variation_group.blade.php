@php
    if(!isset($ProductSubVariant)){
       $ProductSubVariant = getTableColumn('product_sub_variants');
    }

    if(isset($request_data['count_sub_variant'])){
        $count_sub_variant = $request_data['count_sub_variant'];
    }elseif(!isset($count_sub_variant)){
        $count_sub_variant = 0;
    }
@endphp

<?php 
    // echo "<pre>";
    // print_r($ProductSubVariant);
    // echo "</pre>";
?>
<div class="sub_variation_row">
    <input type="hidden" name="product_sub_variant_id[<?php echo $count_sub_variant; ?>]" value="{{$ProductSubVariant['id']}}" />
    <input type="text" name="sub_variant_name[<?php echo $count_sub_variant; ?>]" value="{{$ProductSubVariant['value']}}" class="form-control sub_variant_name_cls" placeholder="Sub Variant Name" id="sub_variant_name_cls_<?php echo $count_sub_variant; ?>" />
    <button type="button" class="remove_sub_variant btn btn-danger">Remove</button>
</div>