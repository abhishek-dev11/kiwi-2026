@extends('admin.layout.master')
@section('content')
    <?php 
        $data = ['meta_title','meta_keywords','meta_description','header_logo','favicon','free_fast_shipping','late_night_delivery','satisfaction_guarantee','secure_checkout','dedicated_help_center','footer_top_logo','footer_top_text','footer_heading_1','footer_title_2','footer_title_3','footer_title_4','footer_address_text','footer_address_4','footer_email_4_text','footer_email_4','footer_phone_4_text','footer_phone_4','footer_copyright_text','facebook_link','instagram_link','twitter_link','linkedin_link','search_categories','product_detail_banner','refferal_minimum_order_amount','refferal_by_user_amount','reffered_to_user_amount','maximum_share_refferal'];
        $data = getPagemeta($data,$Page->id); 

        $search_categories = array();
        if (!empty($data['search_categories'])) {
            $search_categories = json_decode($data['search_categories'],true);
        }
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
                                <label class="col-form-label">Header Logo</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_header_logo')" name="page_content[header_logo]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <?php if ($data['header_logo']!='') { ?>
                                    <img class="img-css" src="{{ get_image_upload('pages/'.$data['header_logo']) }}" id="preview_header_logo">
                                    <?php }else{ ?>
                                    <img class="img-css" src="{{ asset('placeholder/placeholder.png') }}" id="preview_header_logo">
                                    <?php } ?>
                                </a>
                            </div>
                        </div> 
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">Favicon</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_favicon')" name="page_content[favicon]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <?php if ($data['favicon']!='') { ?>
                                    <img class="img-css" src="{{ get_image_upload('pages/'.$data['favicon']) }}" id="preview_favicon">
                                    <?php }else{ ?>
                                    <img class="img-css" src="{{ asset('placeholder/placeholder.png') }}" id="preview_favicon">
                                    <?php } ?>
                                </a>
                            </div>
                        </div>   
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Search Categories') }} </label>
                                 <?php 
                                    if (count($search_categories)) {
                                        $search_categories = array_reverse($search_categories);
                                        $Get_categories = collect($categories)
                                            ->sortBy(fn($item) => array_search($item['id'], $search_categories) !== false 
                                                ? array_search($item['id'], $search_categories) 
                                                : count($search_categories)
                                            )
                                            ->values();
                                    }else{
                                        $Get_categories = $categories;
                                    }
                                ?>
                                <select class="form-select single-select_order search_categories" name="page_content[search_categories][]" id="search_categories" multiple=multiple>
                                    <option value="">{{ ('Select Products') }}</option>
                                    @foreach ($Get_categories as $val)
                                        <option value="{{ $val['id'] }}"  @if(in_array($val['id'],$search_categories)) selected @endif
                                        > {{ $val['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row"><div class="col-md-12"><hr></div></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">Footer Top Logo</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_footer_top_logo')" name="page_content[footer_top_logo]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <?php if ($data['footer_top_logo']!='') { ?>
                                    <img class="img-css" src="{{ get_image_upload('pages/'.$data['footer_top_logo']) }}"  id="preview_footer_top_logo">
                                    <?php }else{ ?>
                                    <img class="img-css" src="{{ asset('placeholder/placeholder.png') }}" id="preview_footer_top_logo">
                                    <?php } ?>
                                </a>
                            </div>
                        </div>   
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Top Text') }}</label>
                                <input class="form-control {{ $errors->has('footer_top_text') ? 'is-invalid' : '' }}"  name="page_content[footer_top_text]"
                                    type="text" value="{{ old('footer_top_text', $data['footer_top_text']) }}" >
                            </div>
                        </div>  
                    </div>

                    <div class="row"><div class="col-md-12"><hr></div></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Heading 1') }}</label>
                                <input class="form-control {{ $errors->has('footer_heading_1') ? 'is-invalid' : '' }}"  name="page_content[footer_heading_1]"
                                    type="text" value="{{ old('footer_heading_1', $data['footer_heading_1']) }}">
                            </div>
                        </div>                                             
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Title 2') }}</label>
                                <input class="form-control {{ $errors->has('footer_title_2') ? 'is-invalid' : '' }}"  name="page_content[footer_title_2]"
                                    type="text" value="{{ old('footer_title_2', $data['footer_title_2']) }}" >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Title 3') }}</label>
                                <input class="form-control {{ $errors->has('footer_title_3') ? 'is-invalid' : '' }}"  name="page_content[footer_title_3]"
                                    type="text" value="{{ old('footer_title_3', $data['footer_title_3']) }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Title 4') }}</label>
                                <input class="form-control {{ $errors->has('footer_title_4') ? 'is-invalid' : '' }}"  name="page_content[footer_title_4]"
                                    type="text" value="{{ old('footer_title_4', $data['footer_title_4']) }}" >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Phone Number text') }}</label>
                                <input class="form-control {{ $errors->has('footer_phone_4_text') ? 'is-invalid' : '' }}"  name="page_content[footer_phone_4_text]"
                                    type="text" value="{{ old('footer_phone_4_text', $data['footer_phone_4_text']) }}" >
                            </div>
                        </div>  
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Phone Number') }}</label>
                                <input class="form-control {{ $errors->has('footer_phone_4') ? 'is-invalid' : '' }}"  name="page_content[footer_phone_4]"
                                    type="text" value="{{ old('footer_phone_4', $data['footer_phone_4']) }}" >
                            </div>
                        </div>  
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Email address text') }}</label>
                                <input class="form-control {{ $errors->has('footer_email_4_text') ? 'is-invalid' : '' }}"  name="page_content[footer_email_4_text]"
                                    type="text" value="{{ old('footer_email_4_text', $data['footer_email_4_text']) }}" >
                            </div>
                        </div> 
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Email address') }}</label>
                                <input class="form-control {{ $errors->has('footer_email_4') ? 'is-invalid' : '' }}"  name="page_content[footer_email_4]"
                                    type="text" value="{{ old('footer_email_4', $data['footer_email_4']) }}" >
                            </div>
                        </div> 
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Address Text') }}</label>
                                <input class="form-control {{ $errors->has('footer_address_text') ? 'is-invalid' : '' }}"  name="page_content[footer_address_text]"
                                    type="text" value="{{ old('footer_address_text', $data['footer_address_text']) }}" >
                            </div>
                        </div> 
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Address') }}</label>
                                <input class="form-control {{ $errors->has('footer_address_4') ? 'is-invalid' : '' }}"  name="page_content[footer_address_4]"
                                    type="text" value="{{ old('footer_address_4', $data['footer_address_4']) }}" >
                            </div>
                        </div>                         
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Footer Copyright text') }}</label>
                                <input class="form-control {{ $errors->has('footer_copyright_text') ? 'is-invalid' : '' }}"  name="page_content[footer_copyright_text]"
                                    type="text" value="{{ old('footer_copyright_text', $data['footer_copyright_text']) }}" >
                            </div>
                        </div>  
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Facebook Link') }}</label>
                                <input class="form-control {{ $errors->has('facebook_link') ? 'is-invalid' : '' }}"  name="page_content[facebook_link]"
                                    type="text" value="{{ old('facebook_link', $data['facebook_link']) }}" >
                            </div>
                        </div> 
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Twitter Link') }}</label>
                                <input class="form-control {{ $errors->has('twitter_link') ? 'is-invalid' : '' }}"  name="page_content[twitter_link]"
                                    type="text" value="{{ old('twitter_link', $data['twitter_link']) }}" >
                            </div>
                        </div>  
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Linkedin Link') }}</label>
                                <input class="form-control {{ $errors->has('linkedin_link') ? 'is-invalid' : '' }}"  name="page_content[linkedin_link]"
                                    type="text" value="{{ old('linkedin_link', $data['linkedin_link']) }}" >
                            </div>
                        </div>  
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Instagram Link') }}</label>
                                <input class="form-control {{ $errors->has('instagram_link') ? 'is-invalid' : '' }}"  name="page_content[instagram_link]"
                                    type="text" value="{{ old('instagram_link', $data['instagram_link']) }}" >
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