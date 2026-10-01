<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ShippingSetting;

class ShippingSettingController extends Controller
{

    // Show / Update the single business shipping-origin address row
    public function edit(Request $request)
    {
        $common = array();
        $common['title']        = 'Shipping Settings';
        $common['main_menu']    = 'shipping';
        $common['submain_menu'] = 'shipping_settings';

        if ($request->isMethod('post')) {

            $req_fields = array();
            $req_fields['origin_company_name']   = "nullable|max:255";
            $req_fields['origin_street_address'] = "nullable|max:255";
            $req_fields['origin_city']           = "nullable|max:255";
            $req_fields['origin_region']         = "nullable|max:255";
            $req_fields['origin_postcode']       = "nullable|max:20";
            $req_fields['origin_country']        = "nullable|max:100";

            $errormsg = array();
            $errormsg['origin_company_name']   = translate("Company Name");
            $errormsg['origin_street_address'] = translate("Street Address");
            $errormsg['origin_city']           = translate("City");
            $errormsg['origin_region']         = translate("Region");
            $errormsg['origin_postcode']       = translate("Postcode");
            $errormsg['origin_country']        = translate("Country");

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                return redirect(route('admin.shipping-settings'))->withErrors($validation)->withInput();
            }

            // ShippingSetting::current() resolves the singleton row (id = 1) if it
            // already exists, or a fresh unsaved instance otherwise - assign the id
            // explicitly (not mass-assignable) so a first-ever save inserts as id 1
            // instead of relying on 'id' being fillable.
            $shipping_setting = ShippingSetting::current();
            $shipping_setting->id                    = 1;
            $shipping_setting->origin_company_name   = $request->origin_company_name;
            $shipping_setting->origin_street_address = $request->origin_street_address;
            $shipping_setting->origin_city           = $request->origin_city;
            $shipping_setting->origin_region         = $request->origin_region;
            $shipping_setting->origin_postcode       = $request->origin_postcode;
            $shipping_setting->origin_country        = $request->origin_country;
            $shipping_setting->save();

            return redirect(route('admin.shipping-settings'))->withErrors(["success" => translate("Shipping settings updated successfully.")]);
        }

        $get_shipping_setting = ShippingSetting::current();

        return view('admin.shipping-settings.edit', compact('common', 'get_shipping_setting'));
    }

}
