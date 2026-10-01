<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Customers;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\CustomerGroups;

class CustomerController extends Controller
{

    // Customers List
    public function index(Request $request)
    {  
        $common = array();
        $common['title']        = 'Customers';
        $common['main_menu']    = 'customers_list';
        $common['submain_menu'] = 'customers';
        $common['filter_customer_name']   = "";
        $common['filter_customer_email']  = "";
        $common['filter_customer_status'] = "";

        $getCustomers = Customers::where(ConditionQuery())->where('customer_type' , 'individual')->orderBy('id', 'desc');

        $filterFields = ['filter_customer_name', 'filter_customer_email', 'filter_customer_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        /*if($common['filter_customer_name']){
            $getCustomers->where('first_name','like','%'.$common['filter_customer_name'].'%');         
        }*/

        if ($common['filter_customer_name']) {
            $getCustomers->where('first_name', 'like', '%' . $common['filter_customer_name'] . '%');
            $getCustomers->orWhere('last_name', 'like', '%' . $common['filter_customer_name'] . '%');
        }

        if($common['filter_customer_email']){
            //$getCustomers->where('email',$common['filter_customer_email']);
            $getCustomers->where('email','like', '%' . $common['filter_customer_email'] . '%');
        }

        $getCustomers = $getCustomers->paginate(config('adminconfig.records_per_page'));
        
        return view('admin.customers.index', compact('common', 'getCustomers'));
    }

    // Add Or Update Customer
    public function store(Request $request, $id = ""){

        $common = array();
        $common['title']        = 'Customer Add';
        $common['main_menu']    = 'customers_list';
        $common['submain_menu'] = 'customers';

        $get_customer   = getTableColumn('customers');
        $get_customer_groups  = CustomerGroups::where(ConditionQuery())->orderBy('id', 'desc')->get();
        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_customer = Customers::where('id', $id)->first();
            
            if (!$get_customer) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['first_name']   = "required";
            $req_fields['last_name']    = "required";
            if ($request->id == "") {
                $req_fields['email']    = 'required|email|unique:customers,email';
                $req_fields['password'] = 'required';
            }
            $req_fields['phone']        = "required";
          
            

            //$errormsg['regular_price'] = translate("Regular Price");
            $errormsg = array();
            $errormsg['first_name'] = translate("First Name");
            $errormsg['last_name'] = translate("Last Name");
            if ($request->id == "") {
                $errormsg['email'] = translate("Email");
                $errormsg['password'] = translate("Password");
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
                $Customer = Customers::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Customer = new Customers();
                $Customer->email        = $request->email;
                $Customer->password     = Hash::make($request->password);
            }
            
            $Customer->first_name       = $request->first_name;
            $Customer->last_name        = $request->last_name;
            $Customer->phone            = ($request->phone) ? $request->phone : NULL;
            $Customer->status           = $request->status;
            $Customer->save();
            return response()->json([$status => $message,'error'=>false, 'inputs' => $request->all()]);     
        }

        return view('admin.customers.store', compact('common', 'get_customer', 'get_customer_groups'));
    }

    // Trade Customer List
    public function trade_list(Request $request)
    {  
        $common = array();
        $common['title']        = 'Trade Customers';
        $common['main_menu']    = 'customers_list';
        $common['submain_menu'] = 'trade_customers';
        $common['filter_customer_name']   = "";
        $common['filter_customer_email']  = "";
        $common['filter_customer_status'] = "";

        $getCustomers = Customers::where(ConditionQuery())->where('customer_type' , 'business')->orderBy('id', 'desc');

        $filterFields = ['filter_customer_name', 'filter_customer_email', 'filter_customer_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        /*if($common['filter_customer_name']){
            $getCustomers->where('first_name','like','%'.$common['filter_customer_name'].'%');         
        }*/

        if ($common['filter_customer_name']) {
            $getCustomers->where('first_name', 'like', '%' . $common['filter_customer_name'] . '%');
            $getCustomers->orWhere('last_name', 'like', '%' . $common['filter_customer_name'] . '%');
        }

        if($common['filter_customer_email']){
            //$getCustomers->where('email',$common['filter_customer_email']);
            $getCustomers->where('email','like', '%' . $common['filter_customer_email'] . '%');
        }

        $getCustomers = $getCustomers->paginate(config('adminconfig.records_per_page'));
        
        return view('admin.customers-trade.index', compact('common', 'getCustomers'));
    }

    // Add Or Update Trade Customer
    public function trade_store(Request $request, $id = ""){

        $common = array();
        $common['title']        = 'Trade Customer Add';
        $common['main_menu']    = 'customers_list';
        $common['submain_menu'] = 'trade_customers';

        $get_customer   = getTableColumn('customers');
        $get_countries  = Country::whereNull('is_delete')->orderBy('id', 'desc')->get();
        $get_customer_groups  = CustomerGroups::where(ConditionQuery())->orderBy('id', 'desc')->get();

        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_customer = Customers::where('id', $id)->first();
            
            if (!$get_customer) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['first_name']   = "required";
            $req_fields['last_name']    = "required";
            if ($request->id == "") {
                $req_fields['email']    = 'required|email|unique:customers,email';
                $req_fields['password'] = 'required';
            }
            //$req_fields['quantity']   = "required|int|min:0";
            $req_fields['phone']        = "required";
            $req_fields['country_id']   = "required";
            $req_fields['state_id']     = "required";
            //$req_fields['city.*']     = "required";
            

            //$errormsg['regular_price'] = translate("Regular Price");
            $errormsg = array();
            $errormsg['first_name'] = translate("First Name");
            $errormsg['last_name'] = translate("Last Name");
            if ($request->id == "") {
                $errormsg['email'] = translate("Email");
                $errormsg['password'] = translate("Password");
            }
            $errormsg['phone'] = translate("Phone");
            $errormsg['country_id'] = translate("Country");
            $errormsg['state_id'] = translate("State");
            $errormsg['city_id'] = translate("City");
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
                $Customer = Customers::find($request->id);
            } else {
                $message    = translate("Add Successfully");
                $status     = "success";
                $Customer = new Customers();
                $Customer->email        = $request->email;
                $Customer->password     = Hash::make($request->password);
            }
            $Customer->customer_type    = 'business';
            $Customer->first_name       = $request->first_name;
            $Customer->last_name        = $request->last_name;
            $Customer->phone            = ($request->phone) ? $request->phone : NULL;
            $Customer->customer_group_id    = ($request->customer_group_id) ? $request->customer_group_id : NULL;
            $Customer->country          = ($request->country_id) ? $request->country_id : NULL;
            $Customer->state            = ($request->state_id) ? $request->state_id : NULL;
            $Customer->city             = ($request->city_id) ? $request->city_id : NULL;
            $Customer->status           = $request->status;
            $Customer->save();
            return response()->json([$status => $message,'error'=>false, 'inputs' => $request->all()]);     
        }

        return view('admin.customers-trade.store', compact('common', 'get_customer','get_countries', 'get_customer_groups'));
    }

    // Delete Product
    public function delete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_customer =  Customers::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_customer) {
            $get_customer->is_delete = 1;
            $get_customer->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }


    public function get_state_by_country_id(Request $request){
        $html = '<option value="">'.$request->title.'</option>';
        if ($request->isMethod('post')) {
            $get_states =  State::where(['country_id' => $request->country_id])->whereNull('is_delete')->orderBy('id', 'desc')->get();
            if( $get_states->isNotEmpty()){
                foreach ($get_states as $key => $value) {
                    $html .='<option value="'.$value->id.'" '.getSelected($value->id, $request->child).' >'.$value->name.'</option>';
                }
            }
        }
        echo $html;
    }

    public function get_city_by_state_id(Request $request){
        $html = '<option value="">'.$request->title.'</option>';
        if ($request->isMethod('post')) {
            $get_states =  City::where(['state_id' => $request->state_id,'country_id' => $request->country_id])->whereNull('is_delete')->orderBy('id', 'desc')->get();
            if( $get_states->isNotEmpty()){
                foreach ($get_states as $key => $value) {
                    $html .='<option value="'.$value->id.'" '.getSelected($value->id, $request->child).' >'.$value->name.'</option>';
                }
            }
        }
        echo $html;
    }
    
}