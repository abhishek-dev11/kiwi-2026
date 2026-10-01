<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Pages;
use App\Models\ContactEnquiry;
use App\Models\Products;
use App\Models\Categories;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Mail;

class HomeController extends Controller
{
    public function index()
    {
        $page = Pages::where('slug', 'home')->first();
        $data = getPagemeta(['our_featured_products'], $page->id ?? null);
        $featuredIds = json_decode($data['our_featured_products'] ?: '[]', true) ?: [];

        $featuredProducts = Products::where(ConditionQuery())
            ->whereIn('id', $featuredIds)
            ->with(['subVariantItems' => function ($q) {
                $q->where('status', 'active')->where('is_deleted', 0)->orderBy('price', 'asc');
            }])
            ->get()
            ->sortBy(fn($p) => array_search((string) $p->id, $featuredIds))
            ->values();

        $categoryTitles = Categories::whereIn('id', $featuredProducts->pluck('category')->filter()->unique())
            ->pluck('title', 'id');

        $wishlistedIds = [];
        if (Auth::guard('customers')->check()) {
            $wishlistedIds = Wishlist::where('customer_id', Auth::guard('customers')->id())->pluck('product_id')->toArray();
        }

        return view('front.home', compact('featuredProducts', 'categoryTitles', 'wishlistedIds'));
    }

    public function aboutus()
    {
        $page = Pages::where('slug', 'aboutus')->first();
        $keys = [
            'hero_title', 'hero_subtitle', 'hero_text',
            'company_title', 'company_heading', 'company_text', 'company_image_1', 'company_image_2',
            'whocan_heading', 'whocan_text', 'whocan_image',
            'brands_title',
            'brand_image_1', 'brand_image_2', 'brand_image_3', 'brand_image_4', 'brand_image_5', 'brand_image_6', 'brand_image_7',
            'mission_heading', 'mission_text', 'mission_image_1', 'mission_image_2',
        ];
        $data = getPagemeta($keys, $page->id ?? null);

        // File-type values are stored as bare filenames under uploads/pages —
        // build the full URL here (getPagemeta's own $url flag omits that folder).
        $imageKeys = ['company_image_1', 'company_image_2', 'whocan_image', 'mission_image_1', 'mission_image_2', 'brand_image_1', 'brand_image_2', 'brand_image_3', 'brand_image_4', 'brand_image_5', 'brand_image_6', 'brand_image_7'];
        foreach ($imageKeys as $key) {
            $data[$key] = $data[$key] ? get_image_upload('pages/' . $data[$key]) : '';
        }

        $defaults = [
            'hero_title'    => 'About Us',
            'hero_subtitle' => 'Kiwi Business products',
            'hero_text'     => 'We are New Zealand’s Premium Safety Equipment, Hygiene Products, Packaging Products,<br>Cafe Supplies, Promotional Products, Apparel & Stationery specialist',

            'company_title'   => 'Our Company Focus',
            'company_heading' => '"Customer Satisfaction on Time, Every Time"',
            'company_text'    => '<p>Our commitment to outstanding customer service, an extensive product range, specialized advice and offering the complete supply package helps us set the benchmark as one of the industry\'s leading providers of Business Products.</p>'
                .'<p>We offer the total supply package for creating healthier, safer & more efficient workplace environments as well as getting your brand or special gift out to all of your clients</p>'
                .'<p>Kiwi Business Products is a 100% New Zealand Family Owned Business with over 25 years of experience in providing solutions of Safety, Hygiene, Packaging, Cafe, Promotional Products, Apparel and Stationery to our valued clients from all over New Zealand. We have a large network of quality products from local and overseas manufacturers at our disposal with competitive pricing which we pass on to you.</p>',
            'company_image_1' => asset('assets/dist/images/OurCompany-img1.png'),
            'company_image_2' => asset('assets/dist/images/OurCompany-img2.png'),

            'whocan_heading' => 'Who Can Order From Kiwi Business Products?',
            'whocan_text'    => '<p>Private Individuals: Including Self Employed & Home Handy people who wish to keep safe.</p>'
                .'<p>Purchasing Officers: In all types of Business and sizes.</p>'
                .'<p>Commercial and Industrial Clients: Everything including Forestry, Fisheries, Factories, Builders, Workmen and Women, Security, Civil and Road Construction Workers, Timber Mills, Stevedores and Shipping Engineering and Welding as well as any other industry supplying a full range of Business Products.</p>'
                .'<p>Offices and Service Businesses: Requiring Corporate Imaging or Those Special Gifts.</p>',
            'whocan_image' => asset('assets/dist/images/whocan-img.png'),

            'brands_title'   => 'Trending Brands',
            'brand_image_1'  => asset('assets/dist/images/brand-img1.png'),
            'brand_image_2'  => asset('assets/dist/images/brand-img2.png'),
            'brand_image_3'  => asset('assets/dist/images/brand-img3.png'),
            'brand_image_4'  => asset('assets/dist/images/brand-img4.png'),
            'brand_image_5'  => asset('assets/dist/images/brand-img5.png'),
            'brand_image_6'  => asset('assets/dist/images/brand-img6.png'),
            'brand_image_7'  => asset('assets/dist/images/brand-img4.png'),

            'mission_heading' => 'Our Mission Statement',
            'mission_text'    => '<p>Our Mission Statement "To provide a service that is unique by offering a complete one stop business solution to Individuals, SME Enterprises and Corporate Companies, supplying all Safety related Products, Work Wear, Promotional Branding Hygiene, Packaging and Stationery products. We strive to be recognised as the best in our field whilst pursuing opportunities for future developments"</p>',
            'mission_image_1' => asset('assets/dist/images/missionimg2.png'),
            'mission_image_2' => asset('assets/dist/images/missionimg1.png'),
        ];

        foreach ($defaults as $key => $default) {
            if (empty($data[$key])) {
                $data[$key] = $default;
            }
        }

        return view('front.aboutus', compact('data'));
    }

    public function contactus(Request $request)
    {
        $page = Pages::where('slug', 'contactus')->first();
        $keys = [
            'hero_title', 'hero_subtitle',
            'contact_title', 'contact_text', 'contact_phone', 'chat_text',
            'info_title', 'info_address', 'info_phone', 'info_email',
            'contact_image',
            'form_title', 'form_text',
            'map_embed_url',
        ];
        $data = getPagemeta($keys, $page->id ?? null);

        // File-type values are stored as bare filenames under uploads/pages —
        // build the full URL here (getPagemeta's own $url flag omits that folder).
        $data['contact_image'] = $data['contact_image'] ? get_image_upload('pages/' . $data['contact_image']) : '';

        $defaults = [
            'hero_title'    => 'Contact Us',
            'hero_subtitle' => 'Our friendly team is always here <br>to chat.',

            'contact_title' => 'Contact Us',
            'contact_text'  => 'Our friendly team is always here to chat.',
            'contact_phone' => '0272132533',
            'chat_text'     => 'Chat Now',

            'info_title'   => 'Contact Information',
            'info_address' => '8 Rambler Drive, Whakatane',
            'info_phone'   => '0272132533',
            'info_email'   => 'steve@kiwibusinessproducts.co.nz',

            'contact_image' => asset('assets/dist/images/contactus_img.png'),

            'form_title' => 'Contact Form',
            'form_text'  => 'We look forward to hearing from you and will try to respond within one day.',

            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3145.6572320931928!2d176.964194!3d-37.9617887!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6d6efee23acd93b1%3A0xd763376c1319f4b5!2sKiwi%20Business%20Products!5e0!3m2!1sen!2sin!4v1775885714655!5m2!1sen!2sin',
        ];

        foreach ($defaults as $key => $default) {
            if (empty($data[$key])) {
                $data[$key] = $default;
            }
        }

        if ($request->isMethod('post')) {
            $req_fields = [
                'name'    => 'required',
                'email'   => 'required|email',
                'phone'   => 'required',
                'message' => 'required',
            ];
            $errormsg = [
                'name'    => translate('Name'),
                'email'   => translate('Email'),
                'phone'   => translate('Phone'),
                'message' => translate('Message'),
            ];

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                ['required' => 'The :attribute field is required.'],
                $errormsg
            );

            $validation->after(function ($validation) use ($request) {
                if (strtolower(trim((string) $request->capital_answer)) !== 'wellington') {
                    $validation->errors()->add('capital_answer', 'Incorrect answer, please try again.');
                }
            });

            if ($validation->fails()) {
                return response()->json(['error' => true, 'data' => $validation->getMessageBag()->toArray(), 'message' => 'Fill all required field!']);
            }

            $enquiry = new ContactEnquiry();
            $enquiry->name    = $request->name;
            $enquiry->phone   = $request->phone;
            $enquiry->email   = $request->email;
            $enquiry->message = $request->message;
            $enquiry->save();

            try {
                $adminMail = [
                    'subject' => 'New Contact Enquiry - ' . config('app.name'),
                    'email'   => $data['info_email'],
                    'name'    => $enquiry->name,
                    'phone'   => $enquiry->phone,
                    'from_email' => $enquiry->email,
                    'msg'     => $enquiry->message,
                    'page'    => 'email.front.contactus-admin',
                ];
                Mail::send($adminMail['page'], $adminMail, function ($message) use ($adminMail) {
                    $message->to($adminMail['email'])->subject($adminMail['subject']);
                });

                $customerMail = [
                    'subject' => 'We received your message - ' . config('app.name'),
                    'email'   => $enquiry->email,
                    'name'    => $enquiry->name,
                    'msg'     => $enquiry->message,
                    'page'    => 'email.front.contactus-customer',
                ];
                Mail::send($customerMail['page'], $customerMail, function ($message) use ($customerMail) {
                    $message->to($customerMail['email'])->subject($customerMail['subject']);
                });
            } catch (\Throwable $e) {
                Log::error('Contact enquiry email failed: ' . $e->getMessage());
            }

            return response()->json(['error' => false, 'message' => 'Thank you for contacting us! We will get back to you soon.']);
        }

        return view('front.contactus', compact('data'));
    }

    // Static content pages managed via the admin "Pages" screen (kw_pages / kw_page_meta).
    // Each admin edit form (resources/views/admin/pages/page_<slug>.blade.php) saves a
    // title + rich-text field under its own meta_key pair — mapped here per page.
    public function privacyPolicy()
    {
        return $this->staticPage('privacypolicy', 'privacypolicy_title', 'privacypolicy_text', 'Privacy Policy');
    }

    public function shippingPolicy()
    {
        return $this->staticPage('shipping_policy', 'testimonials_title', 'testimonials_text', 'Shipping Policy');
    }

    public function termsConditions()
    {
        $page  = Pages::where('slug', 'termscondition')->first();
        $data  = getPagemeta(['termscondition_sections'], $page->id ?? null);

        $sections   = json_decode($data['termscondition_sections'] ?: '[]', true) ?: [];
        $pageTitle  = 'Terms & Conditions';

        return view('front.terms-conditions', compact('pageTitle', 'sections'));
    }

    public function returnsPolicy()
    {
        return $this->staticPage('returns-policy', 'testimonials_title', 'testimonials_text', 'Returns Policy');
    }

    public function disclaimer()
    {
        $page  = Pages::where('slug', 'disclaimer')->first();
        $data  = getPagemeta(['disclaimer_sections'], $page->id ?? null);

        $sections   = json_decode($data['disclaimer_sections'] ?: '[]', true) ?: [];
        $pageTitle  = 'Disclaimer';

        return view('front.disclaimer', compact('pageTitle', 'sections'));
    }

    private function staticPage(string $dbSlug, string $titleKey, string $textKey, string $fallbackTitle)
    {
        $page = Pages::where('slug', $dbSlug)->first();
        $data = getPagemeta([$titleKey, $textKey], $page->id ?? null);

        $pageTitle   = $data[$titleKey] ?: $fallbackTitle;
        $pageContent = $data[$textKey];

        return view('front.policy-page', compact('pageTitle', 'pageContent'));
    }
}