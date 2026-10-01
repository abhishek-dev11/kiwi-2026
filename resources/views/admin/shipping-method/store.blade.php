@extends('admin.layout.master')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.shipping-methods') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" class="relative_cls" action="{{ route('admin.shipping-method.add') }}" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_shipping_method['id'] }}">

                    <div class="row">

                        <!-- Title -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" name="title" id="title"
                                    type="text" value="{{ old('title', $get_shipping_method['title']) }}"
                                    placeholder="{{ translate('e.g. Standard Shipping') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('title') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('type') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="type">{{ translate('Type') }} <span class="mandatory cls">*</span></label>
                                <select name="type" id="type" class="form-select single-select" data-allow-clear="true">
                                    <option value="flat" {{ getSelected('flat', $get_shipping_method['type'] ?: 'flat') }}>{{ translate('Flat Rate') }}</option>
                                    <option value="weight_based" {{ getSelected('weight_based', $get_shipping_method['type']) }}>{{ translate('Weight Based') }}</option>
                                </select>
                                <div class="error_msg">@error('type') {{ $message }} @enderror</div>
                            </div>
                        </div>

                        <!-- Flat Rate -->
                        <div class="col-md-6" id="flat_rate_wrapper">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Flat Rate') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('flat_rate') ? 'is-invalid' : '' }}" name="flat_rate" id="flat_rate"
                                    type="number" step="0.01" min="0" value="{{ old('flat_rate', $get_shipping_method['flat_rate']) }}"
                                    placeholder="{{ translate('Enter Flat Rate') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('flat_rate') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                <label class="col-form-label" for="status">{{ translate('Status') }} <span class="mandatory cls">*</span></label>
                                <select name="status" id="status" class="form-select single-select" data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $get_shipping_method['status'] ?: 'Active') }}>{{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $get_shipping_method['status']) }}>{{ translate('Deactive') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Weight Brackets -->
                        <div class="col-md-12 d-none" id="weight_rates_wrapper">
                            <hr>
                            <label class="col-form-label">{{ translate('Weight Brackets') }} <span class="mandatory cls">*</span></label>
                            <div class="form-text mb-2">{{ translate('Leave Max Weight blank on a row to mean "and above" (no upper limit).') }}</div>

                            <table class="table table-bordered" id="weight_rates_table">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Min Weight (kg)') }}</th>
                                        <th>{{ translate('Max Weight (kg)') }}</th>
                                        <th>{{ translate('Rate') }}</th>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="weight_rates_body">
                                    @if(count($weight_rates) > 0)
                                        @foreach($weight_rates as $key => $rate)
                                        <tr>
                                            <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[{{ $key }}][min_weight]" value="{{ $rate['min_weight'] }}" placeholder="0"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[{{ $key }}][max_weight]" value="{{ $rate['max_weight'] }}" placeholder="{{ translate('No limit') }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[{{ $key }}][rate]" value="{{ $rate['rate'] }}" placeholder="0.00"></td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-weight-row"><i class="bx bx-trash"></i></button></td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[0][min_weight]" value="" placeholder="0"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[0][max_weight]" value="" placeholder="{{ translate('No limit') }}"></td>
                                            <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[0][rate]" value="" placeholder="0.00"></td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-weight-row"><i class="bx bx-trash"></i></button></td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tbody id="weight_rate_row_template" class="d-none">
                                    <tr>
                                        <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[__INDEX__][min_weight]" placeholder="0" disabled></td>
                                        <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[__INDEX__][max_weight]" placeholder="{{ translate('No limit') }}" disabled></td>
                                        <td><input type="number" step="0.01" min="0" class="form-control" name="weight_rates[__INDEX__][rate]" placeholder="0.00" disabled></td>
                                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-weight-row"><i class="bx bx-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>

                            <button type="button" class="btn btn-sm btn-primary" id="add_weight_row">
                                <i class="bx bx-plus me-1"></i>{{ translate('Add Bracket') }}
                            </button>

                            <div id="weight_rates_error" class="text-danger mt-2"></div>
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

    function toggleShippingTypeFields() {
        if ($('#type').val() === 'weight_based') {
            $('#flat_rate_wrapper').addClass('d-none');
            $('#weight_rates_wrapper').removeClass('d-none');
        } else {
            $('#flat_rate_wrapper').removeClass('d-none');
            $('#weight_rates_wrapper').addClass('d-none');
        }
    }

    $(document).ready(function() {
        toggleShippingTypeFields();
    });

    $(document).on('change', '#type', function() {
        toggleShippingTypeFields();
    });

    var weightRowIndex = {{ count($weight_rates) > 0 ? count($weight_rates) : 1 }};

    $(document).on('click', '#add_weight_row', function(e) {
        e.preventDefault();
        var $row = $('#weight_rate_row_template tr').clone();
        $row.find('input').each(function() {
            $(this).attr('name', $(this).attr('name').replace('__INDEX__', weightRowIndex))
                   .removeAttr('disabled')
                   .val('');
        });
        weightRowIndex++;
        $('#weight_rates_body').append($row);
    });

    $(document).on('click', '.remove-weight-row', function(e) {
        e.preventDefault();
        var $body = $('#weight_rates_body');
        if ($body.find('tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            $(this).closest('tr').find('input').val('');
        }
    });

    $(document).on("submit", "#addFrom", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('.error_msg').text("");
        $('#weight_rates_error').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('admin.shipping-method.add') }}",
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
                    if (response.error.weight_rates) {
                        $('#weight_rates_error').text(response.error.weight_rates);
                    }
                } else {
                    success_msg(response.success);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.shipping-methods') }}";
                    }, 1000);
                }
            }
        });
        return false;
    });
</script>

@endsection
