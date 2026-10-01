@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.topcategories') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}

                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.topcategories.add') }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_category['id'] }}">
                    <div class="row">   

                        <!-- Title -->                     
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title"
                                    type="text" value="{{ old('title', $get_category['title']) }}"
                                    placeholder="{{ translate('Enter Category Name') }}">
                                @error('title')
                                    <div
                                        class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                                        </div>
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Image -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('image') ? 'is-invalid' : '' }}">
                                <label class="col-form-label">{{ translate('Image') }}</label>
                                <input type="file" name="image"
                                    class="form-control { $errors->has('image') ? 'form-control-danger' : '' }}"
                                    id="bs-validation-upload-file" onchange="loadFile(event,'cat_image')">
                                <div class="show-image">
                                    <img 
                                        id="cat_image"
                                        class="user-img img-circle img-css" 
                                        src="{{ $get_category['image'] != '' ? get_image_upload('Category/'.$get_category['image']) : asset('placeholder/category/image.png') }}">
                                </div>
                            </div>
                        </div> 
                        
                        <!-- Sort Number -->
                        <!-- <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Sort Number') }} </label>
                                <input class="form-control {{ $errors->has('sort_number') ? 'is-invalid' : '' }}" name="sort_number" id="sort_number"
                                    type="text" value="{{ old('sort_number', $get_category['sort_number']) }}" >
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('sort_number') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div> -->

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="status" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $get_category['status']) }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_category['status']) }}>
                                        {{ translate('Deactive') }}</option>
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
    </div>
@endsection

@section('script')
<script type="text/javascript">
    $(document).on("submit", "#addFrom", function(e) {
        jQuery('#common_loader').show();
    });
</script>
@endsection