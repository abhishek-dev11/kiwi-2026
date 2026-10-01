@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
                <a href="{{ route('admin.subbottomcategories') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.subbottomcategories.add') }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_category['id'] }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Title') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title" type="text" value="{{ old('title', $get_category['title']) }}" placeholder="{{ translate('Enter Category Name') }}">
                                @error('title')
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}</div>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6"></div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('image') ? 'is-invalid' : '' }}">
                                <label class="col-form-label">{{ translate('Image') }}</label>
                                <input type="file" name="image" class="form-control { $errors->has('image') ? 'form-control-danger' : '' }}" id="bs-validation-upload-file" onchange="loadFile(event,'cat_image')">
                                <div class="show-image">
                                    <img class="user-img img-circle img-css" id="cat_image" src="{{ $get_category['image'] != '' ? get_image_upload('Category/'.$get_category['image']) : asset('placeholder/category/image.png') }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Top Level Category') }}</label>
                                <select class="form-control select2 {{ $errors->has('top_level_category_id') ? 'is-invalid' : '' }}" name="top_level_category_id" id="top_level_category_id">
                                    <option value="">Select Top Level Category</option>
                                    @foreach ($top_categories as $value)
                                        <option value="{{ $value->id }}" {{ getSelected($value->id, old('top_level_category_id', $get_category['top_level_category_id'])) }}>{{ $value->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Mid Level Category') }}</label>
                                <select class="form-control select2 {{ $errors->has('mid_level_category_id') ? 'is-invalid' : '' }}" name="mid_level_category_id" id="mid_level_category_id">
                                    <option value="">Select Mid Level Category</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="col-form-label">{{ translate('Bottom Level Category') }}</label>
                                <select class="form-control select2 {{ $errors->has('bottom_level_category_id') ? 'is-invalid' : '' }}" name="bottom_level_category_id" id="bottom_level_category_id">
                                    <option value="">Select Bottom Level Category</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('is_menu') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="is_menu">{{ translate('Show on Menu?') }}</label>
                                <select id="is_menu" name="is_menu" class="form-select single-select" data-allow-clear="true">
                                    <option value="">{{ translate('No') }}</option>
                                    <option value="1" {{ getSelected('1', $get_category['is_menu']) }}>{{ translate('Yes') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="status" class="form-select single-select" data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $get_category['status']) }}>{{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_category['status']) }}>{{ translate('Deactive') }}</option>
                                </select>
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
@endsection
@section('script')
<script type="text/javascript">
    $(document).on("submit", "#addFrom", function() {
        jQuery('#common_loader').show();
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on("change", '#top_level_category_id', function() {
            select_cat('mid_level_category_id', $(this).val());
            $('#bottom_level_category_id').html('<option value="">Select Bottom Level Category</option>');
        });
        $(document).on("change", '#mid_level_category_id', function() {
            select_cat('bottom_level_category_id', $(this).val());
        });
    });

    @if(old('top_level_category_id', null) != null)
        select_cat('mid_level_category_id', '{{ old('top_level_category_id', null) }}', '{{ old('mid_level_category_id', null) }}');
    @elseif($get_category['top_level_category_id'])
        select_cat('mid_level_category_id', '{{ $get_category['top_level_category_id'] }}', '{{ $get_category['mid_level_category_id'] }}');
    @endif

    @if(old('mid_level_category_id', null) != null)
        select_cat('bottom_level_category_id', '{{ old('mid_level_category_id', null) }}', '{{ old('bottom_level_category_id', null) }}');
    @elseif($get_category['mid_level_category_id'])
        select_cat('bottom_level_category_id', '{{ $get_category['mid_level_category_id'] }}', '{{ $get_category['bottom_level_category_id'] }}');
    @endif

    function select_cat(target, category, child='0'){
        $.ajax({
            url: "{{ route('admin.categories.get_mid_level_category_id') }}",
            type: 'post',
            data: {
                'title': target == 'mid_level_category_id' ? 'Mid Level Category' : 'Bottom Level Category',
                'top_level_category_id': category,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#' + target).html(response);
            }
        });
    }
</script>
@endsection
