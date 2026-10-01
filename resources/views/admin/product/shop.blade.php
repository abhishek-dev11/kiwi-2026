@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <form action="{{ route('admin.products') }}" method="post" id="product_filter" class="">
                        @csrf
                        <div class="row gy-4 gy-sm-1">
                            <div class="col-md-2">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Title') }}</label>
                                    <input class="form-control" name="filter_product_title" type="text" value="{{ $common['filter_product_title']  }}" placeholder="{{ translate('Enter title') }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Supplier') }}</label>
                                    <select class="form-control single-select " name="filter_product_supplier" id="filter_product_supplier">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($get_suppliers as $key => $value) { ?>
                                            <option value="<?php echo $value->id; ?>" {{ getSelected($value->id,$common['filter_product_supplier']) }} ><?php echo $value->full_name; ?> (<?php echo strtoupper($value->type); ?>)</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Top Level Category') }}</label>
                                    <select class="form-control single-select " name="filter_product_top_category" id="filter_product_top_category">
                                        <option value="">Select Top Level Category</option>
                                        <?php foreach ($top_categories as $key => $value) { ?>
                                            <option value="<?php echo $value->id; ?>" {{ getSelected($value->id,$common['filter_product_top_category']) }} ><?php echo $value->title; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Mid Level Category') }}</label>
                                    <select class="form-control single-select " name="filter_product_mid_category" id="filter_product_mid_category">
                                        <option value="">Select Mid Level Category</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Bottom Level Category') }}</label>
                                    <select class="form-control single-select " name="filter_product_bottom_category" id="filter_product_bottom_category">
                                        <option value="">Select Bottom Level Category</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Fourth Level Category') }}</label>
                                    <select class="form-control single-select " name="filter_product_subbottom_category" id="filter_product_subbottom_category">
                                        <option value="">Select Fourth Level Category</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                    <select id="status" name="filter_product_status" class="form-select single-select" data-allow-clear="true">
                                        <option value="" > {{ translate('All') }}</option>
                                        <option value="Active" {{ getSelected('Active', $common['filter_product_status']) }}> {{ translate('Active') }}</option>
                                        <option value="Deactive" {{ getSelected('Deactive', $common['filter_product_status']) }}>{{ translate('Deactive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="col-form-label" for="filter_product_type">{{ translate('Product Type') }}</label>
                                    <select id="filter_product_type" name="filter_product_type" class="form-select single-select" data-allow-clear="true">
                                        <option value="" > {{ translate('All') }}</option>
                                        <option value="simple" {{ getSelected('simple', $common['filter_product_type']) }}> {{ translate('Simple') }}</option>
                                        <option value="variable" {{ getSelected('variable', $common['filter_product_type']) }}>{{ translate('Variable') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex justify-content-left mt-4">
                                <div class="filter_button my-auto">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="is_filter" value="1"><i class="fa fa-filter" aria-hidden="true"></i></button>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="reset" value="1"><i class="fa fa-undo" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <form method="POST" class="relative_cls" action="{{ route('admin.products.delete_seleted') }}" enctype="multipart/form-data" id="DeleteFrom">
            @csrf
            <div class="card shadow mb-4">
                 <div class="card-header">
                    <div class="entries_div">
                        <strong class="card-title f-18">{{ $common['title'] }}</strong>
                        <div class="number_of_page">
                            <a href="{{ route('admin.products.add') }}" class="dt-button create-new btn btn-primary float-end">
                                <span>
                                    <i class="bx bx-plus me-sm-1"></i>
                                    <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span>
                                </span>
                            </a>
                            
                        </div>
                    </div>
                </div>

                {{-- table-responsive --}}
                <div class=" text-nowrap dataTables_wrapper ">

                    <div class="table-responsive">
                        <table class="table table-striped table-hover ">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ translate('Title') }}</th>
                                    <th>{{ translate('Supplier') }}</th>
                                    <th>{{ translate('Category') }}</th>
                                    <th>{{ translate('Regular Price') }}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th>{{ translate('Updated At') }}</th>
                                    <th>{{ translate('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @if ($products->isNotEmpty())
                                    @foreach ($products_list as $key => $value)
                                        <tr>
                                            <td>{{ $products->firstItem() + $key }}</td>
                                            <td style="word-wrap:break-word;white-space: normal;"><?php echo nl2br($value['title']); ?></td>
                                            <td>{{ $value['supplier_name'] }}</td>
                                            <td>{{ implode(' > ', array_filter([$value['top_level_category'], $value['mid_level_category'], $value['bottom_level_category'], $value['fourth_level_category']], fn($cat) => $cat && $cat !== '-')) ?: '-' }}</td>
                                            <td>{{get_currency_symbol()}} {{ $value['regular_price'] }}</td>
                                            <td>{!! checkStatus($value['status']) !!}</td>
                                            <td>
                                                @if($value['updated_at'])
                                                    {{ date('d M Y,', strtotime($value['updated_at'])) }}<br>
                                                    {{ date('h:i A', strtotime($value['updated_at'])) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                        data-bs-toggle="dropdown"><i
                                                            class="bx bx-dots-vertical-rounded"></i></button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.products.edit', encrypt($value['id'])) }}"><i
                                                                class="bx bx-edit-alt me-1"></i> {{ translate('Edit') }}</a>
                                                        <a class="dropdown-item confirm-delete" href="javascript:void(0);"
                                                            data-href="{{ route('admin.products.delete', encrypt($value['id'])) }}"><i
                                                                class="bx bx-trash me-1"></i>{{ translate('Delete') }} </a>
                                                        <a class="dropdown-item"
                                                            href="{{ route('product-detail', $value['slug']) }}" target="_blank"><i
                                                                class="bx bx-link-external me-1"></i> {{ translate('View') }}</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">{{ translate('No record Found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="dataTables_paginate paging_simple_numbers mt-4">
                            {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>

                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        // Select/Deselect all checkboxes
        $('#selectAll').on('change', function() {
            $('.individual').prop('checked', $(this).prop('checked'));
        });

        // If any individual checkbox is unchecked, uncheck "Select All"
        // If all are checked, check "Select All"
        $('.individual').on('change', function() {
            if ($('.individual:checked').length === $('.individual').length) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
        });
    });

</script>
<script type="text/javascript">
    $(document).on("submit", "#DeleteFrom", function(e) {
        e.preventDefault(); // Prevent default form submit
         if ($('.individual:checked').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Please select at least one product to delete.'
            });
            return;
        }
        Swal.fire({
            title: 'Are you sure you want to delete selected products?',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: "#8486e3",
        }).then((result) => {
            if (result.isConfirmed) {
                // Manually submit the form
                this.submit();
            }
        });
    });
</script>
<script type="text/javascript">
    //filter_product_top_category
    $(document).ready(function () { 
        $(document).on("change", '#filter_product_top_category', function() {
            select_cat('filter_product_mid_category',$(this).val());
        });
    });
    <?php if(old('filter_product_top_category', null) != null){ ?>
        <?php if(old('filter_product_mid_category', null) != null){ ?>
            select_cat('filter_product_mid_category','<?php echo old('filter_product_top_category', null); ?>','<?php echo old('filter_product_mid_category', null); ?>');
        <?php }else{ ?>
            select_cat('filter_product_mid_category','<?php echo old('filter_product_top_category', null); ?>');
        <?php } ?>
    <?php }elseif($common['filter_product_top_category']){ ?>
        <?php if($common['filter_product_mid_category']){ ?>
            select_cat('filter_product_mid_category','<?php echo $common['filter_product_top_category']; ?>','<?php echo $common['filter_product_mid_category']; ?>');
        <?php }else{ ?>
            select_cat('filter_product_mid_category','<?php echo $common['filter_product_top_category']; ?>');
        <?php } ?>
    <?php } ?>

    //filter_product_mid_category
    $(document).ready(function () { 
        $(document).on("change", '#filter_product_mid_category', function() {
            select_cat('filter_product_bottom_category',$(this).val());
            $('#filter_product_subbottom_category').html('<option value="">Select Fourth Level Category</option>');
        });
    });
    <?php if(old('filter_product_mid_category', null) != null){ ?>
        <?php if(old('filter_product_bottom_category', null) != null){ ?>
            select_cat('filter_product_bottom_category','<?php echo old('filter_product_mid_category', null); ?>','<?php echo old('filter_product_bottom_category', null); ?>');
        <?php }else{ ?>
            select_cat('filter_product_bottom_category','<?php echo old('filter_product_mid_category', null); ?>');
        <?php } ?>
    <?php }elseif($common['filter_product_mid_category']){ ?>
        <?php if($common['filter_product_bottom_category']){ ?>
            select_cat('filter_product_bottom_category','<?php echo $common['filter_product_mid_category']; ?>','<?php echo $common['filter_product_bottom_category']; ?>');
        <?php }else{ ?>
            select_cat('filter_product_bottom_category','<?php echo $common['filter_product_mid_category']; ?>');
        <?php } ?>
    <?php } ?>

    //filter_product_bottom_category
    $(document).ready(function () {
        $(document).on("change", '#filter_product_bottom_category', function() {
            select_cat('filter_product_subbottom_category',$(this).val());
        });
    });
    <?php if(old('filter_product_bottom_category', null) != null){ ?>
        <?php if(old('filter_product_subbottom_category', null) != null){ ?>
            select_cat('filter_product_subbottom_category','<?php echo old('filter_product_bottom_category', null); ?>','<?php echo old('filter_product_subbottom_category', null); ?>');
        <?php }else{ ?>
            select_cat('filter_product_subbottom_category','<?php echo old('filter_product_bottom_category', null); ?>');
        <?php } ?>
    <?php }elseif($common['filter_product_bottom_category']){ ?>
        <?php if($common['filter_product_subbottom_category']){ ?>
            select_cat('filter_product_subbottom_category','<?php echo $common['filter_product_bottom_category']; ?>','<?php echo $common['filter_product_subbottom_category']; ?>');
        <?php }else{ ?>
            select_cat('filter_product_subbottom_category','<?php echo $common['filter_product_bottom_category']; ?>');
        <?php } ?>
    <?php } ?>


    function select_cat(idddd,category,child='0'){
        var cat_title = "Bottom Level Category";
        if (idddd=='filter_product_mid_category') {
            cat_title = "Mid Level Category";
        } else if (idddd=='filter_product_subbottom_category') {
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
@endsection
