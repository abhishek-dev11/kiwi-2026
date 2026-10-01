@extends('admin.layout.master')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.customergroups') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" class="relative_cls" action="{{ route('admin.customergroup.add') }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_customer_group['id'] }}">
                    
                    <div class="row">

                        <!-- Group Name -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Group Name') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('group_name') ? 'is-invalid' : '' }}" name="group_name" id="group_name"
                                    type="text" value="{{ old('group_name', $get_customer_group['group_name']) }}"
                                    placeholder="{{ translate('Enter Group Name') }}" >
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('group_name') {{ $message }} @enderror
                                    </div>
                                </div>                                            
                            </div>
                        </div>     

                        <!-- Discount Percentage -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Discount Percentage') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('discount_percentage') ? 'is-invalid' : '' }}" name="discount_percentage" id="discount_percentage"
                                    type="text" value="{{ old('discount_percentage', $get_customer_group['discount_percentage']) }}"
                                    placeholder="{{ translate('Enter Discount Percentage') }}" >
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('discount_percentage') {{ $message }} @enderror
                                    </div>
                                </div>                                            
                            </div>
                        </div>  

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }} <span class="mandatory cls">*</span></label>
                                <select name="status" id="status" class="form-select single-select"
                                    data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $get_customer_group['status']) }}>
                                        {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_customer_group['status']) }}>
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
        e.preventDefault();
        var formData = new FormData(this);
        $('.error_msg').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('admin.customergroup.add') }}",
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
                    // $(".tab-pane").removeClass('active show');
                    // $('.nav-link').removeClass('active');
                    danger_msg("Something is wrong with required field!");
                    activeTab(response.error);
                } else {
                    success_msg(response.success);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.customergroups') }}";
                    }, 1000);
                }
            }
        });
        return false;
    });
</script>
@endsection