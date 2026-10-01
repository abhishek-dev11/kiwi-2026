@extends('admin.layout.master')
@section('content')
    <?php
        $data = [
            'meta_title','meta_keywords','meta_description',
            'hero_title','hero_subtitle',
            'contact_title','contact_text','contact_phone','chat_text',
            'info_title','info_address','info_phone','info_email',
            'contact_image',
            'form_title','form_text',
            'map_embed_url',
        ];
        $data = getPagemeta($data,$Page->id);

        $imageField = function($key) use ($data) {
            return $data[$key] != '' ? get_image_upload('pages/' . $data[$key]) : asset('placeholder/placeholder.png');
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
                                <input class="form-control" name="page_content[meta_title]" type="text" value="{{ old('meta_title', $data['meta_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Meta Keywords') }}</label>
                                <input class="form-control" name="page_content[meta_keywords]" type="text" value="{{ old('meta_keywords', $data['meta_keywords']) }}" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Meta Description') }}</label>
                                <textarea class="form-control" name="page_content[meta_description]" >{{ old('meta_description', $data['meta_description']) }}</textarea>
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
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Contact Us Block') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}</label>
                                <input class="form-control" name="page_content[contact_title]" type="text" value="{{ old('contact_title', $data['contact_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Text') }}</label>
                                <input class="form-control" name="page_content[contact_text]" type="text" value="{{ old('contact_text', $data['contact_text']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Customer Service Phone') }}</label>
                                <input class="form-control" name="page_content[contact_phone]" type="text" value="{{ old('contact_phone', $data['contact_phone']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Chat Link Text') }}</label>
                                <input class="form-control" name="page_content[chat_text]" type="text" value="{{ old('chat_text', $data['chat_text']) }}" >
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Contact Information Block') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}</label>
                                <input class="form-control" name="page_content[info_title]" type="text" value="{{ old('info_title', $data['info_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Address') }}</label>
                                <input class="form-control" name="page_content[info_address]" type="text" value="{{ old('info_address', $data['info_address']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Phone') }}</label>
                                <input class="form-control" name="page_content[info_phone]" type="text" value="{{ old('info_phone', $data['info_phone']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Email (also used as admin notification recipient)') }}</label>
                                <input class="form-control" name="page_content[info_email]" type="text" value="{{ old('info_email', $data['info_email']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Bottom Image') }}</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_contact_image')" name="page_content[contact_image]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <img class="img-css" src="{{ $imageField('contact_image') }}" id="preview_contact_image">
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Contact Form Block') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}</label>
                                <input class="form-control" name="page_content[form_title]" type="text" value="{{ old('form_title', $data['form_title']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Text') }}</label>
                                <input class="form-control" name="page_content[form_text]" type="text" value="{{ old('form_text', $data['form_text']) }}" >
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5>{{ translate('Map') }}</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Google Maps Embed URL') }}</label>
                                <textarea rows="2" class="form-control" name="page_content[map_embed_url]">{{ old('map_embed_url', $data['map_embed_url']) }}</textarea>
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
