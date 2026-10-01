<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Suppliers;

class SuppliersController extends Controller
{

    // Suppliers List
    public function index(Request $request)
    {  
        $common = array();
        $common['title']        = 'Suppliers';
        $common['main_menu']    = 'suppliers';
        $common['submain_menu'] = 'suppliers';
        $common['filter_supplier_name']   = "";
        $common['filter_supplier_email']  = "";
        $common['filter_supplier_status'] = "";
        $common['filter_supplier_type']   = "";

        $getSuppliers = Suppliers::where(ConditionQuery())->orderBy('id', 'desc');

        $filterFields = ['filter_supplier_name', 'filter_supplier_email', 'filter_supplier_status', 'filter_supplier_type'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        if($common['filter_supplier_name']){
            $getSuppliers->where('full_name','like','%'.$common['filter_supplier_name'].'%');
        }



        if($common['filter_supplier_email']){
            //$getSuppliers->where('email',$common['filter_supplier_email']);
            $getSuppliers->where('email','like', '%' . $common['filter_supplier_email'] . '%');
        }

        if($common['filter_supplier_type']){
            $getSuppliers->where('type', $common['filter_supplier_type']);
        }

        $getSuppliers = $getSuppliers->paginate(config('adminconfig.records_per_page'));
        
        return view('admin.supplier.index', compact('common', 'getSuppliers'));
    }

    // Add Or Update Supplier
    public function store(Request $request, $id = ""){

        $common = array();
        $common['title']        = 'Supplier Add';
        $common['main_menu']    = 'suppliers';
        $common['submain_menu'] = 'suppliers';

        $get_supplier   = getTableColumn('suppliers');

        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_supplier = Suppliers::where('id', $id)->first();
            
            if (!$get_supplier) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['full_name']   = "required";
            if ($request->id == "") {
                $req_fields['email']    = 'required|email|unique:suppliers,email';
            }
            $req_fields['phone']        = "required";
          
            

            //$errormsg['regular_price'] = translate("Regular Price");
            $errormsg = array();
            $errormsg['full_name'] = translate("First Name");
            if ($request->id == "") {
                $errormsg['email'] = translate("Email");
            }
            $errormsg['phone'] = translate("Phone");
            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                return response()->json(['error' => $validation->getMessageBag()->toArray() ]);
            }

            if ($request->id != "") {
                $message    = translate("Update Successfully");
                $status     = "success";
                $Supplier = Suppliers::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Supplier = new Suppliers();
                $Supplier->email        = $request->email;
            }
            $Supplier->full_name       = $request->full_name;
            $Supplier->phone            = ($request->phone) ? $request->phone : NULL;
            $Supplier->type           = $request->type;
            $Supplier->status           = $request->status;
            $Supplier->save();
            return response()->json([$status => $message,'error'=>false, 'inputs' => $request->all()]);     
        }

        return view('admin.supplier.store', compact('common', 'get_supplier'));
    }

    // Delete Product
    public function delete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_supplier =  Suppliers::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_supplier) {
            $get_supplier->is_delete = 1;
            $get_supplier->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }
    
}