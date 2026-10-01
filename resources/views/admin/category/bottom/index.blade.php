@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">

                <form action="{{ route('admin.bottomcategories') }}" method="post" id="product_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-2">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}</label>
                                <input class="form-control" name="filter_bottomcategory_title" type="text" value="{{ $common['filter_bottomcategory_title']  }}" placeholder="{{ translate('Enter title') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="filter_bottomcategory_top_category">{{ translate('Top Category') }}</label>
                                <select id="filter_bottomcategory_top_category" name="filter_bottomcategory_top_category" class="form-select single-select" data-allow-clear="true">
                                    <option value="" > {{ translate('All') }}</option>
                                    <?php foreach ($top_categories as $key => $value) { ?>
                                        <option value="<?php echo $value->id; ?>" {{ getSelected($value->id, $common['filter_bottomcategory_top_category']) }} ><?php echo $value->title; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="filter_bottomcategory_mid_category">{{ translate('Mid Category') }}</label>
                                <select id="filter_bottomcategory_mid_category" name="filter_bottomcategory_mid_category" class="form-select single-select" data-allow-clear="true">
                                    <option value="" > {{ translate('Mid Level Category') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="filter_bottomcategory_status" class="form-select single-select" data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $common['filter_bottomcategory_status']) }}> {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $common['filter_bottomcategory_status']) }}>{{ translate('Deactive') }}</option>
                                </select>
                            </div>
                        </div>
                   
                        <div class="col-md-3 d-flex justify-content-left mt-4">
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

        <div class="card shadow mb-4">
             <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.bottomcategories.add') }}" class="dt-button create-new btn btn-primary float-end">
                    <span><i class="bx bx-plus me-sm-1"></i>
                        <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span></span>
                </a>

            </div>

            {{-- table-responsive --}}
            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Title') }}</th>
                            <th>{{ translate('Top Level Category') }}</th>
                            <th>{{ translate('Mid Level Category') }}</th>
                            <!-- <th>{{ translate('Sort Number') }}</th> -->
                            <th>{{ translate('Menu') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($categories->isNotEmpty())
                            @foreach ($categories_list as $key => $value)
                                <tr>
                                    <td>{{ $categories->firstItem() + $key }}</td>
                                    <td>{{ $value['title'] }}</td>
                                    <td>{{ $value['top_level_category'] }}</td>
                                    <td>{{ $value['mid_level_category'] }}</td>
                                    <!-- <td>{{ ($value['sort_number']) ? $value['sort_number'] : '-' }}</td> -->
                                    <td>{{ ($value['is_menu']==1) ? 'Yes' : 'No' }}</td>
                                    <td>{!! checkStatus($value['status']) !!}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.bottomcategories.edit', encrypt($value['id'])) }}"><i
                                                        class="bx bx-edit-alt me-1"></i> {{ translate('Edit') }}</a>
                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);"
                                                    data-href="{{ route('admin.bottomcategories.delete', encrypt($value['id'])) }}"><i
                                                        class="bx bx-trash me-1"></i>{{ translate('Delete') }} </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">{{ translate('No record Found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="row">
                    <div class="dataTables_paginate paging_simple_numbers mt-4">
                        {{ $categories->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function () { 
        $(document).on("change", '#filter_bottomcategory_top_category', function() {
            select_cat($(this).val());
        });
    });
    <?php if($common['filter_bottomcategory_top_category']){ ?>
        <?php if($common['filter_bottomcategory_mid_category']){ ?>
            select_cat('<?php echo $common['filter_bottomcategory_top_category']; ?>','<?php echo $common['filter_bottomcategory_mid_category']; ?>');
        <?php }else{ ?>
            select_cat('<?php echo $common['filter_bottomcategory_top_category']; ?>');
        <?php } ?>
    <?php } ?>

    function select_cat(top_level_category_id,child='0'){
        $.ajax({
            url: "{{ route('admin.categories.get_mid_level_category_id') }}",
            type: 'post',
            data: {
                'title': 'Mid Level Category',
                'top_level_category_id': top_level_category_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#filter_bottomcategory_mid_category').html(response);
            }
        });
    }
</script>
@endsection