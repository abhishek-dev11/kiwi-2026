@extends('admin.layout.master')
@section('content')
<style>
    .variation_add_area,
    .sub_variant_add_area  {display: flex;flex-direction: column;gap: 10px;}
    .gray_box {background-color: #ECEEF1;padding: 20px;border-radius: 6px;}
    .plus_style {display: flex;gap: 20px; }
    .plus_style h4 {margin: 0;}
    .plus_style button {background-color: #F16C21;border: 2px solid #ddd;width: 30px;height: 30px;border-radius: 50%;color: #fff;font-size: 16px;font-weight: 500;}
    .variation_row,
    .sub_variation_row {display: flex;gap: 15px; align-items: center;background: #fff;padding: 10px;border-radius: 6px;}

    .variation_row input.form-control ,
    .sub_variation_row input.form-control {width: 40%;}

    .variation_row .variation_img_col {position: relative;display: flex;align-items: center;}
    .variation_row .variation_img_col img {width: 46px;}
    .variation_row .variation_img_col input[type="file"]{width: 50px;height: 50px;position: absolute;left: 0;top: 0;opacity: 0;}

    .white_bg {background: #fff;padding: 10px; border-radius: 6px;}
    #sub_variant_main {display: flex;gap: 20px;flex-direction: column;margin-bottom: 0px;}
    .remove_combination_th,
    .remove_combination_td{display: none;}

    .variation_add_area .variation_row:nth-child(2) .remove_variant {display: none;}
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow mb-4">
        <div class="card-header">
            <strong class="card-title f-18">{{ $common['title'] }}</strong>

            <a href="{{ route('admin.products') }}" class="btn btn-secondary float-end">
                <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
            </a>
        </div>
        <div class="card-body">
            <form method="POST" class="relative_cls" action="{{ route('admin.products.add') }}" enctype="multipart/form-data" id="addFrom">
                @csrf
                <input type="hidden" name="id" id="MainProductID" value="{{ $get_product['id'] }}">
                <div class="row">
                    <div class="col">
                        <div class="nav-align-top mb-3">

                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                  <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='GeneralInformation'){ ?> active <?php } }else{ ?> active <?php } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-GeneralInformation" role="tab" aria-selected="true">General Information</button>
                                </li>
                                <li class="nav-item">
                                  <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='Productdetails'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-Productdetails" role="tab" aria-selected="false">Product details</button>
                                </li>
                                <li class="nav-item">
                                  <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='gallery'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-gallery" role="tab" aria-selected="false">Gallery</button>
                                </li>

                                <li class="nav-item">
                                  <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='variants'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-variants" role="tab" aria-selected="false">Variants</button>
                                </li>

                            </ul>

                            <div class="tab-content">

                                <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='GeneralInformation'){ ?> active show <?php } }else{ ?> active show <?php } ?>" id="form-tabs-GeneralInformation" role="tabpanel">
                                    <div class="row">

                                        <!-- Title -->
                                        <div class="col-md-6">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Title') }} <span class="mandatory cls">*</span></label>
                                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title" id="title"
                                                    type="text" value="{{ old('title', $get_product['title']) }}"
                                                    placeholder="{{ translate('Enter Product') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('title') {{ $message }} @enderror
                                                    </div>
                                                </div>                                            
                                            </div>
                                        </div>     

                                        <!-- Quantity -->                 
                                        <div class="col-md-6">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Quantity') }}<span class="mandatory cls">*</span></label>
                                                <input class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}" name="quantity" id="quantity"
                                                    type="text" value="{{ old('quantity', $get_product['quantity']) }}"
                                                    placeholder="{{ translate('Enter Quantity') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('quantity') {{ $message }} @enderror</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Regular Price -->
                                        <div class="col-md-6">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Regular Price') }} ({{get_currency_symbol()}}) <span class="mandatory cls">*</span></label>
                                                <input class="form-control {{ $errors->has('regular_price') ? 'is-invalid' : '' }}" name="regular_price" id="regular_price"
                                                    type="text" value="{{ old('regular_price', $get_product['regular_price']) }}"
                                                    placeholder="{{ translate('Enter Regular Price') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('regular_price') {{ $message }} @enderror</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Discount Price -->
                                        <div class="col-md-6">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Discount Price') }} ({{get_currency_symbol()}})</label>
                                                <input class="form-control {{ $errors->has('discount_price') ? 'is-invalid' : '' }}" name="discount_price" id="discount_price"
                                                    type="text" value="{{ old('discount_price', $get_product['discount_price']) }}"
                                                    placeholder="{{ translate('Enter Discount Price') }}" >
                                                
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationdiscount_price" data-validator="notEmpty" class="error_msg">@error('discount_price') {{ $message }} @enderror </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Weight -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Weight') }} (kg)</label>
                                                <input class="form-control {{ $errors->has('weight') ? 'is-invalid' : '' }}" name="weight" id="weight"
                                                    type="text" value="{{ old('weight', $get_product['weight']) }}"
                                                    placeholder="{{ translate('Enter Weight') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div class="error_msg">@error('weight') {{ $message }} @enderror</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Length -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Length') }} (cm)</label>
                                                <input class="form-control {{ $errors->has('length') ? 'is-invalid' : '' }}" name="length" id="length"
                                                    type="text" value="{{ old('length', $get_product['length']) }}"
                                                    placeholder="{{ translate('Enter Length') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div class="error_msg">@error('length') {{ $message }} @enderror</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Width -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Width') }} (cm)</label>
                                                <input class="form-control {{ $errors->has('width') ? 'is-invalid' : '' }}" name="width" id="width"
                                                    type="text" value="{{ old('width', $get_product['width']) }}"
                                                    placeholder="{{ translate('Enter Width') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div class="error_msg">@error('width') {{ $message }} @enderror</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Height -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Height') }} (cm)</label>
                                                <input class="form-control {{ $errors->has('height') ? 'is-invalid' : '' }}" name="height" id="height"
                                                    type="text" value="{{ old('height', $get_product['height']) }}"
                                                    placeholder="{{ translate('Enter Height') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div class="error_msg">@error('height') {{ $message }} @enderror</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Top Level Category -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Top Level Category') }} <span class="mandatory cls">*</span></label>
                                                <select class="form-control select2 {{ $errors->has('top_level_category_id') ? 'is-invalid' : '' }}" name="top_level_category_id" id="top_level_category_id">
                                                    <option value="">Select Top Level Category</option>
                                                    <?php foreach ($top_level_categories as $key => $value) { ?>
                                                        <option value="<?php echo $value->id; ?>" {{ getSelected($value->id, old('top_level_category_id', $get_product['top_level_category_id'])) }} ><?php echo $value->title; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationcategory" data-validator="notEmpty" class="error_msg">@error('category') {{ $message }} @enderror </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mid Level Category -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Mid Level Category') }} <span class="mandatory cls">*</span></label>
                                                <select class="form-control select2 {{ $errors->has('mid_level_category_id') ? 'is-invalid' : '' }}" name="mid_level_category_id" id="mid_level_category_id">
                                                    <option value="">Select Mid Level Category</option>
                                                </select>
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationcategory" data-validator="notEmpty" class="error_msg">@error('category') {{ $message }} @enderror </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bottom Level Category -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Bottom Level Category') }}</label>
                                                <select class="form-control select2 {{ $errors->has('bottom_level_category_id') ? 'is-invalid' : '' }}" name="bottom_level_category_id" id="bottom_level_category_id">
                                                    <option value="">Select Bottom Level Category</option>
                                                </select>
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationcategory" data-validator="notEmpty" class="error_msg">@error('bottom_level_category_id') {{ $message }} @enderror </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fourth Level Category -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('Fourth Level Category') }}</label>
                                                <select class="form-control select2 {{ $errors->has('category') ? 'is-invalid' : '' }}" name="category" id="category">
                                                    <option value="">Select Fourth Level Category</option>
                                                </select>
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationcategory" data-validator="notEmpty" class="error_msg">@error('category') {{ $message }} @enderror </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- SKU -->
                                        <div class="col-md-4">
                                            <div class="form-group mb-3 ">
                                                <label class="col-form-label">{{ translate('SKU') }} <span class="mandatory cls">*</span></label>
                                                <input class="form-control {{ $errors->has('sku') ? 'is-invalid' : '' }}" name="sku" id="sku"
                                                    type="text" value="{{ old('sku', $get_product['sku']) }}"
                                                    placeholder="{{ translate('Enter SKU') }}" >
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('sku') {{ $message }} @enderror
                                                    </div>
                                                </div>                                            
                                            </div>
                                        </div>  

                                        <!-- Image -->
                                        <div class="col-md-4">
                                            <div class="form-group mb-3 {{ $errors->has('image') ? 'is-invalid' : '' }}">
                                                <label class="col-form-label">{{ translate('Image') }} <span class="mandatory cls">*</span> <span style="red;color: #e8214c;font-size: 10px;">Size should be 1200 × 1200 px.</span></label>
                                                <input type="file" name="image"
                                                    class="form-control {{ $errors->has('image') ? 'form-control-danger' : '' }}"
                                                    id="image" onchange="loadFile(event,'cat_image')">
                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                    <div data-field="formValidationshape" data-validator="notEmpty" class="error_msg">@error('image') {{ $message }} @enderror </div>
                                                </div>
                                                <div class="show-image">
                                                    <img class="user-img img-circle img-css" id="cat_image"
                                                        src="{{ $get_product['image'] != '' ? (preg_match('/^https?:\/\//i', $get_product['image']) ? $get_product['image'] : get_image_upload('Product/'.$get_product['image'])) : asset('placeholder/product/image.png') }}">
                                                </div>
                                            </div>
                                        </div>                                       
                                         
                                        <!-- Status -->
                                        <div class="col-md-4">
                                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                                <label class="col-form-label" for="status">{{ translate('Status') }} <span class="mandatory cls">*</span></label>
                                                <select name="status" id="status" class="form-select single-select"
                                                    data-allow-clear="true">
                                                    <option value="Active" {{ getSelected('Active', $get_product['status']) }}>
                                                        {{ translate('Active') }}</option>
                                                    <option value="Deactive" {{ getSelected('Deactive', $get_product['status']) }}>
                                                        {{ translate('Deactive') }}</option>
                                                </select>
                                            </div>
                                        </div>                                       
                                    </div>
                                </div>

                                <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='Productdetails'){ ?> active show <?php } } ?>" id="form-tabs-Productdetails" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="col-form-label" for="short_decripation">{{ translate('Short decripation') }}</label>
                                            <textarea name="short_decripation" id="short_decripation" cols="" rows="4" class="form-control">{{ old('short_decripation', $get_product['short_decripation']) }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <label class="col-form-label" for="Description">{{ translate('Description') }}</label>
                                            <textarea name="description" id="description" cols="" rows="4" class="form-control ckeditor">{{ old('description', $get_product['description']) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='gallery'){ ?> active show <?php } } ?>" id="form-tabs-gallery" role="tabpanel">
                                    <span style="red;color: #e8214c;font-size: 10px;">Size should be 1200 × 1200 px.</span>
                                    <div class="uploader_file wrapper">
                                        <div class="drop">
                                            <div class="cont">
                                                <label for="files" class="image-file">
                                                    <p class="fs-4 note needsclick my-2">Drag and drop your image here</p>
                                                    <small class="text-muted d-block fs-6 my-2">or</small>
                                                    <span class="note needsclick btn bg-label-primary d-inline" >Browse image</span>
                                                </label>
                                                <input  name="files[]" id="files"  multiple="true" type="file" />
                                            </div>
                                        </div>
                                        <output id="list"></output>
                                        <div class="row appenImage"></div>
                                    </div>
                                    <div class="row">
                                        @if (count($get_product_gallery) > 0)
                                            @foreach ($get_product_gallery as $key => $image)
                                                <div class="col-md-2">
                                                    <div class="image-item upload_img_list">
                                                        <img src="{{ $image->image != '' ? (preg_match('/^https?:\/\//i', $image->image) ? $image->image : get_image_upload('ProductGallery/'.$image->image)) : asset('placeholder/product/image.png') }}" alt="" width="20" >
                                                        <div class="image-item__btn-wrapper">
                                                            <button type="button" class="btn btn-default remove btn-sm">
                                                                <i class="fa fa-times" aria-hidden="true"></i>
                                                            </button>
                                                            <input type="hidden" name="image_id[]" value="{{ $image['id'] }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='variants'){ ?> active show <?php } } ?>" id="form-tabs-variants" role="tabpanel">
                                    <div class="variation_main">

                                        <!-- Product type -->
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-3 {{ $errors->has('product_type') ? 'is-invalid' : '' }}">
                                                    <label class="col-form-label" for="product_type">{{ translate('Product type') }} <span class="mandatory cls">*</span></label>
                                                    <select name="product_type" id="product_type" class="form-select single-select" data-allow-clear="true">
                                                        <!-- <option value="" >{{ translate('None') }}</option> -->
                                                        <option value="simple" 
                                                            {{ getSelected('simple', $get_product['product_type']) }}>{{ translate('Simple') }}</option>
                                                        <option value="variable" 
                                                            {{ getSelected('variable', $get_product['product_type']) }}>{{ translate('Variable') }}</option>
                                                    </select>
                                                </div>
                                            </div> 
                                        </div>  

                                        <div id="variation_type_product">
                                            <div class="gray_box mb-4">

                                                <div class="white_bg mb-3">
                                                    <div class="row">
                                                        <!-- Attribute -->
                                                        <div class="col-md-4">
                                                            <div class="form-group  ">
                                                                <label class="col-form-label">{{ translate('Attribute') }} 
                                                                    <span class="mandatory cls">*</span></label>
                                                                <select class="form-control select2 {{ $errors->has('attribute_select_id') ? 'is-invalid' : '' }} " name="attribute_select_id" id="attribute_select_id" >
                                                                    <option value="">Select Attribute</option>
                                                                    <?php foreach ($get_attributes as $key => $value) { ?>
                                                                        <option 
                                                                            value="<?php echo $value->id; ?>" 
                                                                            {{ getSelected($value->id, old('attribute_select_id', $get_product['attribute_id'])) }}
                                                                        >
                                                                            <?php echo $value->title; ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                                    <div data-field="formValidationattribute_select_id" data-validator="notEmpty" class="error_msg">@error('attribute_select_id') {{ $message }} @enderror </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php 
                                                    $CheckedImage = '';
                                                    if ( count($get_product_variant_images) ) {
                                                        $CheckedImage = 'checked';
                                                    }
                                                ?>
                                                <div class="variation_add_area">
                                                    <div>
                                                        <div class="add_more_variant plus_style">
                                                            <h4> Add Variants to the family </h4>
                                                            <button type="button">+</button>
                                                        </div>

                                                        <div class="image_variant_div"> 
                                                            <label for="image_variant_id">
                                                                <input type="checkbox" {{$CheckedImage}} name="image_variant_id" id="image_variant_id" > Image Variant
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php $count_product_variant = 0; ?>
                                                    <?php if (count($get_product_variant) ) { ?>
                                                        @foreach($get_product_variant as $ProductVariant)
                                                            @include('admin.product._variation_group')
                                                            <?php $count_product_variant++; ?>
                                                        @endforeach                        
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="gray_box" id="sub_variant_main">
                                                <?php 
                                                    $SubVariantChecked = '';
                                                    $IsSubVariantData = false;
                                                    if( $get_product['sub_variant_attribute_id'] != '' ){
                                                        $SubVariantChecked = 'checked';
                                                        $IsSubVariantData = true;
                                                    }
                                                ?>
                                                <div class="sub_variant_div"> 
                                                    <label for="sub_variant_checkbox_id">
                                                        <input type="checkbox"
                                                            name="sub_variant_checkbox_id" 
                                                            id="sub_variant_checkbox_id"
                                                            {{$SubVariantChecked}}
                                                        > 
                                                        Sub Variant Option
                                                    </label>
                                                </div>

                                                <div id="sub_variant_option">
                                                    <div class="white_bg mb-3">
                                                        <div class="row">
                                                            <!-- Attribute -->
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label class="col-form-label">{{ translate('Variant Family Name') }} 
                                                                        <span class="mandatory cls">*</span></label>
                                                                    <select class="form-control select2  " name="vatiant_attribute_select_id" id="vatiant_attribute_select_id" >
                                                                        <option value="">Select Attribute</option>
                                                                        <?php foreach ($get_attributes as $key => $value) { ?>
                                                                            <option value="<?php echo $value->id; ?>" 
                                                                                {{ getSelected($value->id, old('vatiant_attribute_id', $get_product['sub_variant_attribute_id'])) }}    
                                                                            >
                                                                                <?php echo $value->title; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                                        <div data-field="formValidationvatiant_attribute_id" data-validator="notEmpty" class="error_msg">@error('vatiant_attribute_id') {{ $message }} @enderror </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                    <div class="sub_variant_add_area">
                                                        <div>
                                                            <div class="add_more_sub_variant plus_style">
                                                                <h4> Add Sub Variants to the family </h4>
                                                                <button type="button">+</button>
                                                            </div>
                                                        </div>

                                                        <?php $count_sub_variant = 0; ?>
                                                        <?php if (count($get_product_sub_variant) ) { ?>
                                                            @foreach($get_product_sub_variant as $ProductSubVariant)
                                                                @include('admin.product._sub_variation_group')
                                                                <?php $count_sub_variant++; ?>
                                                            @endforeach                        
                                                        <?php } ?>
                                                    </div>
                                                </div>

                                                <?php 
                                                    /*echo "<pre>";
                                                    print_r($get_sub_variant_items);
                                                    echo "</pre>";*/
                                                ?>
                                                <!-- HTML for Variant Table -->
                                                <table class="table table-responsive variant_combination_table">
                                                    <thead>
                                                        <tr>
                                                            <th id="parent_variant_column">Parent Variant Family</th>
                                                            <th id="dynamic_column"></th>
                                                            <th>Price</th>
                                                            <th>Stock</th>
                                                            <th>Weight (kg)</th>
                                                            <th>Length (cm)</th>
                                                            <th>Width (cm)</th>
                                                            <th>Height (cm)</th>
                                                            <th class="remove_combination_th">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php

                                                            if ( count($get_product_sub_variant) ) { 
                                                                $TableHtml = '';
                                                                $outer_index = 0;
                                                                //$get_product['id']
                                                                $grouped = $get_sub_variant_items->groupBy('product_variant_id');
                                                                foreach ($grouped as $variant_id => $items) {

                                                                    // Get variant name (ONLY ONCE per group)
                                                                    $variant_name = optional(
                                                                        App\Models\ProductVariants::find($variant_id)
                                                                    )->value;

                                                                    $rowspan = $items->count();
                                                                    $index = 0;

                                                                    foreach ($items as $item) {

                                                                        // Get sub variant name
                                                                        $sub_variant_name = optional(
                                                                            App\Models\ProductSubVariants::find($item->product_sub_variant_id)
                                                                        )->value;

                                                                        if ($index == 0) {
                                                                            $TableHtml .= '<tr>
                                                                                <td rowspan="'.$rowspan.'">'.$variant_name.'</td>
                                                                                <td>'.$sub_variant_name.'</td>
                                                                                <td>
                                                                                    <input type="text" name="price['.$outer_index.']['.$index.']" class="form-control price_cls" value="'.$item->price.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="stock['.$outer_index.']['.$index.']" class="form-control stock_cls" value="'.$item->stock.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_weight['.$outer_index.']['.$index.']" class="form-control weight_cls" value="'.$item->weight.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_length['.$outer_index.']['.$index.']" class="form-control length_cls" value="'.$item->length.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_width['.$outer_index.']['.$index.']" class="form-control width_cls" value="'.$item->width.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_height['.$outer_index.']['.$index.']" class="form-control height_cls" value="'.$item->height.'">
                                                                                </td>
                                                                            </tr>';
                                                                        } else {
                                                                            $TableHtml .= '<tr>
                                                                                <td>'.$sub_variant_name.'</td>
                                                                                <td>
                                                                                    <input type="text" name="price['.$outer_index.']['.$index.']" class="form-control price_cls" value="'.$item->price.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="stock['.$outer_index.']['.$index.']" class="form-control stock_cls" value="'.$item->stock.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_weight['.$outer_index.']['.$index.']" class="form-control weight_cls" value="'.$item->weight.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_length['.$outer_index.']['.$index.']" class="form-control length_cls" value="'.$item->length.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_width['.$outer_index.']['.$index.']" class="form-control width_cls" value="'.$item->width.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_height['.$outer_index.']['.$index.']" class="form-control height_cls" value="'.$item->height.'">
                                                                                </td>
                                                                            </tr>';
                                                                        }

                                                                        $index++;
                                                                    }

                                                                    $outer_index++;
                                                                }
                                                                echo $TableHtml;
                                                            }elseif ( count($get_sub_variant_items) ) { 
                                                                $TableHtml = '';
                                                                $outer_index = 0;
                                                                 
                                                                foreach ($get_sub_variant_items as $variant_id => $item) {

                                                                    // Get sub variant name
                                                                    $variant_name = optional(
                                                                        App\Models\ProductVariants::find($item->product_variant_id)
                                                                    )->value;

                                                                    $TableHtml .= '<tr>
                                                                                <td >'.$variant_name.'</td>
                                                                                <td> </td>
                                                                                <td>
                                                                                    <input type="text" name="price['.$outer_index.']" class="form-control price_cls" value="'.$item->price.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="stock['.$outer_index.']" class="form-control stock_cls" value="'.$item->stock.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_weight['.$outer_index.']" class="form-control weight_cls" value="'.$item->weight.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_length['.$outer_index.']" class="form-control length_cls" value="'.$item->length.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_width['.$outer_index.']" class="form-control width_cls" value="'.$item->width.'">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" name="item_height['.$outer_index.']" class="form-control height_cls" value="'.$item->height.'">
                                                                                </td>
                                                                            </tr>';

                                                                    $outer_index++;
                                                                }
                                                                echo $TableHtml;
                                                            }
                                                        ?>
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                 
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10"></div>
                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-primary">
                            <span class="tf-icons bx bx-save me-1"></span>Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection


@section('script')


<script>
    $(document).ready(function() {
        if (window.File && window.FileList && window.FileReader) {
            $("#files").on("change", function(e) {
                var files = e.target.files,
                    filesLength = files.length;
                for (var i = 0; i < filesLength; i++) {
                    var f = files[i]
                    var fileReader = new FileReader();
                    fileReader.onload = (function(e) {
                        var file = e.target;
                        var html =
                            "<div class='col-md-2'><div class='image-item upload_img_list'>" +
                            "<img src=' " +
                            e.target.result + "' alt='' width='20' title='" + file.name +
                            "'>" + "<div class='image-item__btn-wrapper'>" +
                            "<button type='button' class='btn btn-default remove btn-sm'>" +
                            "<i class='fa fa-times' aria-hidden='true'></i>" +
                            "</button>" +
                            "</div>" +
                            "</div>" +
                            "</div>";

                        // $(html).insertAfter(".appenImage");
                        $(".appenImage").append(html);
                    });
                    fileReader.readAsDataURL(f);
                }
            });
        } else {
            alert("Your browser doesn't support to File API")
        }
        $(document).on("click", ".remove", function() {
            $(this).closest(".col-md-2").remove();
        });  
    });
</script>
 
<script type="text/javascript">
    $(document).on("submit", "#addFrom", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('.error_msg').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('admin.products.add') }}",
            datatype: 'JSON',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(result){
               jQuery('#common_loader').show();
            },
            success: function(response) {
                jQuery('#common_loader').hide();
                if (response.error) {
                    $(".tab-pane").removeClass('active show');
                    $('.nav-link').removeClass('active');
                    danger_msg("Something is wrong with required field!");
                    activeTab(response.error);
                } else {
                    success_msg(response.success);
                    /*setTimeout(() => {
                        //location.reload();
                        window.location.href = "{{ route('admin.products') }}";
                    }, 1000);*/
                }
            }
        });
        return false;
    });
</script>

<script type="text/javascript">
    //top_level_category_id
    $(document).ready(function () { 
        $(document).on("change", '#top_level_category_id', function() {
            select_cat('mid_level_category_id',$(this).val());
            $('#bottom_level_category_id').html('<option value="">Select Bottom Level Category</option>');
            $('#category').html('<option value="">Select Fourth Level Category</option>');
        });
    });
    <?php if(old('top_level_category_id', null) != null){ ?>
        <?php if(old('mid_level_category_id', null) != null){ ?>
            select_cat('mid_level_category_id','<?php echo old('top_level_category_id', null); ?>','<?php echo old('mid_level_category_id', null); ?>');
        <?php }else{ ?>
            select_cat('mid_level_category_id','<?php echo old('top_level_category_id', null); ?>');
        <?php } ?>
    <?php }elseif($get_product['top_level_category_id']){ ?>
        <?php if($get_product['mid_level_category_id']){ ?>
            select_cat('mid_level_category_id','<?php echo $get_product['top_level_category_id']; ?>','<?php echo $get_product['mid_level_category_id']; ?>');
        <?php }else{ ?>
            select_cat('mid_level_category_id','<?php echo $get_product['top_level_category_id']; ?>');
        <?php } ?>
    <?php } ?>

    //mid_level_category_id
    $(document).ready(function () { 
        $(document).on("change", '#mid_level_category_id', function() {
            select_cat('bottom_level_category_id',$(this).val());
            $('#category').html('<option value="">Select Fourth Level Category</option>');
        });
    });
    <?php if(old('mid_level_category_id', null) != null){ ?>
        <?php if(old('bottom_level_category_id', null) != null){ ?>
            select_cat('bottom_level_category_id','<?php echo old('mid_level_category_id', null); ?>','<?php echo old('bottom_level_category_id', null); ?>');
        <?php }else{ ?>
            select_cat('bottom_level_category_id','<?php echo old('mid_level_category_id', null); ?>');
        <?php } ?>
    <?php }elseif($get_product['mid_level_category_id']){ ?>
        <?php if($get_product['bottom_level_category_id']){ ?>
            select_cat('bottom_level_category_id','<?php echo $get_product['mid_level_category_id']; ?>','<?php echo $get_product['bottom_level_category_id']; ?>');
        <?php }else{ ?>
            select_cat('bottom_level_category_id','<?php echo $get_product['mid_level_category_id']; ?>');
        <?php } ?>
    <?php } ?>

    //bottom_level_category_id
    $(document).ready(function () {
        $(document).on("change", '#bottom_level_category_id', function() {
            select_cat('category',$(this).val());
        });
    });
    <?php if(old('bottom_level_category_id', null) != null){ ?>
        <?php if(old('category', null) != null){ ?>
            select_cat('category','<?php echo old('bottom_level_category_id', null); ?>','<?php echo old('category', null); ?>');
        <?php }else{ ?>
            select_cat('category','<?php echo old('bottom_level_category_id', null); ?>');
        <?php } ?>
    <?php }elseif($get_product['bottom_level_category_id']){ ?>
        <?php if($get_product['category']){ ?>
            select_cat('category','<?php echo $get_product['bottom_level_category_id']; ?>','<?php echo $get_product['category']; ?>');
        <?php }else{ ?>
            select_cat('category','<?php echo $get_product['bottom_level_category_id']; ?>');
        <?php } ?>
    <?php } ?>


    function select_cat(idddd,category,child='0'){
        var cat_title = "Bottom Level Category";
        if (idddd=='mid_level_category_id') {
            cat_title = "Mid Level Category";
        } else if (idddd=='category') {
            cat_title = "Fourth Level Category";
        }
        $.ajax({
            url: "{{ route('admin.products.get_child_category') }}",
            type: 'post',
            data: {
                'title': cat_title,
                'category': category,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#'+idddd).html(response);
            }
        });
    }
</script>

<!--  -->
<script>
    $(document).ready(function () {

        /* 1. Variant Jquery Start */

        //$('#variation_type_product').hide();
        setTimeout(function () {
            $('#product_type').trigger('change');             
        }, 1000);
        $('#product_type').on('change', function () {
            let value = $(this).val();
            if (value && value == 'variable') {
                $('#variation_type_product').slideDown(); // Show Add Variant area
            } else {
                $('#variation_type_product').slideUp(); // Hide Add Variant area
            }
        });

        // Initially hide
        //$('.variation_add_area').hide();
        setTimeout(function () {
            $('#attribute_select_id').trigger('change');             
        }, 1000);

        $('#attribute_select_id').on('change', function () {
            let value = $(this).val();
            if (value && value !== '') {
                $('.variation_add_area').slideDown(); // Show Add Variant area
            } else {
                $('.variation_add_area').slideUp(); // Hide Add Variant area
            }
            toggleSubVariantCheckbox(); // Toggle Sub Variant checkbox disabled state
        });

        /* Add more Image Option */
        function toggleImageVariant() {
            let isChecked = $('#image_variant_id').is(':checked');
            if (isChecked) {
                $('.variation_img_col').show();
            } else {
                $('.variation_img_col').hide();
            }
        }

        // Run on load
        toggleImageVariant();

        // Checkbox change
        $(document).on('change', '#image_variant_id', function () {
            toggleImageVariant();
        });

        // On attribute select change, toggle the disabled state of the sub variant checkbox
        function toggleSubVariantCheckbox() {
            if ($('#attribute_select_id').val()) {  // If an attribute is selected
                $('#sub_variant_checkbox_id').prop('disabled', false);  // Enable Sub Variant Option checkbox
            } else {
                $('#sub_variant_checkbox_id').prop('disabled', true);  // Disable Sub Variant Option checkbox
            }
        }

        // Run on page load to check if an attribute is selected
        toggleSubVariantCheckbox();

        /*$(document).on('change', '.variant_image_input', function (e) {
            let input = this;
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    // find the image inside same row
                    $(input).closest('.variation_row')
                            .find('.variant_preview')
                            .attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        });*/

        $(document).on('change', '.variant_image_input', function () {
            let previewContainer = $(this).siblings('.preview_container');
            //previewContainer.html('');

            let files = Array.from(this.files);
            let dt = new DataTransfer(); // to manage files

            files.forEach((file, index) => {
                let reader = new FileReader();

                reader.onload = function (e) {
                    let html = `
                        <div class="img_wrapper" data-index="${index}" style="display:inline-block; position:relative; margin:5px;">
                            
                            <img src="${e.target.result}" 
                                 style="width:70px; height:70px; border-radius:5px;">
                            
                            <span class="remove_img" 
                                  style="position:absolute; top:-5px; right:-5px; cursor:pointer; background:red; color:#fff; border-radius:50%; padding:2px 6px;">
                                ❌
                            </span>
                        </div>
                    `;
                    previewContainer.append(html);
                };

                dt.items.add(file);
                reader.readAsDataURL(file);
            });

            this.files = dt.files;
        });

        $(document).on('click', '.remove_img', function () {

            let wrapper = $(this).closest('.img_wrapper');
            let index = wrapper.data('index');

            let input = wrapper.closest('.variation_img_col').find('.variant_image_input')[0];
            let files = Array.from(input.files);

            // remove selected file
            files.splice(index, 1);

            // rebuild FileList
            let dt = new DataTransfer();
            files.forEach(file => dt.items.add(file));

            input.files = dt.files;

            // remove preview
            wrapper.remove();

        });
        /* ======== 1. Variant Jquery End ========= */

        /* ======== 2. Sub Variant Jquery Start ======== */  
        function toggleSubVariantOption() {
            if ($('#sub_variant_checkbox_id').is(':checked')) {
                $('#sub_variant_option').show();
            } else {
                $('#sub_variant_option').hide();
            }
        }

        // Run on page load (if checked, show; else hide)
        toggleSubVariantOption();

        // Toggle visibility on checkbox change
        $('#sub_variant_checkbox_id').on('change', function () {
            toggleSubVariantOption();
            updateCombinationTable()
        });

        function toggleSubVariantAddArea() {
            if ($('#vatiant_attribute_select_id').val()) {  // Check if a value is selected
                $('.sub_variant_add_area').show();
            } else {
                $('.sub_variant_add_area').hide();
            }
        }

        // Run on page load (if selected, show; otherwise hide)
        toggleSubVariantAddArea();

        // On select change, toggle visibility of .sub_variant_add_area
        $('#vatiant_attribute_select_id').on('change', function () {
            toggleSubVariantAddArea();
        });
        
        /* ======== 2. Sub Variant Jquery End ======== */ 
  

        /* ======== 3. jQuery Script to Manage Variants Table Start ======== */ 

        // Initially set the first column as "Parent Variant Family"
        $("#parent_variant_column").text("Parent Variant Family");
        $("#dynamic_column").text(""); // Empty the dynamic column text initially
        //$('#dynamic_column').hide();  // Hide the dynamic column initially



        // Reads whatever price/stock values are currently in the table, keyed by
        // "<variant name>||<sub variant name>", so a rebuild (add/remove row, rename, etc.)
        // can restore them instead of wiping every combination back to blank.
        //
        // Locates cells by the .price_cls/.stock_cls inputs themselves rather than by raw
        // <td> position/count - the JS-rebuilt table always emits a trailing "remove" <td>
        // (CSS-hidden, not removed), while the server-rendered table doesn't, so counting
        // <td>s puts continuation rows (2nd/3rd sub-variant in a rowspan group) off by one
        // and silently drops their price/stock.
        function captureCombinationValues() {
            let values = {};
            let currentVariant = null;
            let isSubVariantEnabled = $('#sub_variant_checkbox_id').is(':checked');

            $('.variant_combination_table tbody tr').each(function () {
                let $row = $(this);
                let $priceInput = $row.find('.price_cls');
                let $stockInput = $row.find('.stock_cls');
                if (!$priceInput.length || !$stockInput.length) return;

                let $rowspanCell = $row.find('td[rowspan]');
                let subVariant;

                if ($rowspanCell.length) {
                    // First row of a sub-variant group - the very next cell is the sub-variant name.
                    currentVariant = $rowspanCell.text().trim();
                    subVariant = $rowspanCell.next('td').text().trim();
                } else if (isSubVariantEnabled) {
                    // Continuation row - only the sub-variant name repeats, variant carries over.
                    subVariant = $row.find('td').first().text().trim();
                } else {
                    // Simple mode: no sub-variants, the first cell is the variant name itself.
                    currentVariant = $row.find('td').first().text().trim();
                    subVariant = '';
                }

                let key = currentVariant + '||' + subVariant;
                values[key] = {
                    price: $priceInput.val() || '',
                    stock: $stockInput.val() || '',
                    weight: $row.find('.weight_cls').val() || '',
                    length: $row.find('.length_cls').val() || '',
                    width: $row.find('.width_cls').val() || '',
                    height: $row.find('.height_cls').val() || '',
                };
            });
            return values;
        }

        // Function to update the variant combination table
        function updateCombinationTable() {
            let existingValues = captureCombinationValues();

            let variantCount = 0;  // Counter to track variants
            let variants = $("input.variant_name_cls").map(function() {
                return $(this).val();
            }).get();

            let subVariants = $("input.sub_variant_name_cls").map(function() {
                return $(this).val();
            }).get();

            let isSubVariantEnabled = $('#sub_variant_checkbox_id').is(':checked');
            let IsSubVariantData = '<?php echo $IsSubVariantData; ?>';
            //console.log('IsSubVariantData', IsSubVariantData);

            if (!isSubVariantEnabled) {
                let tableHtml = "";
                if (variants.length > 0) {
                    variants.forEach(function(variant, outer_index) {
                        let existing = existingValues[variant + '||'] || {};
                        tableHtml += `
                            <tr>
                                <td>${variant}</td>
                                <td></td>
                                <td><input type="text" name="price[${outer_index}]" class="form-control price_cls" placeholder="Price" value="${existing.price || ''}"></td>
                                <td><input type="text" name="stock[${outer_index}]" class="form-control stock_cls" placeholder="Stock" value="${existing.stock || ''}"></td>
                                <td><input type="text" name="item_weight[${outer_index}]" class="form-control weight_cls" placeholder="Weight" value="${existing.weight || ''}"></td>
                                <td><input type="text" name="item_length[${outer_index}]" class="form-control length_cls" placeholder="Length" value="${existing.length || ''}"></td>
                                <td><input type="text" name="item_width[${outer_index}]" class="form-control width_cls" placeholder="Width" value="${existing.width || ''}"></td>
                                <td><input type="text" name="item_height[${outer_index}]" class="form-control height_cls" placeholder="Height" value="${existing.height || ''}"></td>
                                <td class="remove_combination_td">
                                    <button type="button" class="remove_combination btn btn-danger">Remove</button>
                                </td>
                            </tr>
                        `;
                    });
                }
                $('.variant_combination_table tbody').html(tableHtml);
            }else{
                let tableHtml = "";
                // Loop through the variants and sub-variants to create the combinations
                if (variants.length > 0) {
                    variants.forEach(function(variant, outer_index) {
                        if (subVariants.length > 0) {
                            subVariants.forEach(function(subVariant, index) {
                                let existing = existingValues[variant + '||' + subVariant] || {};
                                // If it's the first row for this variant, add rowspan
                                if (index === 0) {
                                    tableHtml += `
                                        <tr>
                                            <td rowspan="${subVariants.length}">${variant}</td>
                                            <td>${subVariant}</td>
                                            <td><input type="text" name="price[${outer_index}][${index}]" class="form-control price_cls" placeholder="Price" value="${existing.price || ''}"></td>
                                            <td><input type="text" name="stock[${outer_index}][${index}]" class="form-control stock_cls" placeholder="Stock" value="${existing.stock || ''}"></td>
                                            <td><input type="text" name="item_weight[${outer_index}][${index}]" class="form-control weight_cls" placeholder="Weight" value="${existing.weight || ''}"></td>
                                            <td><input type="text" name="item_length[${outer_index}][${index}]" class="form-control length_cls" placeholder="Length" value="${existing.length || ''}"></td>
                                            <td><input type="text" name="item_width[${outer_index}][${index}]" class="form-control width_cls" placeholder="Width" value="${existing.width || ''}"></td>
                                            <td><input type="text" name="item_height[${outer_index}][${index}]" class="form-control height_cls" placeholder="Height" value="${existing.height || ''}"></td>
                                            <td class="remove_combination_td"><button type="button" class="remove_combination btn btn-danger">Remove</button></td>
                                        </tr>
                                    `;
                                } else {
                                    tableHtml += `
                                        <tr>
                                            <td>${subVariant}</td>
                                            <td><input type="text" name="price[${outer_index}][${index}]" class="form-control price_cls" placeholder="Price" value="${existing.price || ''}"></td>
                                            <td><input type="text" name="stock[${outer_index}][${index}]" class="form-control stock_cls" placeholder="Stock" value="${existing.stock || ''}"></td>
                                            <td><input type="text" name="item_weight[${outer_index}][${index}]" class="form-control weight_cls" placeholder="Weight" value="${existing.weight || ''}"></td>
                                            <td><input type="text" name="item_length[${outer_index}][${index}]" class="form-control length_cls" placeholder="Length" value="${existing.length || ''}"></td>
                                            <td><input type="text" name="item_width[${outer_index}][${index}]" class="form-control width_cls" placeholder="Width" value="${existing.width || ''}"></td>
                                            <td><input type="text" name="item_height[${outer_index}][${index}]" class="form-control height_cls" placeholder="Height" value="${existing.height || ''}"></td>
                                            <td class="remove_combination_td"><button type="button" class="remove_combination btn btn-danger">Remove</button></td>
                                        </tr>
                                    `;
                                }
                            });
                        }
                    });
                    $('.variant_combination_table tbody').html(tableHtml);
                }
            }
        }

        // Add variant row

        var count_product_variant = '<?php echo $count_product_variant; ?>';
        console.log('count_product_variant', count_product_variant);
        $("body").on('click','.add_more_variant button',function(e) {
            e.preventDefault();
            var ParamArr = {
                'view': 'admin.product._variation_group',
            }
            $.post("{{ route('admin.get_append_view') }}", {
                params: ParamArr,
                count_product_variant: count_product_variant,
                _token: "{{ csrf_token() }}"
            }, function(data) {
                $('.variation_add_area').append(data);
                sort_agegroup_box();
                toggleImageVariant();
                // Update the table with new combinations
                updateCombinationTable();
                count_product_variant++;
            });
        });

          

        // Remove variant row
        $(document).on('click', '.remove_variant', function () {
            $(this).closest('.variation_row').remove();
            sort_agegroup_box();
            updateCombinationTable();
        });

        $(document).on('click', '.remove_existing_img', function () {
            let wrapper = $(this).closest('.img_wrapper');

            // Remove hidden input ALSO
            wrapper.find('input[type="hidden"]').remove();

            // Remove UI
            wrapper.remove();
        });

        // Add sub-variant row
        var count_sub_variant = '<?php echo $count_sub_variant; ?>';
        $("body").on('click','.add_more_sub_variant button',function(e) {
            e.preventDefault();
            var ParamArr = {
                'view': 'admin.product._sub_variation_group',
            }
            $.post("{{ route('admin.get_append_view') }}", {
                params: ParamArr,
                count_sub_variant: count_sub_variant,
                _token: "{{ csrf_token() }}"
            }, function(data) {
                $('.sub_variant_add_area').append(data);
                sort_sub_variation_group();
                updateCombinationTable();
                count_sub_variant++;
            });
        });


        // Remove sub-variant row
        $(document).on('click', '.remove_sub_variant', function () {
            $(this).closest('.sub_variation_row').remove();
            sort_sub_variation_group();
            updateCombinationTable();
        });

        // Remove combination row
        $(document).on('click', '.remove_combination', function () {
            $(this).closest('tr').remove();
            updateCombinationTable(); // Update the table when combination is removed
        });

        // Update the table when input fields for variants or sub-variants are changed
        $(document).on('input', "input.variant_name_cls, input.sub_variant_name_cls", function() {
            updateCombinationTable();
        });

        $('#vatiant_attribute_select_id').on('change', function () {
            let selectedVariantFamily = $(this).val();  // Get selected variant family name
            if (selectedVariantFamily) {
                var selectedText = $(this).find('option:selected').text();
                // If variant family is selected, show the second column header and update the name
                $('#dynamic_column').text(selectedText); // Update header name based on selection
                //$('#dynamic_column').show();  // Show the second column header

                if ($('.sub_variation_row').length === 0) {
                    $('.add_more_sub_variant button').trigger('click');
                }
            } else {
                // If no variant family is selected, hide the second column header
                //$('#dynamic_column').hide();
            }
            updateCombinationTable();
        });

        $('#attribute_select_id').on('change', function () {
            let selectedAttr = $(this).val();  // Get selected variant family name
            if (selectedAttr) {
                var selectedText = $(this).find('option:selected').text();
                // If variant family is selected, show the second column header and update the name
                $('#parent_variant_column').text(selectedText); // Update header name based on selection

                // ✅ Check if no variation rows exist
                if ($('.variation_row').length === 0) {
                    //appendDefaultVariationRow();
                    $('.add_more_variant button').trigger('click');
                }
            } else {
                // If no variant family is selected, hide the second column header
                $('#parent_variant_column').text('Parent Variant Family');
            }
            //updateCombinationTable();
        });

        /* ======== 3. jQuery Script to Manage Variants Table End ======== */ 

    });

    function sort_agegroup_box(){
        var count = 0;
        $(".variation_row").each(function (){
            var el_this = $(this);
            setNewIndex(el_this,'variant_name_cls',count);    
            count++;
        });
    }

    function sort_sub_variation_group(){
        var count = 0;
        $(".sub_variation_row").each(function (){
            var el_this = $(this);
            setNewIndex(el_this,'sub_variant_name_cls',count);  
            count++;
        });
    }
 
</script>
@endsection
