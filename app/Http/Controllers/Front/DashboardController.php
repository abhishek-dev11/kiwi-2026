<?php
namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use View;
use Mail;
use Carbon\Carbon;
use App\Models\Customers;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderMeta;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $customer = Auth::guard('customers')->user();
        $common = array();
        $common = array();
        $common['filter_front_order_keyword'] = "";
        $common['filter_front_order_status'] = "";
        $common['filter_front_order_date_from'] = "";
        $common['filter_front_order_date_to'] = "";

        $filterFields = ['filter_front_order_keyword', 'filter_front_order_status', 'filter_front_order_date_from', 'filter_front_order_date_to'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $GetOrders = Order::where('customer_id', $customer->id)->orderBy('id', 'desc');

        if($common['filter_front_order_keyword']){
            $GetOrders->where('id','like','%'.$common['filter_front_order_keyword'].'%');         
        }

        /*if($common['filter_front_order_keyword']){
            $keyword = $common['filter_front_order_keyword'];
            $GetOrders->where(function($query) use ($keyword) {
                $query->where('id', 'like', '%' . $keyword . '%')
                      ->orWhere('order_status', 'like', '%' . $keyword . '%');
            });
        }*/

        if($common['filter_front_order_status']){
            $GetOrders->where('order_status',$common['filter_front_order_status']);
        }

        // Date range filter
        $dateFrom = $common['filter_front_order_date_from'];
        $dateTo   = $common['filter_front_order_date_to'];

        if ($dateFrom && $dateTo) {
            // Both dates provided — between range
            $GetOrders->whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ]);
        } elseif ($dateFrom) {
            // Only from date
            $GetOrders->whereDate('created_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            // Only to date
            $GetOrders->whereDate('created_at', '<=', $dateTo);
        }

        $Orders  = $GetOrders->paginate(config('adminconfig.records_per_page'));

        $order_arrs = [];
        foreach ($Orders as $key => $value) {
            $order_arr = [];
            $order_arr['sn'] = $Orders->firstItem() + $key;
            $order_arr['id'] = $value->id;
            $order_arr['order_date'] = $value->created_at;
            $order_arr['order_status'] = $value->order_status;
            $order_arr['payment_method'] = $value->payment_method;
            $OrderTotal = OrderMeta::where([ 'customer_id' => $customer->id, 'order_id' => $value->id, 'meta_key' => 'total' ])->first();
            $order_arr['total'] = $OrderTotal->meta_value ?? ''; 
            $order_arrs[] = $order_arr;
        }

        return view('front.dashboard', compact('common','Orders','order_arrs'));
    }

    public function profile_update(Request $request){
        $id =  Auth::guard('customers')->user()->id;
        $get_user = Customers::where(['id' => $id])->first();
        if ($request->isMethod('post')) {
            $data = $request->all();

            $req_fields = array();
            $errormsg   = array();
            $req_fields['first_name'] = "required";
            $req_fields['last_name'] = "required";

            $errormsg['first_name'] = "First Name";
            $errormsg['last_name'] = "Last Name";

            if ($request->has('is_change_password')) {
                $req_fields['old_password'] = "required";
                $req_fields['new_password'] = 'min:6|required';
                $req_fields['password_confirmation'] = "min:6|same:new_password";

                $errormsg['old_password'] = "Old Password";
                $errormsg['new_password'] = "New Password";
                $errormsg['password_confirmation'] = "Confirm Password";
            }

            $validator = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                    'same' => 'The :attribute confirmation does not match.',
                    'min' => 'The :attribute must have atleast 6 charectors.'
                ],
                $errormsg
            );

            if ($validator->fails()) { 
                return redirect(route('user.profile'))->withErrors($validator)->withInput();
            }

            if ($request->has('is_change_password') && $request->is_change_password != "") {
                if (Hash::check($request->old_password, $get_user->password)) {
                    
                } else {
                    return redirect(route('user.profile'))->withErrors(["error" => "Incorrect old password...."]);
                }
            }

            $get_user->first_name      = $request->first_name;
            $get_user->last_name      = $request->last_name;
            if ($request->hasFile('profile_pic')) {
                $image = image_upload($request->file('profile_pic'), 'users');
                $get_user->image   = $image;
            }

            if ($request->has('is_change_password') && $request->is_change_password != "") {
                $get_user->password = Hash::make($request->new_password);
                $name          = $get_user->first_name.' '.$get_user->last_name;
                $email         = $get_user->email;
                $data = array('subject' => "Change Password",'email' => $email, 'name' => $name, 'page' => 'email.changepassword');
                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
            }

            $get_user->save();
            return redirect(route('user.profile'))->withErrors(["success" => "Propfile updated successfully...."]);
        }

        $common = array();
        $common['title'] = 'Dashboard';
        $common['main_menu']    = 'profile';
        $common['submain_menu'] = 'profile';

        return view('front.profile', compact('common', 'get_user'));
    }

    ///Logout
    public function logout(){
        session()->flush();
        return redirect()->route('user.login');
    }
}
