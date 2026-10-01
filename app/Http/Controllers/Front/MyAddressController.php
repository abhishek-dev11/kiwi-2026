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
use App\Models\Customers;
use App\Models\CustomerAddress;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
 
class MyAddressController extends Controller
{

    public function index()
    {
        $customer      = Auth::guard('customers')->user();
        $get_countries = Country::whereNull('is_delete')->orderBy('id', 'desc')->get();
        $get_addresses = CustomerAddress::where('customer_id', $customer->id)
                                        ->latest()
                                        ->paginate(config('adminconfig.records_per_page'));

        // Build address array with state/city names
        $address_arrs = [];
        foreach ($get_addresses as $addr) {
            $state_name = State::where('id', $addr->state)->value('name') ?? '';
            $city_name  = City::where('id', $addr->city)->value('name')  ?? '';

            $address_arrs[] = [
                'id'             => $addr->id,
                'address_title'  => $addr->address_title,
                'first_name'     => $addr->first_name,
                'last_name'      => $addr->last_name,
                'email'          => $addr->email,
                'phone'          => $addr->phone,
                'company_name'   => $addr->company_name,
                'country'        => $addr->country,
                'state'          => $addr->state,
                'state_name'     => $state_name,
                'city'           => $addr->city,
                'city_name'      => $city_name,
                'street_address' => $addr->street_address,
                'pincode'        => $addr->pincode,
                'is_default'     => $addr->is_default_address,
            ];
        }

        return view('front.myaddress', compact(
            'get_countries',
            'get_addresses',
            'address_arrs'
        ));
    }

    public function get_address_detail(Request $request)
    {
        $customer = Auth::guard('customers')->user();

        $address = CustomerAddress::where('id', $request->address_id)
                                  ->where('customer_id', $customer->id)
                                  ->first();

        if (!$address) {
            return response()->json(['error' => true, 'message' => 'Address not found']);
        }

        return response()->json([
            'error'          => false,
            'first_name'     => $address->first_name,
            'last_name'      => $address->last_name,
            'email'          => $address->email,
            'phone'          => $address->phone,
            'company_name'   => $address->company_name,
            'country'        => $address->country,
            'state'          => $address->state,
            'city'           => $address->city,
            'pincode'        => $address->pincode ?? '',
            'street_address' => $address->street_address,
        ]);
    }

    public function update_address(Request $request)
    {
        if ($request->isMethod('post')) {
            $customer   = Auth::guard('customers')->user();
            $address_id = $request->address_id;

            $address = CustomerAddress::where('id', $address_id)
                                      ->where('customer_id', $customer->id)
                                      ->first();
            if (!$address) {
                return response()->json(['error' => true, 'message' => 'Address not found']);
            }

            // Validation — email unique except current record
            $req_fields = [
                'billing_address_title'  => 'required',
                'billing_first_name'     => 'required',
                'billing_last_name'      => 'required',
                //'billing_email'          => 'required|email|unique:customer_address,email,' . $address_id,
                'billing_email'          => 'required|email',
                'billing_phone'          => 'required',
                'billing_country_id'     => 'required',
                'billing_state_id'       => 'required',
                'billing_city_id'        => 'required',
                'billing_street_address' => 'required',
                'billing_pincode'        => 'required',
            ];

            $errormsg = [
                'billing_address_title'  => translate('Address Title'),
                'billing_first_name'     => translate('First Name'),
                'billing_last_name'      => translate('Last Name'),
                'billing_email'          => translate('Email'),
                'billing_phone'          => translate('Phone'),
                'billing_country_id'     => translate('Country'),
                'billing_state_id'       => translate('State'),
                'billing_city_id'        => translate('City'),
                'billing_street_address' => translate('Street address'),
                'billing_pincode'        => translate('Pin Code'),
            ];

            $validation = Validator::make($request->all(), $req_fields,
                ['required' => 'The :attribute field is required.'],
                $errormsg
            );

            if ($validation->fails()) {
                return response()->json(['error' => $validation->getMessageBag()->toArray()]);
            }

            $address->address_title  = $request->billing_address_title;
            $address->first_name     = $request->billing_first_name;
            $address->last_name      = $request->billing_last_name;
            $address->email          = $request->billing_email;
            $address->phone          = $request->billing_phone;
            $address->company_name   = $request->billing_company_name ?? '';
            $address->country        = $request->billing_country_id;
            $address->state          = $request->billing_state_id;
            $address->city           = $request->billing_city_id;
            $address->street_address = $request->billing_street_address;
            $address->pincode        = $request->billing_pincode;
            $address->save();

            return response()->json(['success' => translate('Updated Successfully'), 'error' => false]);
        }
    }

    public function delete_address(Request $request)
    {
        $customer = Auth::guard('customers')->user();
        $address  = CustomerAddress::where('id', $request->address_id)
                                   ->where('customer_id', $customer->id)
                                   ->first();
        if (!$address) {
            return response()->json(['error' => true, 'message' => 'Not found']);
        }

        $was_default = $address->address_title == 'Default Address';
        $address->delete();

        // If deleted was default, assign default to next available
        if ($was_default) {
            $next = CustomerAddress::where('customer_id', $customer->id)->first();
            if ($next) {
                $next->address_title = 'Default Address';
                $next->save();
            }
        }

        return response()->json(['error' => false, 'message' => translate('Deleted Successfully')]);
    }

    public function set_default_address(Request $request)
    {
        $customer = Auth::guard('customers')->user();

        // Remove default from all
        CustomerAddress::where('customer_id', $customer->id)
                       ->update(['is_default_address' => null]);

        // Set new default
        CustomerAddress::where('id', $request->address_id)
                       ->where('customer_id', $customer->id)
                       ->update(['is_default_address' => 'yes']);

        return response()->json(['error' => false, 'message' => 'Default address updated']);
    }

}
