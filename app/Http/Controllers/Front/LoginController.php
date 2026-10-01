<?php
namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mail;
use App\Models\User;
use App\Models\Customers;
use App\Models\TradeCustomerCompany;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class LoginController extends Controller
{

    //
    public function index(Request $request)
    {
        $common = [];
        if ($request->isMethod('post')) {
            $req_fields        = array();
            $req_fields['email']        = "required";
            $req_fields['password']     = "required";
            $errormsg = [
                "email"       => translate("Email"),
                "password"    => translate("Password"),
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
                return response()->json(['error' => true, 'data' => $validation->getMessageBag()->toArray(), 'message' => 'Fill all required field!' ]);
            }

            $user = Customers::where(['email' => $request->email ])->whereNull('is_delete')->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $userdata = array(
                        'email' => $request->email,
                        'password' => $request->password
                    );
                    //if (Auth::attempt($userdata)) {
                    if (Auth::guard('customers')->attempt($userdata)) {
                        return response()->json(['error'=>false, 'message' => "Login Successfully"]); 
                    } else {
                        return response()->json(['error' => true, 'message' => "Invalid Credential"]);
                    }
                } else {
                    return response()->json(['error' => true, 'message' => "Invalid Credential"]);
                }
            } else {
                return response()->json(['error' => true, 'message' => "Invalid Credential"]); 
            }
        }
        return view('front.login', compact('common'));
    }

    ///Forget Password
    public function forgot_password(Request $request)
    {
        if ($request->isMethod('post')) {

            $req_fields        = array();
            $req_fields['email']        = "required";

            $errormsg = [
                "email"       => translate("Email"),
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
                return back()->withErrors($validation)->withInput();
            }

            $user = Customers::where(['email' => $request->email])->first();

            if ($user) {
                $user_id          = $user->id;
                $name          = $user->name;
                $email         = $user->email;
                $expFormat = mktime(
                    date("H"),
                    date("i"),
                    date("s"),
                    date("m"),
                    date("d") + 1,
                    date("Y")
                );

                $expDate = date("Y-m-d H:i:s", $expFormat);
                $key = md5($user_id);
                $addKey = substr(md5(uniqid(rand(), 1)), 3, 10);
                $key = $key . $addKey;
                $user->reset_password_key = $key;
                $user->reset_password_expiry = $expDate;
                $user->save();

                $data = array('subject' => "Forgot Password", 'email' => $email, 'key' => $key, 'name' => $name, 'page' => 'email.front.forgotpassword');

                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
                //return redirect(route('front.forgot-password'))->withErrors(["success" => "Mail Send Successfully"]);
                return redirect(route('front.forgot-password'))->with('success', "We’ve sent a password reset link to {$email}");
            } else {
                return back()->withErrors(['error' => 'Invalid Email Address'])->withInput();
            }
        } else {
            return view('front.forgot_password');
        }
    }

    ///Reset Password
    public function reset_password($user_id = "", Request $request)
    {

        if ($user_id != "") {
            $user =  Customers::where('reset_password_key', $user_id)->first();
            if ($user) {

            } else {
                return redirect(route('user.login'));
            }
        } else {
            return redirect(route('user.login'));
        }

        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'new_password'     => 'required',
                'confirm_password' => 'required_with:new_password|same:new_password|'
            ]);

            if ($validator->fails()) {
                return redirect(route('user.reset_password', $user_id))->withErrors($validator)->withInput();
            }

            $user = Customers::where(['reset_password_key' => $user_id])->first();
            $user->password = Hash::make($request->new_password);
            $user->reset_password_key = NULL;
            $user->reset_password_expiry = NULL;
            $user->save();
            $name   = $user->name;
            $email  = $user->email;
            $data = array('subject' => "Change Password", 'email' => $email, 'name' => $name, 'page' => 'email.front.changepassword');
            Mail::send($data['page'], $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });
            return redirect(route('user.login'))->withErrors(["success" => "Password Update Successfully "]);
        }

        return view('front.reset_password', compact('user_id'));
    }

    public function signup(Request $request){
        $common = [];
        $get_countries  = Country::whereNull('is_delete')->orderBy('id', 'desc')->get();

        /*$name = 'Abhishek Kashyap';
        $email = 'dev1.infosparkles@gmail.com';
        $data = array('subject' => "Welcome to KIWI", 'email' => $email, 'name' => $name, 'page' => 'email.front.welcome');
        Mail::send($data['page'], $data, function ($message) use ($data) {
            $message->to($data['email'])->subject($data['subject']);
        });*/

        return view('front.signup.signup', compact('common','get_countries'));
    }

    // Customer Signup
    public function customer_signup(Request $request){
        $common = [];
        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['first_name']   = "required";
            $req_fields['last_name']    = "required";
            $req_fields['email']    = 'required|email|unique:customers,email';
            $req_fields['password'] = 'required';
            $req_fields['confirm_password'] = "required|min:6|same:password";
            //$req_fields['phone']        = "required";

            $errormsg = array();
            $errormsg['first_name'] = translate("First Name");
            $errormsg['last_name'] = translate("Last Name");
            $errormsg['email'] = translate("Email");
            $errormsg['password'] = translate("Password");
            $errormsg['confirm_password'] = translate("Confirm Password");
            //$errormsg['phone'] = translate("Phone");

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

            $name = $request->first_name.' '.$request->last_name;
            $email = $request->email;

            $message    = translate("Add Successfully");
            $status     = "success";
            $Customer = new Customers();
            $Customer->email        = $request->email;
            $Customer->password     = Hash::make($request->password);
            $Customer->first_name   = $request->first_name;
            $Customer->last_name    = $request->last_name;
            //$Customer->phone            = ($request->phone) ? $request->phone : NULL;
            //$Customer->status           = $request->status;
            $Customer->save();

            $data = array('subject' => "Welcome to KIWI", 'email' => $email, 'name' => $name, 'page' => 'email.front.welcome');
            Mail::send($data['page'], $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });

            return response()->json([$status => $message,'error'=>false]);     
        }
        return view('front.customer-signup', compact('common'));
    }
    
    // Trade Customer Signup
    public function trade_signup(Request $request){

        $common = array();
        
        if ($request->isMethod('post')) {

            $req_fields = array();

            /* Step 1 */
            $req_fields['trade_first_name']         = "required";
            $req_fields['trade_last_name']          = "required";
            $req_fields['trade_phone']              = "required";
            $req_fields['trade_email']              = 'required|email|unique:customers,email';
            $req_fields['trade_password']           = 'required';
            $req_fields['trade_confirm_password']   = "required|min:6|same:trade_password";

            /* Step 2 */
            $req_fields['company_registered_name']      = "required";
            $req_fields['company_registration_number']  = "required";
            $req_fields['period_of_trading']            = "required";
            $req_fields['number_of_staff']              = "required";

            /* Step 3 */
            $req_fields['physical_address']     = "required";
            $req_fields['country_id']           = "required";
            $req_fields['state_id']             = "required"; 
            $req_fields['city_id']              = "required"; 
            $req_fields['postcode']             = "required"; 

            /* Step 4 */

            $req_fields['delivery_address']     = "required";
            $req_fields['company_name_1']       = "required";
            $req_fields['company_address_1']    = "required";
            $req_fields['company_tel_1']        = "required";
            $req_fields['account_name_1']       = "required";

            $req_fields['company_name_2']       = "required";
            $req_fields['company_address_2']    = "required";
            $req_fields['company_tel_2']        = "required";
            $req_fields['account_name_2']       = "required";

            $req_fields['company_name_3']       = "required";
            $req_fields['company_address_3']    = "required";
            $req_fields['company_tel_3']        = "required";
            $req_fields['account_name_3']       = "required";

            


            //$errormsg['regular_price'] = translate("Regular Price");
            $errormsg = array();
            /* Step 1 */
            $errormsg['trade_first_name'] = translate("First Name");
            $errormsg['trade_last_name'] = translate("Last Name");
            $errormsg['trade_phone'] = translate("Phone");
            $errormsg['trade_email'] = translate("Email");
            $errormsg['trade_password'] = translate("Password");
            $errormsg['trade_confirm_password'] = translate("Confirm Password");

            /* Step 2 */
            $errormsg['company_registered_name'] = translate("Company Name");
            $errormsg['company_registration_number'] = translate("Company Registration Number");
            $errormsg['period_of_trading'] = translate("Period of Trading");
            $errormsg['number_of_staff'] = translate("Number of staff");

            /* Step 3 */
            $errormsg['physical_address'] = translate("Physical Address Details");
            $errormsg['country_id'] = translate("Country");
            $errormsg['state_id'] = translate("State");
            $errormsg['city_id'] = translate("City");
            $errormsg['postcode'] = translate("Postcode / ZIP");

            /* Step 4 */
            $errormsg['delivery_address']   = translate("Delivery Address Details");

            $errormsg['company_name_1']     = translate("Company Name");
            $errormsg['company_address_1']  = translate("Address");
            $errormsg['company_tel_1']      = translate("Telephone No");
            $errormsg['account_name_1']     = translate("Account name");

            $errormsg['company_name_2']     = translate("Company Name");
            $errormsg['company_address_2']  = translate("Address");
            $errormsg['company_tel_2']      = translate("Telephone No");
            $errormsg['account_name_2']     = translate("Account name");

            $errormsg['company_name_3']     = translate("Company Name");
            $errormsg['company_address_3']  = translate("Address");
            $errormsg['company_tel_3']      = translate("Telephone No");
            $errormsg['account_name_3']     = translate("Account name");


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

            $message    = translate("Add Successfully");
            $status     = "success";

            $Customer = new Customers();
            $Customer->customer_type        = 'business';
            $Customer->customer_group_id    = ($request->customer_group_id) ? $request->customer_group_id : NULL;

            /* Step 1 */
            $Customer->first_name   = $request->trade_first_name;
            $Customer->last_name    = $request->trade_last_name;
            $Customer->phone        = ($request->trade_phone) ? $request->trade_phone : NULL;
            $Customer->email        = $request->trade_email;
            $Customer->password     = Hash::make($request->trade_password);

            /* Step 2 */
            $Customer->company_registered_name      = $request->company_registered_name;
            $Customer->company_trading_name         = ($request->company_trading_name) ? $request->company_trading_name : NULL;
            $Customer->company_registration_number  = $request->company_registration_number;
            $Customer->period_of_trading            = $request->period_of_trading;
            $Customer->number_of_staff              = $request->number_of_staff;

            /* Step 3 */
            $Customer->physical_address_details = ($request->physical_address) ? $request->physical_address : NULL;
            $Customer->address_line             = ($request->address_line_2) ? $request->address_line_2 : NULL;
            $Customer->country                  = ($request->country_id) ? $request->country_id : NULL;
            $Customer->state                    = ($request->state_id) ? $request->state_id : NULL;
            $Customer->city                     = ($request->city_id) ? $request->city_id : NULL;
            $Customer->postcode                 = ($request->postcode) ? $request->postcode : NULL;
            $Customer->gst                      = ($request->gst_number) ? $request->gst_number : NULL;

            /* Step 4 */
            $Customer->delivery_first_name      = ($request->delivery_first_name) ? $request->delivery_first_name : NULL;
            $Customer->delivery_last_name       = ($request->delivery_last_name) ? $request->delivery_last_name : NULL;
            $Customer->delivery_phone           = ($request->delivery_phone) ? $request->delivery_phone : NULL;
            $Customer->delivery_address         = ($request->delivery_address) ? $request->delivery_address : NULL;
            $Customer->customer_address         = ($request->customer_address) ? $request->customer_address : NULL;
            $Customer->status                   = 'Active';
            $Customer->save();

            if( $Customer->id ){
                $TradeCompany = new TradeCustomerCompany();
                $TradeCompany->customer_id          = $Customer->id;

                $TradeCompany->company_name_1       = ($request->company_name_1) ? $request->company_name_1 : NULL;
                $TradeCompany->company_address_1    = ($request->company_address_1) ? $request->company_address_1 : NULL;
                $TradeCompany->company_tel_1        = ($request->company_tel_1) ? $request->company_tel_1 : NULL;
                $TradeCompany->account_name_1       = ($request->account_name_1) ? $request->account_name_1 : NULL;
                
                $TradeCompany->company_name_2       = ($request->company_name_2) ? $request->company_name_2 : NULL;
                $TradeCompany->company_address_2    = ($request->company_address_2) ? $request->company_address_2 : NULL;
                $TradeCompany->company_tel_2        = ($request->company_tel_2) ? $request->company_tel_2 : NULL;
                $TradeCompany->account_name_2       = ($request->account_name_2) ? $request->account_name_2 : NULL;

                $TradeCompany->company_name_3       = ($request->company_name_3) ? $request->company_name_3 : NULL;
                $TradeCompany->company_address_3    = ($request->company_address_3) ? $request->company_address_3 : NULL;
                $TradeCompany->company_tel_3        = ($request->company_tel_3) ? $request->company_tel_3 : NULL;
                $TradeCompany->account_name_3       = ($request->account_name_3) ? $request->account_name_3 : NULL;
                $TradeCompany->save();

                $name = $request->trade_first_name.' '.$request->trade_last_name;
                $email = $request->trade_email;
                $data = array('subject' => "Welcome to KIWI", 'email' => $email, 'name' => $name, 'page' => 'email.front.welcome');
                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });


                return response()->json([ $status => $message, 'error'=>false ]);     
            }

        }

        return view('front.trade-signup', compact('common','get_countries'));
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
