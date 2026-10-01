@extends('admin.layout.master')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.coupons') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" class="relative_cls" action="{{ route('admin.coupon.add') }}" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_coupon['id'] }}">

                    <div class="row">

                        <!-- Code -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Coupon Code') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" name="code" id="code"
                                    type="text" style="text-transform: uppercase;" value="{{ old('code', $get_coupon['code']) }}"
                                    placeholder="{{ translate('e.g. SAVE10') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('code') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('type') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="type">{{ translate('Type') }} <span class="mandatory cls">*</span></label>
                                <select name="type" id="type" class="form-select single-select" data-allow-clear="true">
                                    <option value="fixed" {{ getSelected('fixed', $get_coupon['type']) }}>{{ translate('Fixed Amount') }}</option>
                                    <option value="percentage" {{ getSelected('percentage', $get_coupon['type']) }}>{{ translate('Percentage') }}</option>
                                </select>
                                <div class="error_msg">@error('type') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <!-- Value -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Value') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('value') ? 'is-invalid' : '' }}" name="value" id="value"
                                    type="number" step="0.01" min="0" value="{{ old('value', $get_coupon['value']) }}"
                                    placeholder="{{ translate('Enter Value') }}">
                                <div class="form-text">{{ translate('Flat amount off, or a % off if type is Percentage.') }}</div>
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('value') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Max Discount Amount -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Max Discount Amount') }}</label>
                                <input class="form-control {{ $errors->has('max_discount_amount') ? 'is-invalid' : '' }}" name="max_discount_amount" id="max_discount_amount"
                                    type="number" step="0.01" min="0" value="{{ old('max_discount_amount', $get_coupon['max_discount_amount']) }}"
                                    placeholder="{{ translate('Optional cap - only applies to Percentage coupons') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('max_discount_amount') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Min Order Amount -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Minimum Order Amount') }}</label>
                                <input class="form-control {{ $errors->has('min_order_amount') ? 'is-invalid' : '' }}" name="min_order_amount" id="min_order_amount"
                                    type="number" step="0.01" min="0" value="{{ old('min_order_amount', $get_coupon['min_order_amount']) }}"
                                    placeholder="{{ translate('Optional - cart subtotal must reach this amount') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('min_order_amount') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Limit -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Usage Limit') }}</label>
                                <input class="form-control {{ $errors->has('usage_limit') ? 'is-invalid' : '' }}" name="usage_limit" id="usage_limit"
                                    type="number" step="1" min="1" value="{{ old('usage_limit', $get_coupon['usage_limit']) }}"
                                    placeholder="{{ translate('Optional - total number of times this code can be used') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('usage_limit') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Start Date') }}</label>
                                <input class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" name="start_date" id="start_date"
                                    type="date" value="{{ old('start_date', $get_coupon['start_date']) }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('start_date') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('End Date') }}</label>
                                <input class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}" name="end_date" id="end_date"
                                    type="date" value="{{ old('end_date', $get_coupon['end_date']) }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('end_date') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }} <span class="mandatory cls">*</span></label>
                                <select name="status" id="status" class="form-select single-select" data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $get_coupon['status'] ?: 'Active') }}>{{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_coupon['status']) }}>{{ translate('Deactive') }}</option>
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
    $(document).on("submit", "#addFrom", function(e) {
        e.preventDefault();
        $('#code').val($('#code').val().toUpperCase());
        var formData = new FormData(this);
        $('.error_msg').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('admin.coupon.add') }}",
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
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.coupons') }}";
                    }, 1000);
                }
            }
        });
        return false;
    });
</script>

@endsection
