<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ShippingMethod;
use App\Models\ShippingWeightRate;

class ShippingMethodController extends Controller
{

    // Shipping Method List
    public function index(Request $request)
    {
        $common = array();
        $common['title']        = 'Shipping Methods';
        $common['main_menu']    = 'shipping';
        $common['submain_menu'] = 'shipping_methods';
        $common['filter_shipping_method_title']  = "";
        $common['filter_shipping_method_status'] = "";

        $getShippingMethods = ShippingMethod::whereNull('is_delete')->orderBy('id', 'desc');

        $filterFields = ['filter_shipping_method_title', 'filter_shipping_method_status'];
        $common = updateSessionFilters($request, $filterFields, $common);

        if ($common['filter_shipping_method_title']) {
            $getShippingMethods->where('title', 'like', '%'.$common['filter_shipping_method_title'].'%');
        }
        if ($common['filter_shipping_method_status']) {
            $getShippingMethods->where('status', $common['filter_shipping_method_status']);
        }

        $getShippingMethods = $getShippingMethods->paginate(config('adminconfig.records_per_page'));

        return view('admin.shipping-method.index', compact('common', 'getShippingMethods'));
    }

    // Add Or Update Shipping Method
    public function store(Request $request, $id = "")
    {
        $common = array();
        $common['title']        = 'Shipping Method Add';
        $common['main_menu']    = 'shipping';
        $common['submain_menu'] = 'shipping_methods';

        $get_shipping_method = getTableColumn('shipping_methods');
        $weight_rates = array();

        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id = checkDecrypt($id);
            $get_shipping_method = ShippingMethod::where('id', $id)->first();

            if (!$get_shipping_method) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }

            $weight_rates = $get_shipping_method->weightRates()->get()->toArray();
        }

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['title']     = "required|max:255";
            $req_fields['type']      = "required|in:flat,weight_based";
            $req_fields['flat_rate'] = $request->type == 'flat' ? "required|numeric|min:0" : "nullable|numeric|min:0";
            $req_fields['status']    = "required|in:Active,Deactive";

            $errormsg = array();
            $errormsg['title']     = translate("Title");
            $errormsg['type']      = translate("Type");
            $errormsg['flat_rate'] = translate("Flat Rate");
            $errormsg['status']    = translate("Status");

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

            // Weight brackets - ignore rows where rate was left blank (unused
            // add-row template rows, or a row the admin cleared out instead of removing).
            $weightRatesInput = $request->input('weight_rates', []);
            $validBrackets = array();
            foreach ($weightRatesInput as $bracket) {
                if (!isset($bracket['rate']) || $bracket['rate'] === '' || $bracket['rate'] === null) {
                    continue;
                }
                $validBrackets[] = $bracket;
            }

            if ($request->type == 'weight_based' && count($validBrackets) < 1) {
                return response()->json(['error' => ['weight_rates' => translate("At least one weight bracket is required for a weight based shipping method.")]]);
            }

            try {
                DB::beginTransaction();

                if ($request->id != "") {
                    $message = translate("Update Successfully");
                    $status  = "success";
                    $ShippingMethod = ShippingMethod::find($request->id);
                } else {
                    $message = translate("Add Successfully");
                    $status  = "success";
                    $ShippingMethod = new ShippingMethod();
                }

                $ShippingMethod->title     = $request->title;
                $ShippingMethod->type      = $request->type;
                $ShippingMethod->flat_rate = $request->type == 'flat' ? $request->flat_rate : null;
                $ShippingMethod->status    = $request->status;
                $ShippingMethod->save();

                // Rebuild weight brackets from scratch every save - simplest way to
                // guarantee removed/edited rows never leave stale brackets behind.
                ShippingWeightRate::where('shipping_method_id', $ShippingMethod->id)->delete();

                if ($request->type == 'weight_based') {
                    foreach ($validBrackets as $bracket) {
                        $WeightRate = new ShippingWeightRate();
                        $WeightRate->shipping_method_id = $ShippingMethod->id;
                        $WeightRate->min_weight = (isset($bracket['min_weight']) && $bracket['min_weight'] !== '') ? $bracket['min_weight'] : 0;
                        $WeightRate->max_weight = (isset($bracket['max_weight']) && $bracket['max_weight'] !== '') ? $bracket['max_weight'] : null;
                        $WeightRate->rate = $bracket['rate'];
                        $WeightRate->save();
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => ['title' => translate("Something went wrong. Please try again.")]]);
            }

            return response()->json([$status => $message, 'error' => false, 'inputs' => $request->all()]);
        }

        return view('admin.shipping-method.store', compact('common', 'get_shipping_method', 'weight_rates'));
    }

    // Delete Shipping Method
    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $get_shipping_method = ShippingMethod::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_shipping_method) {
            $get_shipping_method->is_delete = 1;
            $get_shipping_method->save();
        }
        return back()->withErrors(["success" => translate("Delete Successfully")]);
    }

}
