<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Categories;

class CategoryController extends Controller
{
    // ################## Top Level Category ##############################
    public function topindex(Request $request)
    {
        $common = array();
        $common['title'] = 'Top Categories';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'topcategory';

        $common['filter_topcategory_title']     = "";
        $common['filter_topcategory_status'] = "";

        $filterFields = ['filter_topcategory_title', 'filter_topcategory_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $categories = Categories::whereNull('is_delete')->whereNull('top_level_category_id')->whereNull('mid_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc');

        if($common['filter_topcategory_title']){
            $categories->where('title','like','%'.$common['filter_topcategory_title'].'%');         
        }

        if($common['filter_topcategory_status']){
            $categories->where('status',$common['filter_topcategory_status']);
        }

        $categories  = $categories->paginate(config('adminconfig.category_records_per_page'));

        $categories_list = array();
        foreach ($categories as $key => $value) {
            $categories_list[] = $value;
        }
        return view('admin.category.top.index', compact('common', 'categories','categories_list'));
    }

    public function topstore(Request $request, $id = ""){
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_category = Categories::where('id', $id)->first();
            if (!$get_category) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']   = "required";
            if (isset($request->delivery_after_days)) {
                $req_fields['delivery_after_days']   = "required|int|min:1";
            }
            if (isset($request->sort_number)) {
                $req_fields['sort_number']   = "required|int|min:1";
            }

            $errormsg = [
                "title" => translate("Title"),
                "delivery_after_days" => translate("Delivery after days"),
                "sort_number" => translate("Sort number"),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }
            if ($request->id != "") {
                $message    = translate("Update Successfully");
                $status     = "success";
                $Category = Categories::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Category = new Categories();
            }

            if ($request->hasFile('image')) {
                $image = image_upload($request->file('image'), 'Category');
                $Category->image   = $image;
            }



            $Category->title   = $request->title;
            if ($request->id != "") {
                $Category->slug = createSlug('categories', $request->title, $request->id);
            } else {
                $Category->slug = createSlug('categories', $request->title);
            }
            $Category->description   = $request->description;
            $Category->status   = $request->status;
            $Category->is_menu   = ($request->is_menu) ? $request->is_menu : NULL;
            $Category->sort_number   = ($request->sort_number) ? $request->sort_number : NULL;
            $Category->save();

            $get_category = Categories::where('id', $Category->id)->first();
            $get_category->sort_number   = ($request->sort_number) ? $request->sort_number : $Category->id;
            $get_category->save();
           
            return redirect()->route('admin.topcategories')->withErrors([$status => $message]);
        }

        $common = array();
        $common['title'] = 'Top Category Add';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'topcategory';
        $get_category             = getTableColumn('categories');
        if ($id != "") {          
            $get_category = Categories::where('id', $id)->first();
        }

        return view('admin.category.top.store', compact('common', 'get_category'));
    }

    public function topdelete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_category =  Categories::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_category) {
            $get_category->is_delete = 1;
            $get_category->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }

    // ################## Mid Level Category ##############################
    public function midindex(Request $request)
    {
        $common = array();
        $common['title'] = 'Mid Categories';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'midcategory';

        $common['filter_midcategory_title']     = "";
        $common['filter_midcategory_top_category'] = "";
        $common['filter_midcategory_status'] = "";

        $filterFields = ['filter_midcategory_title','filter_midcategory_top_category','filter_midcategory_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $categories = Categories::whereNull('is_delete')->whereNotNull('top_level_category_id')->whereNull('mid_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc');

        if($common['filter_midcategory_title']){
            $categories->where('title','like','%'.$common['filter_midcategory_title'].'%');         
        }

        if($common['filter_midcategory_top_category']){
            $categories->where('top_level_category_id',$common['filter_midcategory_top_category']);
        }

        if($common['filter_midcategory_status']){
            $categories->where('status',$common['filter_midcategory_status']);
        }

        $categories  = $categories->paginate(config('adminconfig.category_records_per_page'));

        $categories_list = array();
        foreach ($categories as $key => $value) {
            $top_level_category = '-';
            $get_category = Categories::where('id', $value->top_level_category_id)->first();
            if ($get_category) {
                $top_level_category = $get_category->title;
            }
            $value['top_level_category'] = $top_level_category;
            $categories_list[] = $value;
        }

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id')->get();
        return view('admin.category.mid.index', compact('common', 'categories','categories_list','top_categories'));
    }

    public function midstore(Request $request, $id = ""){
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_category = Categories::where('id', $id)->first();
            if (!$get_category) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']   = "required";
            if (isset($request->delivery_after_days)) {
                $req_fields['delivery_after_days']   = "required|int|min:1";
            }
            if (isset($request->sort_number)) {
                $req_fields['sort_number']   = "required|int|min:1";
            }

            $errormsg = [
                "title" => translate("Title"),
                "delivery_after_days" => translate("Delivery after days"),
                "sort_number" => translate("Sort number"),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }
            if ($request->id != "") {
                $message    = translate("Update Successfully");
                $status     = "success";
                $Category = Categories::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Category = new Categories();
            }

            if ($request->hasFile('image')) {
                $image = image_upload($request->file('image'), 'Category');
                $Category->image   = $image;
            }

            $Category->title   = $request->title;
            $slug_text = $this->buildMidCategorySlugText($request->title, $request->top_level_category_id);
            if ($request->id != "") {
                $Category->slug = createSlug('categories', $slug_text, $request->id);
            } else {
                $Category->slug = createSlug('categories', $slug_text);
            }
            $Category->description   = $request->description;
            $Category->top_level_category_id   = ($request->top_level_category_id) ? $request->top_level_category_id : NULL;
            $Category->is_menu   = ($request->is_menu) ? $request->is_menu : NULL;
            $Category->status   = $request->status;
            $Category->sort_number   = ($request->sort_number) ? $request->sort_number : NULL;
            /*$Category->meta_title   = $request->meta_title;
            $Category->meta_keywords   = $request->meta_keywords;
            $Category->meta_description   = $request->meta_description;*/
            $Category->save();

            $get_category = Categories::where('id', $Category->id)->first();
            $get_category->sort_number   = ($request->sort_number) ? $request->sort_number : $Category->id;
            $get_category->save();
           
            return redirect()->route('admin.midcategories')->withErrors([$status => $message]);
        }

        $common = array();
        $common['title'] = 'Mid Category Add';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'midcategory';
        $get_category             = getTableColumn('categories');

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id');
        if ($id != "") {          
            $get_category = Categories::where('id', $id)->first();
            $top_categories->where('id','!=',$id);
        }
        $top_categories  = $top_categories->get();

        return view('admin.category.mid.store', compact('common', 'get_category','top_categories'));
    }

    public function middelete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_category =  Categories::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_category) {
            $get_category->is_delete = 1;
            $get_category->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }

    
    // ################## bottom Level Category ##############################
    public function bottomindex(Request $request)
    {
        $common = array();
        $common['title'] = 'Bottom Categories';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'bottomcategory';

        $common['filter_bottomcategory_title']     = "";
        $common['filter_bottomcategory_top_category'] = "";
        $common['filter_bottomcategory_mid_category'] = "";
        $common['filter_bottomcategory_status'] = "";

        $filterFields = ['filter_bottomcategory_title','filter_bottomcategory_top_category','filter_bottomcategory_mid_category','filter_bottomcategory_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $categories = Categories::whereNull('is_delete')->whereNotNull('top_level_category_id')->whereNotNull('mid_level_category_id')->whereNull('bottom_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc');

        if($common['filter_bottomcategory_title']){
            $categories->where('title','like','%'.$common['filter_bottomcategory_title'].'%');         
        }

        if($common['filter_bottomcategory_top_category']){
            $categories->where('top_level_category_id',$common['filter_bottomcategory_top_category']);
        }
        if($common['filter_bottomcategory_mid_category']){
            $categories->where('mid_level_category_id',$common['filter_bottomcategory_mid_category']);
        }

        if($common['filter_bottomcategory_status']){
            $categories->where('status',$common['filter_bottomcategory_status']);
        }

        $categories  = $categories->paginate(config('adminconfig.category_records_per_page'));

        $categories_list = array();
        foreach ($categories as $key => $value) {
            $top_level_category = '-';
            $mid_level_category = '-';
            $get_category = Categories::where('id', $value->top_level_category_id)->first();
            if ($get_category) {
                $top_level_category = $get_category->title;
            }
            $get_category = Categories::where('id', $value->mid_level_category_id)->first();
            if ($get_category) {
                $mid_level_category = $get_category->title;
            }
            $value['top_level_category'] = $top_level_category;
            $value['mid_level_category'] = $mid_level_category;
            $categories_list[] = $value;
        }

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id')->get();
        return view('admin.category.bottom.index', compact('common', 'categories','categories_list','top_categories'));
    }

    public function bottomstore(Request $request, $id = ""){
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_category = Categories::where('id', $id)->first();
            if (!$get_category) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']   = "required";
            if (isset($request->delivery_after_days)) {
                $req_fields['delivery_after_days']   = "required|int|min:1";
            }
            if (isset($request->sort_number)) {
                $req_fields['sort_number']   = "required|int|min:1";
            }

            $errormsg = [
                "title" => translate("Title"),
                "delivery_after_days" => translate("Delivery after days"),
                "sort_number" => translate("Sort number"),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }
            if ($request->id != "") {
                $message    = translate("Update Successfully");
                $status     = "success";
                $Category = Categories::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Category = new Categories();
            }

            if ($request->hasFile('image')) {
                $image = image_upload($request->file('image'), 'Category');
                $Category->image   = $image;
            }

             

            $Category->title   = $request->title;
 

            /*$top_category_title = '';
            if( $request->top_level_category_id != '' ){
                $get_top_category = Categories::where('id', $request->top_level_category_id)->first();
                $top_category_title = $get_top_category->title.'-' ?? '';
            }

            $mid_category_title = '';
            if( $request->mid_level_category_id != '' ){
                $get_mid_category = Categories::where('id', $request->mid_level_category_id)->first();
                $mid_category_title = $get_mid_category->title.'-' ?? '';
            }

            $slug_text = $top_category_title.''.$mid_category_title.''.$request->title;*/

            $slug_text = $this->buildBottomCategorySlugText($request->title, $request->top_level_category_id, $request->mid_level_category_id);
            if ($request->id != "") {
                $Category->slug = createSlug('categories', $slug_text, $request->id);
            } else {
                $Category->slug = createSlug('categories', $slug_text);
            }
            $Category->description   = $request->description;
            $Category->top_level_category_id   = ($request->top_level_category_id) ? $request->top_level_category_id : NULL;
            $Category->mid_level_category_id   = ($request->mid_level_category_id) ? $request->mid_level_category_id : NULL;
            $Category->bottom_level_category_id = NULL;
            $Category->is_menu   = ($request->is_menu) ? $request->is_menu : NULL;
            $Category->status   = $request->status;
            $Category->sort_number   = ($request->sort_number) ? $request->sort_number : NULL;
            // $Category->meta_title   = $request->meta_title;
            // $Category->meta_keywords   = $request->meta_keywords;
            // $Category->meta_description   = $request->meta_description;
            $Category->save();
           
            $get_category = Categories::where('id', $Category->id)->first();
            $get_category->sort_number   = ($request->sort_number) ? $request->sort_number : $Category->id;
            $get_category->save();
            return redirect()->route('admin.bottomcategories')->withErrors([$status => $message]);
        }

        $common = array();
        $common['title'] = 'Bottom Category Add';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'bottomcategory';
        $get_category             = getTableColumn('categories');

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id');
        if ($id != "") {          
            $get_category = Categories::where('id', $id)->first();
            $top_categories->where('id','!=',$id);
        }
        $top_categories  = $top_categories->get();

        return view('admin.category.bottom.store', compact('common', 'get_category','top_categories'));
    }

    public function bottomdelete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_category =  Categories::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_category) {
            $get_category->is_delete = 1;
            $get_category->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }

    // ################## Fourth Level Category ##############################
    public function subbottomindex(Request $request)
    {
        $common = array();
        $common['title'] = 'Fourth Level Categories';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'subbottomcategory';

        $common['filter_subbottomcategory_title'] = "";
        $common['filter_subbottomcategory_top_category'] = "";
        $common['filter_subbottomcategory_mid_category'] = "";
        $common['filter_subbottomcategory_bottom_category'] = "";
        $common['filter_subbottomcategory_status'] = "";

        $filterFields = [
            'filter_subbottomcategory_title',
            'filter_subbottomcategory_top_category',
            'filter_subbottomcategory_mid_category',
            'filter_subbottomcategory_bottom_category',
            'filter_subbottomcategory_status'
        ];
        $common = updateSessionFilters($request, $filterFields, $common);

        $categories = Categories::whereNull('is_delete')->whereNotNull('bottom_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc');

        if ($common['filter_subbottomcategory_title']) {
            $categories->where('title', 'like', '%' . $common['filter_subbottomcategory_title'] . '%');
        }
        if ($common['filter_subbottomcategory_top_category']) {
            $categories->where('top_level_category_id', $common['filter_subbottomcategory_top_category']);
        }
        if ($common['filter_subbottomcategory_mid_category']) {
            $categories->where('mid_level_category_id', $common['filter_subbottomcategory_mid_category']);
        }
        if ($common['filter_subbottomcategory_bottom_category']) {
            $categories->where('bottom_level_category_id', $common['filter_subbottomcategory_bottom_category']);
        }
        if ($common['filter_subbottomcategory_status']) {
            $categories->where('status', $common['filter_subbottomcategory_status']);
        }

        $categories = $categories->paginate(config('adminconfig.category_records_per_page'));

        $categories_list = array();
        foreach ($categories as $key => $value) {
            $top_level_category = '-';
            $mid_level_category = '-';
            $bottom_level_category = '-';

            $get_category = Categories::where('id', $value->top_level_category_id)->first();
            if ($get_category) {
                $top_level_category = $get_category->title;
            }
            $get_category = Categories::where('id', $value->mid_level_category_id)->first();
            if ($get_category) {
                $mid_level_category = $get_category->title;
            }
            $get_category = Categories::where('id', $value->bottom_level_category_id)->first();
            if ($get_category) {
                $bottom_level_category = $get_category->title;
            }

            $value['top_level_category'] = $top_level_category;
            $value['mid_level_category'] = $mid_level_category;
            $value['bottom_level_category'] = $bottom_level_category;
            $categories_list[] = $value;
        }

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id')->get();
        return view('admin.category.subbottom.index', compact('common', 'categories', 'categories_list', 'top_categories'));
    }

    public function subbottomstore(Request $request, $id = "")
    {
        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_category = Categories::where('id', $id)->first();
            if (!$get_category) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = array();
            $req_fields['title'] = "required";

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                ['required' => 'The :attribute field is required.'],
                ['title' => translate("Title")]
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }

            if ($request->id != "") {
                $message = translate("Update Successfully");
                $status = "success";
                $Category = Categories::find($request->id);
            } else {
                $message = translate("Add Successfully");
                $status = "success";
                $Category = new Categories();
            }

            if ($request->hasFile('image')) {
                $image = image_upload($request->file('image'), 'Category');
                $Category->image = $image;
            }

            $Category->title = $request->title;
            $slug_text = $this->buildFourthCategorySlugText($request->title, $request->top_level_category_id, $request->mid_level_category_id, $request->bottom_level_category_id);
            if ($request->id != "") {
                $Category->slug = createSlug('categories', $slug_text, $request->id);
            } else {
                $Category->slug = createSlug('categories', $slug_text);
            }
            $Category->description = $request->description;
            $Category->top_level_category_id = ($request->top_level_category_id) ? $request->top_level_category_id : NULL;
            $Category->mid_level_category_id = ($request->mid_level_category_id) ? $request->mid_level_category_id : NULL;
            $Category->bottom_level_category_id = ($request->bottom_level_category_id) ? $request->bottom_level_category_id : NULL;
            $Category->is_menu = ($request->is_menu) ? $request->is_menu : NULL;
            $Category->status = $request->status;
            $Category->sort_number = ($request->sort_number) ? $request->sort_number : NULL;
            $Category->save();

            $get_category = Categories::where('id', $Category->id)->first();
            $get_category->sort_number = ($request->sort_number) ? $request->sort_number : $Category->id;
            $get_category->save();

            return redirect()->route('admin.subbottomcategories')->withErrors([$status => $message]);
        }

        $common = array();
        $common['title'] = 'Fourth Level Category Add';
        $common['main_menu'] = 'categories';
        $common['submain_menu'] = 'subbottomcategory';
        $get_category = getTableColumn('categories');

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id');
        if ($id != "") {
            $get_category = Categories::where('id', $id)->first();
            $top_categories->where('id', '!=', $id);
        }
        $top_categories = $top_categories->get();

        return view('admin.category.subbottom.store', compact('common', 'get_category', 'top_categories'));
    }

    public function subbottomdelete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $get_category = Categories::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_category) {
            $get_category->is_delete = 1;
            $get_category->save();
        }

        return back()->withErrors(['success' => translate('Delete Successfully')]);
    }


    public function store(Request $request, $id = ""){
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_category = Categories::where('id', $id)->first();
            if (!$get_category) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']   = "required";
            if (isset($request->delivery_after_days)) {
                $req_fields['delivery_after_days']   = "required|int|min:1";
            }
            if (isset($request->sort_number)) {
                $req_fields['sort_number']   = "required|int|min:1";
            }

            $errormsg = [
                "title" => translate("Title"),
                "delivery_after_days" => translate("Delivery after days"),
                "sort_number" => translate("Sort number"),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                $validation->errors()->add('error', 'Something is wrong with required field!');
                return back()->withErrors($validation)->withInput();
            }
            if ($request->id != "") {
                $message    = translate("Update Successfully");
                $status     = "success";
                $Category = Categories::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Category = new Categories();
            }

            if ($request->hasFile('image')) {
                $image = image_upload($request->file('image'), 'Category');
                $Category->image   = $image;
            }

            $Category->title   = $request->title;
            if ($request->id != "") {
                $Category->slug = createSlug('categories', $request->title, $request->id);
            } else {
                $Category->slug = createSlug('categories', $request->title);
            }
            $Category->description   = $request->description;
            $Category->top_level_category_id   = ($request->top_level_category_id) ? $request->top_level_category_id : NULL;
            $Category->mid_level_category_id   = ($request->mid_level_category_id) ? $request->mid_level_category_id : NULL;
            $Category->is_menu   = ($request->is_menu) ? $request->is_menu : NULL;
            $Category->status   = $request->status;
            $Category->sort_number   = ($request->sort_number) ? $request->sort_number : NULL;
            // $Category->meta_title   = $request->meta_title;
            // $Category->meta_keywords   = $request->meta_keywords;
            // $Category->meta_description   = $request->meta_description;
            $Category->save();
           
            return redirect()->route('admin.categories')->withErrors([$status => $message]);
        }

        $common = array();
        $common['title'] = 'Category Add';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'topcategory';
        $get_category             = getTableColumn('categories');

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id');
        $mid_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('mid_level_category_id');
        if ($id != "") {          
            $get_category = Categories::where('id', $id)->first();
            $top_categories->where('id','!=',$id);
            $mid_categories->where('id','!=',$id);
        }

        $top_categories  = $top_categories->get();
        $mid_categories  = $mid_categories->get();
        return view('admin.category.top.store', compact('common', 'get_category','top_categories','mid_categories'));
    }


    // Delete Category
    public function delete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_category =  Categories::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_category) {
            $get_category->is_delete = 1;
            $get_category->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
    

    public function get_mid_level_category_id(Request $request){
        $html = '<option value="">'.$request->title.'</option>';
        if ($request->isMethod('post')) {
            //$get_category =  Categories::where(['top_level_category_id' => $request->top_level_category_id])->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            
            $getcategory =  Categories::where(['id' => $request->top_level_category_id])->whereNull('is_delete')->first();
            if (!$getcategory) {
                $get_category = collect();
            } elseif ($getcategory->bottom_level_category_id) {
                $get_category = collect();
            } elseif ($getcategory->mid_level_category_id) {
                $get_category = Categories::where(['bottom_level_category_id' => $request->top_level_category_id])->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            } elseif ($getcategory->top_level_category_id) {
                $get_category =  Categories::where(['mid_level_category_id' => $request->top_level_category_id])->whereNull('bottom_level_category_id')->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            }else{
                $get_category =  Categories::where(['top_level_category_id' => $request->top_level_category_id])->whereNull('mid_level_category_id')->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            }

            foreach ($get_category as $key => $value) {
                $html .='<option value="'.$value->id.'" '.getSelected($value->id, $request->child).' >'.$value->title.'</option>';
            }
        }
        echo $html;
    }

    private function buildMidCategorySlugText($title, $topCategoryId)
    {
        $slug_parts = [$title];
        $top_category = Categories::where('id', $topCategoryId)->first();
        if ($top_category) {
            $slug_parts[] = $top_category->slug ?: $top_category->title;
        }

        return implode('-', array_filter($slug_parts));
    }

    private function buildBottomCategorySlugText($title, $topCategoryId, $midCategoryId)
    {
        $slug_parts = [$title];
        $mid_category = Categories::where('id', $midCategoryId)->first();
        if ($mid_category) {
            $slug_parts[] = $this->buildMidCategorySlugText($mid_category->title, $topCategoryId ?: $mid_category->top_level_category_id);
        }

        return implode('-', array_filter($slug_parts));
    }

    private function buildFourthCategorySlugText($title, $topCategoryId, $midCategoryId, $bottomCategoryId)
    {
        $slug_parts = [$title];
        $bottom_category = Categories::where('id', $bottomCategoryId)->first();
        if ($bottom_category) {
            $slug_parts[] = $this->buildBottomCategorySlugText($bottom_category->title, $topCategoryId ?: $bottom_category->top_level_category_id, $midCategoryId ?: $bottom_category->mid_level_category_id);
        }

        return implode('-', array_filter($slug_parts));
    }
}
