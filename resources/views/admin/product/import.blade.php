@extends('admin.layout.master')

@section('content')
<style>
    /* Upload Zone */
    .upload-container {
        background-color: #EDEFF3;
        border: 1.5px dashed #465785;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .upload-container:hover { border-color: #0fb7ba; background: #e4f8f8; }
    .upload-container.drag-over { border-color: #0fb7ba; background: #d6f5f5; }
    .upload-container .upload-area {
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }
    .upload-container .upload-area h2 {
        font-size: 18px;
        font-weight: 500;
        margin: 0;
        color: #333;
    }
    .upload-container .upload-area .selected-file {
        font-size: 13px;
        color: #666;
    }
    .upload-container #file { display: none; }

    /* File name badge */
    .file-badge {
        display: none;
        align-items: center;
        gap: 6px;
        background: #e6f1fb;
        color: #185FA5;
        font-size: 12px;
        font-weight: 500;
        padding: 4px 12px;
        border-radius: 20px;
        margin-top: 8px;
    }

    /* Progress Section */
    #progress-section { display: none; margin-top: 20px; }

    .progress-track {
        height: 10px;
        background: #eee;
        border-radius: 20px;
        overflow: hidden;
    }
    #progressBar {
        height: 100%;
        width: 0%;
        border-radius: 20px;
        background: linear-gradient(90deg, #4facfe, #00f2fe);
        transition: width 0.4s ease;
    }
    #progressBar.is-done   { background: linear-gradient(90deg, #43e97b, #38f9d7); }
    #progressBar.is-failed { background: linear-gradient(90deg, #f5365c, #f56036); }
    #progressBar.is-stale  { background: linear-gradient(90deg, #f6ad55, #ed8936); }

    .progress-meta {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
    }
    .progress-meta #statusText  { font-size: 13px; color: #555; }
    .progress-meta #progressPct { font-size: 13px; font-weight: 600; color: #333; }

    /* Status dot */
    .status-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        font-size: 13px;
        color: #777;
    }
    .status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #4facfe;
        flex-shrink: 0;
        animation: blink 1.2s infinite;
    }
    .status-dot.done   { background: #43e97b; animation: none; }
    .status-dot.failed { background: #f5365c; animation: none; }
    .status-dot.stale  { background: #ed8936; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.25} }

    /* Stale warning banner */
    .stale-banner {
        display: flex; align-items: flex-start; gap: 12px;
        background: #fff8ed; border: 1px solid #f6ad55;
        border-radius: 8px; padding: 12px 16px; margin-top: 14px; flex-wrap: wrap;
    }
    .stale-banner .stale-icon { font-size: 22px; color: #ed8936; flex-shrink: 0; padding-top: 2px; }
    .stale-banner .stale-body { flex: 1; min-width: 200px; font-size: 13px; line-height: 1.6; }
    .stale-banner .stale-body strong { display: block; color: #c05621; }
    .stale-banner .stale-cmd {
        display: inline-block; font-size: 11px; margin-top: 4px;
        background: #fef3e2; padding: 3px 8px; border-radius: 4px; color: #7b341e;
        font-family: monospace;
    }

    /* Chunk visualizer */
    .chunks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(20px, 1fr));
        gap: 4px;
        margin-top: 12px;
    }
    .chunk-cell {
        height: 7px;
        border-radius: 3px;
        background: #e0e0e0;
        transition: background 0.3s;
    }
    .chunk-cell.active { background: #4facfe; }
    .chunk-cell.done   { background: #43e97b; }
    .chunk-cell.failed { background: #f5365c; }

    /* Loader overlay */
    .loader_cls { position: relative; }
    .loader_cls #common_loader {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.9);
        z-index: 99;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    #common_loader img { width: 200px; max-width: 50%; }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card shadow mb-4">

        {{-- Header --}}
        <div class="card-header d-flex align-items-center justify-content-between">
            <strong class="card-title f-18">{{ $common['title'] }}</strong>
            <div>
                <a href="{{ route('admin.productsimport') }}" class="btn btn-secondary btn-sm">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
        </div>

        <div class="card-header d-flex align-items-center" style="padding-top: 0; gap: 10px;">
            <button type="button" class="btn btn-outline-primary btn-sm" id="aussiepacificSyncBtn">
                <span class="tf-icons bx bx-sync me-1"></span>Sync Aussie Pacific Catalog
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="banburySyncBtn">
                <span class="tf-icons bx bx-sync me-1"></span>Sync Cloke Clothing Catalog
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="logolineSyncBtn">
                <span class="tf-icons bx bx-sync me-1"></span>Sync Logo Line Catalog
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="fashionbizSyncBtn">
                <span class="tf-icons bx bx-sync me-1"></span>Sync Biz Collection Catalog
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="trendsSyncBtn">
                <span class="tf-icons bx bx-sync me-1"></span>Sync Trends Catalog
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="premiumapparelSyncBtn">
                <span class="tf-icons bx bx-sync me-1"></span>Sync Premium Apparel Catalog
            </button>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.productsimport') }}"
                  enctype="multipart/form-data" id="importForm">
                @csrf

                <div class="row">

                    {{-- Supplier --}}
                    <div class="col-md-6 mb-3">
                        <label class="col-form-label">
                            {{ translate('Supplier') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="supplier_id" id="supplier_id"
                                class="form-control select2 {{ $errors->has('supplier_id') ? 'is-invalid' : '' }}">
                            <option value="">Select Supplier</option>
                            @foreach($get_suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('supplier_id') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Progress Section --}}
                    <div class="col-md-12" id="progress-section">
                        <div class="progress-track">
                            <div id="progressBar"></div>
                        </div>
                        <div class="progress-meta">
                            <span id="statusText">Preparing import...</span>
                            <span id="progressPct">0%</span>
                        </div>
                        <div class="status-row">
                            <div class="status-dot" id="statusDot"></div>
                            <span id="statusDetail">Waiting for queue worker...</span>
                        </div>
                        {{-- Chunk cells will be injected here --}}
                        <div class="chunks-grid mt-2" id="chunksGrid"></div>

                        {{-- Stale / slow-worker warning (hidden by default) --}}
                        <div class="stale-banner" id="staleBanner" style="display:none;">
                            <div class="stale-icon"><i class="bx bx-time-five"></i></div>
                            <div class="stale-body">
                                <strong>Worker is slow or paused</strong>
                                <span id="staleDetail">No chunk progress for 90 s — the import is likely still running in the background.</span><br>
                                <code class="stale-cmd">php artisan queue:work --queue=imports,image-downloads</code>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning mt-1" id="resumePollingBtn">
                                <i class="bx bx-refresh me-1"></i> Resume Monitoring
                            </button>
                        </div>
                    </div>

                    {{-- File Upload --}}
                    <div class="col-md-12 mt-3">
                        <div class="loader_cls">
                            <div id="common_loader" style="display:none;">
                                <img src="{{ asset('assets/images/loader.gif') }}" alt="Loading...">
                            </div>

                            <div class="upload-container" id="uploadContainer">
                                <input type="file" name="file" id="file" accept=".csv,.xlsx" />
                                <div class="upload-area">
                                    <img src="{{ asset('assets/images/upload-file.svg') }}"
                                         width="48" alt="Upload">
                                    <h2>Choose CSV or XLSX File</h2>
                                    <div class="selected-file" id="selectedFileName">
                                        Supported formats: CSV, XLSX
                                    </div>
                                    <div class="file-badge" id="fileBadge">
                                        <i class="bx bx-file"></i>
                                        <span id="fileBadgeName"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /row --}}

                {{-- Submit --}}
                <div class="row mt-4">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="tf-icons bx bx-upload me-1"></span>
                            Upload &amp; Import
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection


@section('script')
<script>
$(function () {

    // ── State ──────────────────────────────────────────────────
    let pollInterval  = null;
    let totalChunks   = 0;
    let lastProgressAt = 0;   // ms timestamp when chunks last advanced
    let lastChunksSeen = 0;   // highest completed_chunks seen so far
    let isStale        = false;

    const SOFT_STALE_MS = 90000;   // 90 s — show warning, keep polling at slow rate
    const HARD_STOP_MS  = 300000;  // 5 min — something really broke, stop

    // ─────────────────────────────────────────────
    // FILE INPUT — click, change, drag & drop
    // ─────────────────────────────────────────────
    $('#uploadContainer').on('click', function (e) {
        if (!$(e.target).is('#file')) $('#file').trigger('click');
    });
    $('#file').on('click', function (e) { e.stopPropagation(); });

    $('#file').on('change', function () {
        const file = this.files[0];
        if (file) showFileBadge(file.name);
    });

    $('#uploadContainer')
        .on('dragover', function (e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        })
        .on('dragleave drop', function () {
            $(this).removeClass('drag-over');
        })
        .on('drop', function (e) {
            e.preventDefault();
            const file = e.originalEvent.dataTransfer.files[0];
            if (!file) return;
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['csv','xlsx'].includes(ext)) {
                danger_msg('Please upload a CSV or XLSX file.');
                return;
            }
            const transfer = new DataTransfer();
            transfer.items.add(file);
            document.getElementById('file').files = transfer.files;
            showFileBadge(file.name);
        });

    function showFileBadge(name) {
        $('#selectedFileName').text(name);
        $('#fileBadgeName').text(name);
        $('#fileBadge').css('display', 'inline-flex');
    }

    // ─────────────────────────────────────────────
    // FORM SUBMIT
    // ─────────────────────────────────────────────
    $('#importForm').on('submit', function (e) {
        e.preventDefault();

        if (!$('#supplier_id').val()) {
            danger_msg('Please select a supplier.');
            return false;
        }
        if (!$('#file')[0].files.length) {
            danger_msg('Please choose a CSV or XLSX file.');
            return false;
        }

        const formData = new FormData(this);
        $('#submitBtn').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...'
        );
        $('#common_loader').css('display', 'flex');

        $.ajax({
            type        : 'POST',
            url         : "{{ route('admin.productsimport') }}",
            data        : formData,
            cache       : false,
            contentType : false,
            processData : false,
            headers     : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                $('#common_loader').hide();
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 0);
                    $('#submitBtn').html(
                        '<span class="tf-icons bx bx-loader-circle me-1"></span> Importing...'
                    );
                    startPolling(response.import_id, response.chunks || 0);
                } else {
                    resetSubmitBtn();
                    danger_msg(response.message || 'Upload failed. Please try again.');
                }
            },
            error: function (xhr) {
                $('#common_loader').hide();
                resetSubmitBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });

        return false;
    });

    // ─────────────────────────────────────────────
    // AUSSIE PACIFIC API SYNC
    // ─────────────────────────────────────────────
    $('#aussiepacificSyncBtn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Starting sync...'
        );

        $.ajax({
            type    : 'POST',
            url     : "{{ route('admin.aussiepacific.import') }}",
            headers : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 1);
                    $btn.html('<span class="tf-icons bx bx-loader-circle me-1"></span> Syncing...');
                    startPolling(response.import_id, response.chunks || 1);
                } else {
                    resetSyncBtn();
                    danger_msg(response.message || 'Could not start the sync. Please try again.');
                }
            },
            error: function (xhr) {
                resetSyncBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });
    });

    function resetSyncBtn() {
        $('#aussiepacificSyncBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-sync me-1"></span> Sync Aussie Pacific Catalog');
    }

    // ─────────────────────────────────────────────
    // BANBURY API SYNC
    // ─────────────────────────────────────────────
    $('#banburySyncBtn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Starting sync...'
        );

        $.ajax({
            type    : 'POST',
            url     : "{{ route('admin.banbury.import') }}",
            headers : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 1);
                    $btn.html('<span class="tf-icons bx bx-loader-circle me-1"></span> Syncing...');
                    startPolling(response.import_id, response.chunks || 1);
                } else {
                    resetBanburySyncBtn();
                    danger_msg(response.message || 'Could not start the sync. Please try again.');
                }
            },
            error: function (xhr) {
                resetBanburySyncBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });
    });

    function resetBanburySyncBtn() {
        $('#banburySyncBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-sync me-1"></span> Sync Cloke Clothing Catalog');
    }

    // ─────────────────────────────────────────────
    // LOGO LINE API SYNC
    // ─────────────────────────────────────────────
    $('#logolineSyncBtn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Starting sync...'
        );

        $.ajax({
            type    : 'POST',
            url     : "{{ route('admin.logoline.import') }}",
            headers : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 1);
                    $btn.html('<span class="tf-icons bx bx-loader-circle me-1"></span> Syncing...');
                    startPolling(response.import_id, response.chunks || 1);
                } else {
                    resetLogolineSyncBtn();
                    danger_msg(response.message || 'Could not start the sync. Please try again.');
                }
            },
            error: function (xhr) {
                resetLogolineSyncBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });
    });

    function resetLogolineSyncBtn() {
        $('#logolineSyncBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-sync me-1"></span> Sync Logo Line Catalog');
    }


    // ─────────────────────────────────────────────
    // FASHION BIZ (BIZ COLLECTION) API SYNC
    // ─────────────────────────────────────────────
    $('#fashionbizSyncBtn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Starting sync...'
        );

        $.ajax({
            type    : 'POST',
            url     : "{{ route('admin.fashionbiz.import') }}",
            headers : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 1);
                    $btn.html('<span class="tf-icons bx bx-loader-circle me-1"></span> Syncing...');
                    startPolling(response.import_id, response.chunks || 1);
                } else {
                    resetFashionBizSyncBtn();
                    danger_msg(response.message || 'Could not start the sync. Please try again.');
                }
            },
            error: function (xhr) {
                resetFashionBizSyncBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });
    });

    function resetFashionBizSyncBtn() {
        $('#fashionbizSyncBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-sync me-1"></span> Sync Biz Collection Catalog');
    }


    // ─────────────────────────────────────────────
    // TRENDS API SYNC
    // ─────────────────────────────────────────────
    $('#trendsSyncBtn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Starting sync...'
        );

        $.ajax({
            type    : 'POST',
            url     : "{{ route('admin.trends.import') }}",
            headers : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 1);
                    $btn.html('<span class="tf-icons bx bx-loader-circle me-1"></span> Syncing...');
                    startPolling(response.import_id, response.chunks || 1);
                } else {
                    resetTrendsSyncBtn();
                    danger_msg(response.message || 'Could not start the sync. Please try again.');
                }
            },
            error: function (xhr) {
                resetTrendsSyncBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });
    });

    function resetTrendsSyncBtn() {
        $('#trendsSyncBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-sync me-1"></span> Sync Trends Catalog');
    }


    // ─────────────────────────────────────────────
    // PREMIUM APPAREL API SYNC
    // ─────────────────────────────────────────────
    $('#premiumapparelSyncBtn').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1"></span> Starting sync...'
        );

        $.ajax({
            type    : 'POST',
            url     : "{{ route('admin.premiumapparel.import') }}",
            headers : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status) {
                    $('#progress-section').slideDown(300);
                    buildChunkGrid(response.chunks || 1);
                    $btn.html('<span class="tf-icons bx bx-loader-circle me-1"></span> Syncing...');
                    startPolling(response.import_id, response.chunks || 1);
                } else {
                    resetPremiumApparelSyncBtn();
                    danger_msg(response.message || 'Could not start the sync. Please try again.');
                }
            },
            error: function (xhr) {
                resetPremiumApparelSyncBtn();
                danger_msg('Error: ' + (xhr.responseJSON?.message || 'Server error. Check logs.'));
            }
        });
    });

    function resetPremiumApparelSyncBtn() {
        $('#premiumapparelSyncBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-sync me-1"></span> Sync Premium Apparel Catalog');
    }

    // ─────────────────────────────────────────────
    // BUILD CHUNK GRID
    // ─────────────────────────────────────────────
    function buildChunkGrid(chunks) {
        totalChunks    = chunks;
        lastChunksSeen = 0;
        const grid     = $('#chunksGrid').empty();
        for (let i = 0; i < chunks; i++) {
            grid.append(`<div class="chunk-cell" id="chunk-${i}" title="Chunk ${i+1}"></div>`);
        }
    }

    // ─────────────────────────────────────────────
    // POLLING
    // ─────────────────────────────────────────────
    function startPolling(importId, knownChunks) {
        if (pollInterval) clearInterval(pollInterval);
        lastProgressAt = Date.now();
        lastChunksSeen = 0;
        isStale        = false;
        $('#staleBanner').hide();

        doPoll(importId, knownChunks);
        pollInterval = setInterval(function () {
            doPoll(importId, knownChunks);
        }, 2000);
    }

    function setSlowPoll(importId, knownChunks) {
        clearInterval(pollInterval);
        pollInterval = setInterval(function () {
            doPoll(importId, knownChunks);
        }, 10000);
    }

    function setFastPoll(importId, knownChunks) {
        clearInterval(pollInterval);
        pollInterval = setInterval(function () {
            doPoll(importId, knownChunks);
        }, 2000);
    }

    function doPoll(importId, knownChunks) {
        $.get('/admin/import-progress/' + importId)
            .done(function (res) {

                // ── Session expired ───────────────────────────────────
                if (res.status === 'not_found') {
                    clearInterval(pollInterval);
                    $('#staleBanner').show();
                    $('#staleDetail').text(
                        'Import session has expired (3-hour limit). Please re-upload the file.'
                    );
                    $('#progressBar').removeClass('is-stale').addClass('is-failed');
                    $('#statusDot').removeClass().addClass('status-dot failed');
                    resetSubmitBtn();
                    return;
                }

                // ── Track chunk progress ──────────────────────────────
                const currentChunks = res.completed_chunks || 0;
                if (currentChunks > lastChunksSeen) {
                    lastChunksSeen = currentChunks;
                    lastProgressAt = Date.now();

                    if (isStale) {
                        // Progress resumed — clear the stale banner
                        isStale = false;
                        $('#staleBanner').hide();
                        $('#progressBar').removeClass('is-stale');
                        $('#statusDot').removeClass('stale');
                        setFastPoll(importId, knownChunks);
                    }
                }

                const elapsed = Date.now() - lastProgressAt;

                // ── Soft stale (90 s): warn but keep watching ─────────
                if (!isStale && elapsed > SOFT_STALE_MS) {
                    isStale = true;
                    $('#progressBar').addClass('is-stale');
                    $('#statusDot').addClass('stale');
                    $('#staleBanner').show();
                    $('#staleDetail').text(
                        'No chunk progress for ' + Math.round(elapsed / 1000) +
                        ' s — the import is likely still running in the background.'
                    );
                    // Wire up the resume button
                    $('#resumePollingBtn').off('click').on('click', function () {
                        lastProgressAt = Date.now(); // reset timer
                        isStale = false;
                        $('#staleBanner').hide();
                        $('#progressBar').removeClass('is-stale');
                        $('#statusDot').removeClass('stale');
                        setFastPoll(importId, knownChunks);
                        doPoll(importId, knownChunks);
                    });
                    setSlowPoll(importId, knownChunks);
                }

                // ── Hard stop (5 min): something is really broken ─────
                if (elapsed > HARD_STOP_MS) {
                    clearInterval(pollInterval);
                    onImportFailed('Import timed out after 5 minutes of no progress.');
                    return;
                }

                updateProgressUI(res, knownChunks);

                if (res.done || res.status === 'completed') {
                    clearInterval(pollInterval);
                    onImportComplete(res);
                    return;
                }

                if (res.status === 'failed') {
                    clearInterval(pollInterval);
                    onImportFailed('The import job reported a failure. Check storage/logs/laravel.log.');
                }
            })
            .fail(function () {
                $('#statusDetail').text('Connection issue — retrying...');
            });
    }

    // ─────────────────────────────────────────────
    // UPDATE PROGRESS UI
    // ─────────────────────────────────────────────
    function updateProgressUI(res, knownChunks) {
        const completed = res.completed_chunks || 0;
        const total     = res.total_chunks     || knownChunks;
        const pct       = res.percentage       || 0;
        const processed = res.processed_rows   || 0;
        const totalRows = res.total_rows        || 0;

        $('#progressBar').css('width', pct + '%');
        $('#progressPct').text(pct + '%');

        $('#statusText').text(
            totalRows > 0
                ? 'Processed: ' + processed.toLocaleString() + ' / ' + totalRows.toLocaleString() + ' rows'
                : 'Processing...'
        );
        $('#statusDetail').text('Chunk ' + completed + ' of ' + total + ' completed');

        for (let i = 0; i < completed; i++) {
            $('#chunk-' + i).removeClass('active').addClass('done');
        }
        if (completed < total) {
            $('#chunk-' + completed).addClass('active');
        }
    }

    // ─────────────────────────────────────────────
    // COMPLETE / FAILED
    // ─────────────────────────────────────────────
    function onImportComplete(res) {
        $('#staleBanner').hide();
        $('#progressBar').css('width', '100%').removeClass('is-stale is-failed').addClass('is-done');
        $('#progressPct').text('100%');
        $('#statusText').text('Import completed successfully!');
        $('#statusDetail').text((res.processed_rows || 0).toLocaleString() + ' rows imported.');
        $('#statusDot').removeClass().addClass('status-dot done');
        $('.chunk-cell').removeClass('active').addClass('done');
        $('#submitBtn')
            .html('<span class="tf-icons bx bx-check-circle me-1"></span> Done')
            .addClass('btn-success').removeClass('btn-primary');

        success_msg('Products imported successfully!');

        setTimeout(function () {
            window.location.href = "{{ route('admin.products') }}";
        }, 2000);
    }

    function onImportFailed(msg) {
        msg = msg || 'Import failed. Check storage/logs/laravel.log for details.';
        $('#progressBar').removeClass('is-stale').addClass('is-failed');
        $('#statusText').text('Import failed.');
        $('#statusDot').removeClass().addClass('status-dot failed');
        $('.chunk-cell.active').addClass('failed').removeClass('active');
        resetSubmitBtn();
        resetSyncBtn();
        resetBanburySyncBtn();
        resetLogolineSyncBtn();
        resetFashionBizSyncBtn();
        resetPremiumApparelSyncBtn();
        danger_msg(msg);
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    function resetSubmitBtn() {
        $('#submitBtn')
            .prop('disabled', false)
            .html('<span class="tf-icons bx bx-upload me-1"></span> Upload &amp; Import');
    }

});
</script>
@endsection