@extends('admin.layout.master')
@section('content')
    <?php 
        $data = ['meta_title','meta_keywords','meta_description','popular_products_text_1','explore_categories','our_featured_products','cakes_gifts_categories_banner'];
        $data = getPagemeta($data,$Page->id);

         
        $our_featured_products = array();
        if (!empty($data['our_featured_products'])) {
            $our_featured_products = json_decode($data['our_featured_products'],true);
        }
          

        $explore_categories = array();
        if (!empty($data['explore_categories'])) {
            $explore_categories = json_decode($data['explore_categories'],true);
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
                                <label class="col-form-label">{{ translate('Explore Categories') }} </label>
                                <?php 
                                    if (count($explore_categories)) {
                                        //$explore_categories = array_reverse($explore_categories);
                                        $Get_categories = collect($categories)
                                            ->sortBy(fn($item) => array_search($item['id'], $explore_categories) !== false 
                                                ? array_search($item['id'], $explore_categories) 
                                                : count($explore_categories)
                                            )
                                            ->values();
                                    }else{
                                        $Get_categories = $categories;
                                    }
                                ?>
                                <select class="form-select single-select  explore_categories" name="page_content[explore_categories][]" id="explore_categories" multiple=multiple>
                                    <option value="">{{ ('Select Categories') }}</option>
                                    @foreach ($Get_categories as $val)
                                        <option value="{{ $val['id'] }}"  @if(in_array($val['id'],$explore_categories)) selected @endif
                                        > {{ $val['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Our Featured Products') }} </label>
                                <select class="form-select single-select our_featured_products" name="page_content[our_featured_products][]" id="our_featured_products" multiple=multiple>
                                    <option value="">{{ ('Select Products') }}</option>
                                    @foreach ($Products as $val)
                                        <option value="{{ $val['id'] }}"  @if(in_array($val['id'],$our_featured_products)) selected @endif
                                        > {{ $val['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                     
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Popular Text') }}</label>
                                <input class="form-control {{ $errors->has('popular_products_text_1') ? 'is-invalid' : '' }}"  name="page_content[popular_products_text_1]"
                                    type="text" value="{{ old('popular_products_text_1', $data['popular_products_text_1']) }}" >
                            </div>
                        </div>
                         
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">Cakes & Gifts Categories Banner</label>
                                <input type="file" class="form-control" onchange="loadFile(event,'preview_cakes_gifts_categories_banner')" name="page_content[cakes_gifts_categories_banner]">
                            </div>
                            <div class="media-left">
                                <a href="#" class="profile-image">
                                    <?php if ($data['cakes_gifts_categories_banner']!='') { ?>
                                    <img class="img-css" src="{{ get_image_upload($data['cakes_gifts_categories_banner']) }}" id="preview_cakes_gifts_categories_banner">
                                    <?php }else{ ?>
                                    <img class="img-css" src="{{ asset('placeholder/placeholder.png') }}" id="preview_cakes_gifts_categories_banner">
                                    <?php } ?>
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
    </div>
@endsection