@extends('admin.layout.master')
@section('content')
    <?php 
        $data = ['meta_title','meta_keywords','meta_description','privacypolicy_title','privacypolicy_text'];
        $data = getPagemeta($data,$Page->id);
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
                                <label class="col-form-label">{{ translate('Meta Keywords') }}</label>
                                <textarea class="form-control {{ $errors->has('meta_description') ? 'is-invalid' : '' }}"  name="page_content[meta_description]" >{{ old('meta_description', $data['meta_description']) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">                        
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Privacy Policy Title') }}</label>
                                <input class="form-control {{ $errors->has('privacypolicy_title') ? 'is-invalid' : '' }}"  name="page_content[privacypolicy_title]"
                                    type="text" value="{{ old('privacypolicy_title', $data['privacypolicy_title']) }}" >
                            </div>
                        </div>     
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Privacy Policy Text') }} </label>
                                <textarea rows="5" class="form-control ckeditor" name="page_content[privacypolicy_text]" >{{ $data['privacypolicy_text'] }}</textarea>
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
    </div>
@endsection