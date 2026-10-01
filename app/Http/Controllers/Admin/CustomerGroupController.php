<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\CustomerGroups;


class CustomerGroupController extends Controller
{

    // Customer Group List
    public function index(Request $request)
    {  
        $common = array();
        $common['title']        = 'CustomerGroups';
        $common['main_menu']    = 'customers_list';
        $common['submain_menu'] = 'customergroups';
        $common['filter_customergroup_name']   = "";
        $common['filter_customergroup_status'] = "";

        $getCustomerGroups = CustomerGroups::whereNull('is_delete')->orderBy('id', 'desc');

        $filterFields = ['filter_customergroup_name', 'filter_customergroup_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);


        if ($common['filter_customergroup_name']) {
            $getCustomerGroups->where('group_name', 'like', '%' . $common['filter_customergroup_name'] . '%');
        }

        if($common['filter_customergroup_status']){
            $getCustomerGroups->where('status',$common['filter_customergroup_status']);
        }

        $getCustomerGroups = $getCustomerGroups->paginate(config('adminconfig.records_per_page'));
        
        return view('admin.customergroups.index', compact('common', 'getCustomerGroups'));
    }

    // Add Or Update Customer Group
    public function store(Request $request, $id = ""){

        $common = array();
        $common['title']        = 'Customer Group Add';
        $common['main_menu']    = 'customers_list';
        $common['submain_menu'] = 'customergroups';

        $get_customer_group   = getTableColumn('customer_groups');
        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_customer_group = CustomerGroups::where('id', $id)->first();
            
            if (!$get_customer_group) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['group_name']   = "required";
            $req_fields['discount_percentage']    = "required";
            

            //$errormsg['regular_price'] = translate("Regular Price");
            $errormsg = array();
            $errormsg['group_name'] = translate("Group Name");
            $errormsg['discount_percentage'] = translate("Discount Percentage");
           
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
                $CustomerGroup = CustomerGroups::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $CustomerGroup = new CustomerGroups();
            }
            
            $CustomerGroup->group_name          = $request->group_name;
            $CustomerGroup->discount_percentage = $request->discount_percentage;
            $CustomerGroup->status              = $request->status;
            $CustomerGroup->save();
            return response()->json([$status => $message,'error'=>false, 'inputs' => $request->all()]);     
        }

        return view('admin.customergroups.store', compact('common', 'get_customer_group'));
    }

    // Delete Product
    public function delete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_customer_group =  CustomerGroups::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_customer_group) {
            $get_customer_group->is_delete = 1;
            $get_customer_group->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }

    
}