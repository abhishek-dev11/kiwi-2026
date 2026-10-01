<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Settings;
use App\Models\StockInventory;
use App\Models\Sale;
use App\Models\Items;
use App\Models\Ledger;
use App\Models\Products;
use App\Models\Categories;
use App\Models\Suppliers;
use App\Models\Customers;
use App\Models\Order;
use App\Models\OrderMeta;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use View;
use Mail;

class DashboardController extends Controller
{
    // Orders in these statuses don't count towards revenue - no money was
    // actually retained (cancelled before payment / refunded after).
    const NON_REVENUE_STATUSES = ['Order Cancelled', 'Refunded'];

    // Years selectable from the Orders Overview chart's year dropdown.
    const CHART_YEARS = [2026, 2027, 2028, 2029, 2030];

    //
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Dashboard';
        $common['main_menu']    = 'dashboard';
        $common['submain_menu'] = 'dashboard';

        $stats = array();
        $stats['total_products']        = Products::whereNull('is_delete')->count();
        $stats['total_categories']      = Categories::whereNull('is_delete')->count();
        $stats['total_suppliers']       = Suppliers::where(ConditionQuery())->count();
        $stats['total_orders']          = Order::count();
        $stats['total_customers']       = Customers::where(ConditionQuery())->where('customer_type', 'individual')->count();
        $stats['total_trade_customers'] = Customers::where(ConditionQuery())->where('customer_type', 'business')->count();

        $revenueOrderIds = Order::whereNotIn('order_status', self::NON_REVENUE_STATUSES)->pluck('id');
        $stats['total_revenue'] = OrderMeta::where('meta_key', 'total')
            ->whereIn('order_id', $revenueOrderIds)
            ->sum('meta_value');

        $getRecentOrders = Order::orderBy('id', 'desc')->take(10)->get();
        $recentOrderMeta = OrderMeta::whereIn('order_id', $getRecentOrders->pluck('id'))
            ->where('meta_key', 'total')
            ->get()
            ->groupBy('order_id');

        $recent_orders = array();
        foreach ($getRecentOrders as $value) {
            $meta = $recentOrderMeta->get($value->id, collect());
            $recent_orders[] = [
                'id'           => $value->id,
                'created_at'   => $value->created_at,
                'full_name'    => trim($value->billing_first_name.' '.$value->billing_last_name),
                'order_status' => $value->order_status,
                'total'        => optional($meta->firstWhere('meta_key', 'total'))->meta_value ?? 0,
                'status_color' => $this->orderStatusColor($value->order_status),
            ];
        }

        $chart_year   = in_array((int) now()->year, self::CHART_YEARS) ? (int) now()->year : self::CHART_YEARS[0];
        $chart_years  = self::CHART_YEARS;
        [$chart_labels, $chart_orders] = $this->monthlyOrderCountsForYear($chart_year);

        return view('admin.dashboard', compact('common', 'stats', 'recent_orders', 'chart_labels', 'chart_orders', 'chart_year', 'chart_years'));
    }

    // AJAX: Orders Overview chart data for a given year (Jan-Dec, zero-filled
    // for months with no orders so the chart doesn't silently skip gaps).
    public function ordersChartData(Request $request)
    {
        $year = (int) $request->input('year');
        if (!in_array($year, self::CHART_YEARS)) {
            $year = self::CHART_YEARS[0];
        }

        [$labels, $orders] = $this->monthlyOrderCountsForYear($year);

        return response()->json([
            'labels' => $labels,
            'orders' => $orders,
        ]);
    }

    private function monthlyOrderCountsForYear($year)
    {
        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $counts = Order::selectRaw('MONTH(created_at) as m, COUNT(*) as cnt')
            ->whereYear('created_at', $year)
            ->groupBy('m')
            ->pluck('cnt', 'm');

        $labels = [];
        $orders = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $monthNames[$m - 1];
            $orders[] = (int) ($counts[$m] ?? 0);
        }

        return [$labels, $orders];
    }

    private function orderStatusColor($status){
        return match ($status) {
            'Completed' => 'table-success',
            'Order Cancelled', 'Refunded' => 'table-danger',
            'Returned' => 'table-warning',
            default => '',
        };
    }

    //User Profile
    public function user_profile(Request $request){
        $id =  auth()->user()->id;
        $get_user = User::where(['id' => $id])->first();
        if ($request->isMethod('post')) {
            $data = $request->all();

            $req_fields = array();
            $errormsg   = array();
            $req_fields['name'] = "required";
            $errormsg['name'] = "Name";

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
                return redirect(route('admin.profile'))->withErrors($validator)->withInput();
            }

            if ($request->has('is_change_password') && $request->is_change_password != "") {
                if (Hash::check($request->old_password, $get_user->password)) {
                    
                } else {
                    return redirect(route('admin.profile'))->withErrors(["error" => "Incorrect old password...."]);
                }
            }

            $get_user->name      = $request->name;
            if ($request->hasFile('profile_pic')) {
                //$img = $request->file('profile_pic');
                $path = 'uploads/users';
                $imagePath = image_upload($request->file('profile_pic'), $path);
                $get_user->image = $imagePath;
            }


            if ($request->has('is_change_password') && $request->is_change_password != "") {
                $get_user->password = Hash::make($request->new_password);

                $name          = $get_user->name;
                $email         = $get_user->email;
                $data = array('subject' => "Change Password",'email' => $email, 'name' => $name, 'page' => 'email.changepassword');

                Mail::send($data['page'], $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                });
            }
            $get_user->save();
            return redirect(route('admin.profile'))->withErrors(["success" => "Propfile updated successfully...."]);
        }

        $common = array();
        $common['title'] = 'Dashboard';
        $common['main_menu']    = 'profile';
        $common['submain_menu'] = 'profile';

        return view('admin.profile', compact('common', 'get_user'));
    }

    public function get_append_view(Request $request){
        $request_data = $request->all();
        return View::make($request->params['view'],compact('request_data'))->render();
    }

    ///Logout
    public function logout(){
        session()->flush();
        return redirect()->route('admin.login');
    }
}
