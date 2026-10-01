@extends('admin.layout.master')
@section('content')
    <?php
        $data = [
            'meta_title','meta_keywords','meta_description',
            'hero_title','hero_subtitle','hero_text',
            'company_title','company_heading','company_text','company_image_1','company_image_2',
            'whocan_heading','whocan_text','whocan_image',
            'brands_title','brand_image_1','brand_image_2','brand_image_3','brand_image_4','brand_image_5','brand_image_6','brand_image_7',
            'mission_heading','mission_text','mission_image_1','mission_image_2',
        ];
        $data = getPagemeta($data,$Page->id);

        $imageField = function($key) use ($data) {
            return $data[$key] != '' ? get_image_upload('pages/'.$data[$key]) : asset('placeholder/placeholder.png');
        };
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
                    <h5>{{ translate('Hero Section') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Hero Title') }}</label>
                                <input class="form-control" name="page_content[hero_title]" type="text" value="{{ old('hero_title', $data['hero_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Hero Subtitle') }}</label>
                                <input class="form-control" name="page_content[hero_subtitle]" type="text" value="{{ old('hero_subtitle', $data['hero_subtitle']) }}" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Hero Text') }}</label>
                                <textarea rows="3" class="form-control ckeditor" name="page_content[hero_text]">{{ $data['hero_text'] }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Our Company Focus Section') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Title') }}</label>
                                <input class="form-control" name="page_content[company_title]" type="text" value="{{ old('company_title', $data['company_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Heading') }}</label>
                                <input class="form-control" name="page_content[company_heading]" type="text" value="{{ old('company_heading', $data['company_heading']) }}" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Content') }}</label>
                                <textarea rows="5" class="form-control ckeditor" name="page_content[company_text]">{{ $data['company_text'] }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Image 1') }}</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_company_image_1')" name="page_content[company_image_1]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <img class="img-css" src="{{ $imageField('company_image_1') }}" id="preview_company_image_1">
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Image 2') }}</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_company_image_2')" name="page_content[company_image_2]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <img class="img-css" src="{{ $imageField('company_image_2') }}" id="preview_company_image_2">
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Who Can Order Section') }}</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Heading') }}</label>
                                <input class="form-control" name="page_content[whocan_heading]" type="text" value="{{ old('whocan_heading', $data['whocan_heading']) }}" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Content') }}</label>
                                <textarea rows="5" class="form-control ckeditor" name="page_content[whocan_text]">{{ $data['whocan_text'] }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Image') }}</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_whocan_image')" name="page_content[whocan_image]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <img class="img-css" src="{{ $imageField('whocan_image') }}" id="preview_whocan_image">
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Trending Brands Section') }}</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Title') }}</label>
                                <input class="form-control" name="page_content[brands_title]" type="text" value="{{ old('brands_title', $data['brands_title']) }}" >
                            </div>
                        </div>
                        @for ($i = 1; $i <= 7; $i++)
                            <div class="col-md-3">
                                <div class="form-group mb-3 ">
                                    <label class="col-form-label">{{ translate('Brand Logo') }} {{ $i }}</label>
                                    <input type="file" class="form-control" onchange="loadFile(event,'preview_brand_image_{{ $i }}')" name="page_content[brand_image_{{ $i }}]">
                                </div>
                                <div class="media-left">
                                    <a href="#" class="profile-image">
                                        <img class="img-css" src="{{ $imageField('brand_image_'.$i) }}" id="preview_brand_image_{{ $i }}">
                                    </a>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Our Mission Section') }}</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Heading') }}</label>
                                <input class="form-control" name="page_content[mission_heading]" type="text" value="{{ old('mission_heading', $data['mission_heading']) }}" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Section Content') }}</label>
                                <textarea rows="5" class="form-control ckeditor" name="page_content[mission_text]">{{ $data['mission_text'] }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Image 1') }}</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_mission_image_1')" name="page_content[mission_image_1]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <img class="img-css" src="{{ $imageField('mission_image_1') }}" id="preview_mission_image_1">
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Image 2') }}</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_mission_image_2')" name="page_content[mission_image_2]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <img class="img-css" src="{{ $imageField('mission_image_2') }}" id="preview_mission_image_2">
                                </a>
                            </div>
                        </div>
                    </div>

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
@endsection
