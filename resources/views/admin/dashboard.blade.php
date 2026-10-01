@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Total Products') }}</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_products']) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-primary"><i class="bx bx-package fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Total Categories') }}</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_categories']) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-info"><i class="bx bx-category fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Total Suppliers') }}</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_suppliers']) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-dark"><i class="bx bx-store fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Total Orders') }}</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_orders']) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-warning"><i class="bx bx-cart fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Total Customers') }}</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_customers']) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-success"><i class="bx bx-user fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Trade Customers') }}</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total_trade_customers']) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-secondary"><i class="bx bx-briefcase-alt-2 fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4 col-xxl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted">{{ translate('Total Revenue') }}</p>
                            <h4 class="mb-0 fw-bold">{{ get_currency_symbol() }} {{ number_format($stats['total_revenue'], 2) }}</h4>
                        </div>
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded-circle bg-label-danger"><i class="bx bx-dollar-circle fs-3"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong class="card-title f-18 d-block">{{ translate('Orders Overview') }}</strong>
                    <small class="text-muted">{{ translate('Orders placed per month') }}</small>
                </div>
                <select id="chartYearSelect" class="form-select form-select-sm" style="width:auto;">
                    @foreach($chart_years as $year)
                        <option value="{{ $year }}" {{ $year == $chart_year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="card-body">
                <div id="ordersChart"></div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title f-18">{{ translate('Recent Orders') }}</strong>
                <a href="{{ route('admin.order') }}" class="btn btn-primary btn-sm">{{ translate('View All') }}</a>
            </div>

            <div class="text-nowrap dataTables_wrapper">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ translate('Order ID') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Total') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($recent_orders as $value)
                            <tr class="@if($value['status_color']) {{ $value['status_color'] }} @endif">
                                <td>#{{ $value['id'] }}</td>
                                <td>{{ date('d F,Y', strtotime($value['created_at'])) }}</td>
                                <td>{{ $value['full_name'] }}</td>
                                <td>{{ strtoupper($value['order_status']) }}</td>
                                <td>{{ get_currency_symbol() }} {{ $value['total'] }}</td>
                                <td>
                                    <a href="{{ route('admin.order.details', encrypt($value['id'])) }}"><i class="fa fa-eye text-primary cursur-pointer" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ translate('No record Found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        var ordersChartEl = document.querySelector('#ordersChart');
        if (ordersChartEl !== null) {
            var themeColors  = (typeof config !== 'undefined') ? config.colors : {};
            var primaryColor = themeColors.primary   || '#696cff';
            var axisColor    = themeColors.axisColor || '#a1acb8';
            var borderColor  = themeColors.borderColor || '#eceef1';

            var ordersChartConfig = {
                series: [{
                    name: '{{ translate("Orders") }}',
                    data: @json($chart_orders),
                }],
                chart: {
                    height: 320,
                    type: 'bar',
                    toolbar: { show: false },
                },
                plotOptions: {
                    bar: {
                        columnWidth: '40%',
                        borderRadius: 8,
                        startingShape: 'rounded',
                        endingShape: 'rounded',
                    },
                },
                colors: [primaryColor],
                dataLabels: { enabled: false },
                grid: {
                    borderColor: borderColor,
                    padding: { top: 0, bottom: -8, left: 10, right: 10 },
                },
                legend: { show: false },
                xaxis: {
                    categories: @json($chart_labels),
                    labels: { style: { fontSize: '12px', colors: axisColor } },
                    axisTicks: { show: false },
                    axisBorder: { show: false },
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '12px', colors: axisColor },
                        formatter: function (val) { return Math.round(val); },
                    },
                },
            };

            var ordersChart = new ApexCharts(ordersChartEl, ordersChartConfig);
            ordersChart.render();

            $(document).on('change', '#chartYearSelect', function () {
                var year = $(this).val();

                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.dashboard.orders_chart") }}',
                    data: { year: year, _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        ordersChart.updateOptions({ xaxis: { categories: response.labels } });
                        ordersChart.updateSeries([{ name: '{{ translate("Orders") }}', data: response.orders }]);
                    },
                    error: function () {
                        danger_msg('Could not load chart data. Please try again.');
                    }
                });
            });
        }
    });
</script>
@endsection
