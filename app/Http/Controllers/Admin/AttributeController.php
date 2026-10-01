<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Attributes;

class AttributeController extends Controller
{

    // Attribute List
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Attributes';
        $common['main_menu']    = 'catalogs';
        $common['submain_menu'] = 'attribute';
        $common['filter_attribute_title']     = "";
        $common['filter_attribute_status'] = "";

        $filterFields = ['filter_attribute_title', 'filter_attribute_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $Attributes = Attributes::whereNull('is_delete')->orderBy('id', 'desc');

        if($common['filter_attribute_title']){
            $Attributes->where('title','like','%'.$common['filter_attribute_title'].'%');         
        }

        if($common['filter_attribute_status']){
            $Attributes->where('status',$common['filter_attribute_status']);
        }

        $Attributes  = $Attributes->paginate(config('adminconfig.records_per_page'));



        return view('admin.attribute.index', compact('common', 'Attributes'));
    }

    // Add Or Update Attributes
    public function store(Request $request, $id = ""){
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_attribute = Attributes::where('id', $id)->first();
            if (!$get_attribute) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']   = "required";

            $errormsg = [
                "title" => translate("Title")
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
                $Attribute = Attributes::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Attribute = new Attributes();
            }
            $Attribute->title   = $request->title;
            $Attribute->status   = $request->status;
            $Attribute->save();
            return redirect()->route('admin.attributes')->withErrors([$status => $message]);
        }

        $common = array();
        $common['title'] = 'Attribute Add';
        $common['main_menu']    = 'catalogs';
        $common['submain_menu'] = 'attribute';
        $get_attribute  = getTableColumn('attributes');

        if ($id != "") {          
            $get_attribute = Attributes::where('id', $id)->first();
        }
        return view('admin.attribute.store', compact('common', 'get_attribute'));
    }

    // Delete Weight
    public function delete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);

        $status       = 'error';
        $message      = translate('Something went wrong!');

        

        $get_attribute =  Attributes::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_attribute) {
            $get_attribute->is_delete = 1;
            $get_attribute->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
}
