<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactEnquiry;

class ContactEnquiryController extends Controller
{
    // Contact Enquiries List
    public function index(Request $request)
    {
        $common = array();
        $common['title']        = 'Contact Enquiries';
        $common['main_menu']    = 'contact_enquiries';
        $common['submain_menu'] = 'contact_enquiries';
        $common['filter_enquiry_name']   = "";
        $common['filter_enquiry_email']  = "";
        $common['filter_enquiry_status'] = "";

        $getEnquiries = ContactEnquiry::whereNull('is_delete')->orderBy('id', 'desc');

        $filterFields = ['filter_enquiry_name', 'filter_enquiry_email', 'filter_enquiry_status'];
        $common = updateSessionFilters($request, $filterFields, $common);

        if ($common['filter_enquiry_name']) {
            $getEnquiries->where('name', 'like', '%' . $common['filter_enquiry_name'] . '%');
        }

        if ($common['filter_enquiry_email']) {
            $getEnquiries->where('email', 'like', '%' . $common['filter_enquiry_email'] . '%');
        }

        if ($common['filter_enquiry_status']) {
            $getEnquiries->where('status', $common['filter_enquiry_status']);
        }

        $getEnquiries = $getEnquiries->paginate(config('adminconfig.records_per_page'));

        return view('admin.contact-enquiries.index', compact('common', 'getEnquiries'));
    }

    // View Single Enquiry (also marks it as Read)
    public function view($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);

        $common = array();
        $common['title']        = 'Enquiry Details';
        $common['main_menu']    = 'contact_enquiries';
        $common['submain_menu'] = 'contact_enquiries';

        $Enquiry = ContactEnquiry::where('id', $id)->whereNull('is_delete')->first();
        if (!$Enquiry) {
            return back()->withErrors(["error" => translate("Something went wrong")]);
        }

        if ($Enquiry->status == 'Pending') {
            $Enquiry->status = 'Resolved';
            $Enquiry->save();
        }

        return view('admin.contact-enquiries.view', compact('common', 'Enquiry'));
    }

    // Delete Enquiry
    public function delete($id)
    {
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $Enquiry = ContactEnquiry::where(['id' => $id])->whereNull('is_delete')->first();
        if ($Enquiry) {
            $Enquiry->is_delete = 1;
            $Enquiry->save();
        }
        return back()->withErrors(['success' => translate('Delete Successfully')]);
    }
}
