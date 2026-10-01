@extends('admin.layout.master')
@section('content')
    <?php
        $data = ['meta_title','meta_keywords','meta_description','disclaimer_sections'];
        $data = getPagemeta($data,$Page->id);
        $sections = json_decode($data['disclaimer_sections'] ?: '[]', true) ?: [];
    ?>
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
                <a href="{{ route('admin.pages') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.page.edit', encrypt($Page['id'])) }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Meta Title') }}</label>
                                <input class="form-control {{ $errors->has('meta_title') ? 'is-invalid' : '' }}"  name="page_content[meta_title]"
                                    type="text" value="{{ old('meta_title', $data['meta_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Meta Keywords') }}</label>
                                <input class="form-control {{ $errors->has('meta_keywords') ? 'is-invalid' : '' }}"  name="page_content[meta_keywords]"
                                    type="text" value="{{ old('meta_keywords', $data['meta_keywords']) }}" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Meta Description') }}</label>
                                <textarea class="form-control {{ $errors->has('meta_description') ? 'is-invalid' : '' }}"  name="page_content[meta_description]" >{{ old('meta_description', $data['meta_description']) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <label class="col-form-label mb-0">{{ translate('Disclaimer Sections') }}</label>
                        <button type="button" id="addSectionBtn" class="btn btn-sm btn-primary">
                            <span class="tf-icons bx bx-plus me-1"></span>{{ translate('Add Section') }}
                        </button>
                    </div>

                    <div id="sectionsWrapper">
                        @foreach($sections as $index => $section)
                            <div class="card mb-3 section-row" data-index="{{ $index }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <div class="form-group mb-3">
                                                <label class="col-form-label">{{ translate('Section Heading') }}</label>
                                                <input class="form-control" name="page_content[disclaimer_sections][{{ $index }}][heading]"
                                                    type="text" value="{{ $section['heading'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="col-form-label">{{ translate('Section Content') }}</label>
                                                <textarea rows="5" class="form-control ckeditor section-content" id="section_content_{{ $index }}"
                                                    name="page_content[disclaimer_sections][{{ $index }}][content]">{{ $section['content'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger removeSectionBtn" title="{{ translate('Remove') }}">
                                            <span class="tf-icons bx bx-trash"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <template id="sectionTemplate">
                        <div class="card mb-3 section-row" data-index="__INDEX__">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-3">
                                        <div class="form-group mb-3">
                                            <label class="col-form-label">{{ translate('Section Heading') }}</label>
                                            <input class="form-control" name="page_content[disclaimer_sections][__INDEX__][heading]" type="text">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="col-form-label">{{ translate('Section Content') }}</label>
                                            <textarea rows="5" class="form-control ckeditor section-content" id="section_content___INDEX__"
                                                name="page_content[disclaimer_sections][__INDEX__][content]"></textarea>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger removeSectionBtn" title="{{ translate('Remove') }}">
                                        <span class="tf-icons bx bx-trash"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="row">
                        <div class="col-md-10"></div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary"><span class="tf-icons bx bx-save me-1"></span>Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var wrapper  = document.getElementById('sectionsWrapper');
            var template = document.getElementById('sectionTemplate');
            var nextIndex = {{ count($sections) }};

            function initCkeditor(textarea) {
                if (window.CKEDITOR && textarea && !CKEDITOR.instances[textarea.id]) {
                    CKEDITOR.replace(textarea, {
                        extraPlugins: 'colorbutton,colordialog',
                        toolbar: [
                            { name: 'colors', items: [ 'TextColor', 'BGColor', 'colordialog' ] },
                        ],
                    });
                }
            }

            document.getElementById('addSectionBtn').addEventListener('click', function () {
                var html = template.innerHTML.replace(/__INDEX__/g, nextIndex);
                var temp = document.createElement('div');
                temp.innerHTML = html.trim();
                var row = temp.firstElementChild;
                wrapper.appendChild(row);
                initCkeditor(row.querySelector('.ckeditor'));
                nextIndex++;
            });

            wrapper.addEventListener('click', function (e) {
                var btn = e.target.closest('.removeSectionBtn');
                if (!btn) return;
                var row = btn.closest('.section-row');
                var textarea = row.querySelector('.ckeditor');
                if (textarea && window.CKEDITOR && CKEDITOR.instances[textarea.id]) {
                    CKEDITOR.instances[textarea.id].destroy(true);
                }
                row.remove();
            });
        })();
    </script>
@endsection
