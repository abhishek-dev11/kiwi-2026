<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Pages;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        View::composer('*', function ($view) {
            $get_carts = collect();
            $cart_count = 0;

            if (Auth::guard('customers')->check()) {
                $customer = Auth::guard('customers')->user();
                $get_carts = Cart::with('product')
                    ->where('customer_id', $customer->id)
                    ->latest()
                    ->get();

                $cart_count = $get_carts->count();
            }

            $view->with([
                'get_carts'    => $get_carts,
                'cart_count'   => $cart_count,
                'siteSettings' => $this->siteSettings(),
            ]);
        });
    }

    // Global site settings (logos, favicon, contact info, social links) managed
    // via the admin "Website Settings" page (Pages.slug = 'setting'). Shared with
    // every view so header/footer can render them without every controller having
    // to fetch them individually. Memoized so the '*' composer firing repeatedly
    // per request (once per rendered view/partial) only queries the DB once.
    private function siteSettings(): array
    {
        static $settings = null;

        if ($settings !== null) {
            return $settings;
        }

        $page = Pages::where('slug', 'setting')->first();

        $data = getPagemeta([
            'header_logo',
            'favicon',
            'footer_top_logo',
            'footer_top_text',
            'footer_phone_4_text',
            'footer_phone_4',
            'footer_email_4_text',
            'footer_email_4',
            'footer_copyright_text',
            'facebook_link',
            'twitter_link',
            'linkedin_link',
            'instagram_link',
        ], $page->id ?? null);

        // File-type values are stored as bare filenames under uploads/pages —
        // build the full URL here (getPagemeta's own $url flag omits that folder).
        $data['header_logo_url']     = $data['header_logo']    ? get_image_upload('pages/' . $data['header_logo'])    : asset('assets/dist/images/head-logo.png');
        $data['favicon_url']         = $data['favicon']         ? get_image_upload('pages/' . $data['favicon'])         : asset('assets/images/favicon.png');
        $data['footer_top_logo_url'] = $data['footer_top_logo'] ? get_image_upload('pages/' . $data['footer_top_logo']) : asset('assets/dist/images/footer-logowhite.png');

        $settings = $data;

        return $settings;
    }
}
