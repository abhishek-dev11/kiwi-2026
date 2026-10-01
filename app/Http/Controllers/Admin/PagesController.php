<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Pages;
use App\Models\Pagemeta;
use App\Models\Categories;
use App\Models\Products;
use Illuminate\Support\Str;
class PagesController extends Controller{

    public function index(Request $request){

        $common = array();
        $common['title'] = 'Pages';
        $common['main_menu']    = 'content';
        $common['submain_menu'] = 'pages';

        $Pages = Pages::orderBy('id', 'asc')->paginate(25);   
        return view('admin.pages.index', compact('common','Pages'));
    }

    // Add Pages
    public function add_pages(Request $request, $id = ""){

        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => "No Record Found"]);
            }
            $id = checkDecrypt($id);
            $Page = Pages::where('id', $id)->first();
            if (!$Page) {
                return back()->withErrors(["error" => "Something went wrong"]);
            }
        }else{
            return back()->withErrors(["error" => "Something went wrong"]);
        }

        if ($request->isMethod('post')) {

            $message      = translate("Update Successfully");
            $status       = "success";

            if (isset($request->page_content)) {
                foreach ($request->page_content as $meta_key => $meta_value) {
                    $Pagemeta= Pagemeta::where(['page_id'=>$Page->id])->where('meta_key',$meta_key)->first();
                    
                    if (is_array($meta_value)){
                        if ($Pagemeta) {
                        }else{
                            $Pagemeta = new Pagemeta();
                            $Pagemeta->page_id = $Page->id;
                            $Pagemeta->meta_key = $meta_key;
                        }
                        $Pagemeta->meta_value = json_encode($meta_value);
                    }else{
                        if ($Pagemeta) {
                        }else{
                            $Pagemeta = new Pagemeta();
                            $Pagemeta->page_id = $Page->id;
                            $Pagemeta->meta_key = $meta_key;
                        }

                        if ($request->hasFile('page_content.'.$meta_key)) {
                            $img        = $request->file('page_content.'.$meta_key);
                            $meta_value = image_upload($img,'pages',@$Pagemeta->meta_value);
                            $Pagemeta->type = 'File';  
                        }
                        $Pagemeta->meta_value = $meta_value;
                    }
                    $Pagemeta->save();
                }
            }
            return back()->withErrors([$status => $message]);
        }

        $Products     = Products::where(ConditionQuery())->orderBy('id', 'desc')->get();
        $categories = Categories::whereNull('is_delete')->where('status', 'Active')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();

        $bottom_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNotNull('top_level_category_id')->whereNotNull('mid_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();


        $common = array();
        $common['title'] = "Edit Page - ".$Page->title;
        $common['main_menu']    = 'content';
        $common['submain_menu'] = 'pages';    
        return view('admin.pages.page_'.$Page->slug, compact('common','Page','top_categories','bottom_categories','Products','categories'));
    }

}
