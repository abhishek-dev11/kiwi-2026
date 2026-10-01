<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Coupon;

class CouponController extends Controller
{

    // Coupon List
    public function index(Request $request)
    {
        $common = array();
        $common['title']        = 'Coupons';
        $common['main_menu']    = 'coupons';
        $common['submain_menu'] = 'coupons';
        $common['filter_coupon_code']   = "";
        $common['filter_coupon_status'] = "";

        $getCoupons = Coupon::whereNull('is_delete')->orderBy('id', 'desc');

        $filterFields = ['filter_coupon_code', 'filter_coupon_status'];
        $common = updateSessionFilters($request, $filterFields, $common);

        if ($common['filter_coupon_code']) {
            $getCoupons->where('code', 'like', '%'.$common['filter_coupon_code'].'%');
        }
        if ($common['filter_coupon_status']) {
            $getCoupons->where('status', $common['filter_coupon_status']);
        }

        $getCoupons = $getCoupons->paginate(config('adminconfig.records_per_page'));

        return view('admin.coupon.index', compact('common', 'getCoupons'));
    }

    // Add Or Update Coupon
    public function store(Request $request, $id = "")
    {
        $common = array();
        $common['title']        = 'Coupon Add';
        $common['main_menu']    = 'coupons';
        $common['submain_menu'] = 'coupons';

        $get_coupon = getTableColumn('coupons');

        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id = checkDecrypt($id);
            $get_coupon = Coupon::where('id', $id)->first();

            if (!$get_coupon) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['code']              = "required|max:50|unique:coupons,code".($request->id ? ",".$request->id : "");
            $req_fields['type']              = "required|in:fixed,percentage";
            $req_fields['value']             = $request->type == 'percentage' ? "required|numeric|min:0.01|max:100" : "required|numeric|min:0.01";
            $req_fields['max_discount_amount'] = "nullable|numeric|min:0";
            $req_fields['min_order_amount']    = "nullable|numeric|min:0";
            $req_fields['usage_limit']         = "nullable|integer|min:1";
            $req_fields['start_date']           = "nullable|date";
            $req_fields['end_date']             = "nullable|date|after_or_equal:start_date";
            $req_fields['status']               = "required|in:Active,Deactive";

            $errormsg = array();
            $errormsg['code']                 = translate("Coupon Code");
            $errormsg['type']                 = translate("Type");
            $errormsg['value']                = translate("Value");
            $errormsg['max_discount_amount']  = translate("Max Discount Amount");
            $errormsg['min_order_amount']     = translate("Minimum Order Amount");
            $errormsg['usage_limit']          = translate("Usage Limit");
            $errormsg['start_date']           = translate("Start Date");
            $errormsg['end_date']             = translate("End Date");
            $errormsg['status']               = translate("Status");

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                return response()->json(['error' => $validation->getMessageBag()->toArray()]);
            }

            if ($request->id != "") {
                $message = translate("Update Successfully");
                $status  = "success";
                $Coupon  = Coupon::find($request->id);
            } else {
                $message = translate("Add Successfully");
                $status  = "success";
                $Coupon  = new Coupon();
            }

            $Coupon->code                 = strtoupper(trim($request->code));
            $Coupon->type                 = $request->type;
            $Coupon->value                = $request->value;
            $Coupon->max_discount_amount  = $request->filled('max_discount_amount') ? $request->max_discount_amount : null;
            $Coupon->min_order_amount     = $request->filled('min_order_amount') ? $request->min_order_amount : null;
            $Coupon->usage_limit          = $request->filled('usage_limit') ? $request->usage_limit : null;
            $Coupon->start_date           = $request->filled('start_date') ? $request->start_date : null;
            $Coupon->end_date             = $request->filled('end_date') ? $request->end_date : null;
            $Coupon->status               = $request->status;
            $Coupon->save();

            return response()->json([$status => $message, 'error' => false, 'inputs' => $request->all()]);
        }

        return view('admin.coupon.store', compact('common', 'get_coupon'));
    }

    // Delete Coupon
    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $get_coupon = Coupon::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_coupon) {
            $get_coupon->is_delete = 1;
            $get_coupon->save();
        }
        return back()->withErrors(["success" => translate("Delete Successfully")]);
    }

}
