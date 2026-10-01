<?php

$leftMenu = [];

/*======= Dashboard ==========*/

$leftMenu['dashboard']['title'] = translate('Dashboard');
$leftMenu['dashboard']['url'] = route('admin.dashboard');
$leftMenu['dashboard']['icon'] = 'tf-icons bx bx-home-circle';

/*======= Catalogs ==========*/
$leftMenu['catalogs']['title'] = translate('Catalogs');
$leftMenu['catalogs']['url'] = 'javascript:void(0);';
$leftMenu['catalogs']['icon'] = 'menu-icon tf-icons bx bx-package';

$leftSubMenu = [];
$leftSubMenu['product']['title'] = translate('Products');
$leftSubMenu['product']['url'] = route('admin.products');
$leftSubMenu['product']['icon'] = '';

$leftSubMenu['products_import']['title'] = translate('Products Import');
$leftSubMenu['products_import']['url'] = route('admin.productsimport');
$leftSubMenu['products_import']['icon'] = '';

$leftSubMenu['attribute']['title'] = translate('Attributes');
$leftSubMenu['attribute']['url'] = route('admin.attributes');
$leftSubMenu['attribute']['icon'] = 'bx bx-grid-alt bx-sm';

$leftMenu['catalogs']['submenu'] = $leftSubMenu;

/*======= Categories ==========*/
$leftMenu['categories']['title'] = translate('Categories');
$leftMenu['categories']['url'] = 'javascript:void(0);';
$leftMenu['categories']['icon'] = 'menu-icon tf-icons bx bx-category';

$leftSubMenu = [];
$leftSubMenu['topcategory']['title'] = translate('Top Level');
$leftSubMenu['topcategory']['url'] = route('admin.topcategories');
$leftSubMenu['topcategory']['icon'] = 'bx bx-grid-alt bx-sm';

$leftSubMenu['midcategory']['title'] = translate('Mid Level');
$leftSubMenu['midcategory']['url'] = route('admin.midcategories');
$leftSubMenu['midcategory']['icon'] = 'bx bx-grid-alt bx-sm';

$leftSubMenu['bottomcategory']['title'] = translate('Bottom Level');
$leftSubMenu['bottomcategory']['url'] = route('admin.bottomcategories');
$leftSubMenu['bottomcategory']['icon'] = 'bx bx-grid-alt bx-sm';

$leftSubMenu['subbottomcategory']['title'] = translate('Fourth Level');
$leftSubMenu['subbottomcategory']['url'] = route('admin.subbottomcategories');
$leftSubMenu['subbottomcategory']['icon'] = 'bx bx-grid-alt bx-sm';

$leftMenu['categories']['submenu'] = $leftSubMenu;

/*======= Suppliers ==========*/
$leftMenu['suppliers']['title'] = translate('Suppliers');
$leftMenu['suppliers']['url'] = route('admin.suppliers');
$leftMenu['suppliers']['icon'] = 'menu-icon tf-icons bx bx-store-alt';

/*======= Coupons ==========*/
$leftMenu['coupons']['title'] = translate('Coupons');
$leftMenu['coupons']['url'] = route('admin.coupons');
$leftMenu['coupons']['icon'] = 'menu-icon tf-icons bx bx-purchase-tag-alt';

/*======= Shipping ==========*/
$leftMenu['shipping']['title'] = translate('Shipping');
$leftMenu['shipping']['url'] = 'javascript:void(0);';
$leftMenu['shipping']['icon'] = 'menu-icon tf-icons bx bxs-truck';

$leftSubMenu = [];
$leftSubMenu['shipping_methods']['title'] = translate('Shipping Methods');
$leftSubMenu['shipping_methods']['url'] = route('admin.shipping-methods');
$leftSubMenu['shipping_methods']['icon'] = 'bx bx-grid-alt bx-sm';

$leftSubMenu['shipping_settings']['title'] = translate('Shipping Settings');
$leftSubMenu['shipping_settings']['url'] = route('admin.shipping-settings');
$leftSubMenu['shipping_settings']['icon'] = 'bx bx-map-pin bx-sm';

$leftMenu['shipping']['submenu'] = $leftSubMenu;

/*======= Customers ==========*/
$leftMenu['customers_list']['title'] = translate('Customer');
$leftMenu['customers_list']['url'] = 'javascript:void(0);';
$leftMenu['customers_list']['icon'] = 'menu-icon tf-icons bx bx-group';

$leftSubMenu = [];
$leftSubMenu['customers']['title'] = translate('Customers');
$leftSubMenu['customers']['url'] = route('admin.customers');
$leftSubMenu['customers']['icon'] = 'menu-icon tf-icons bx bx-collection';

$leftSubMenu['trade_customers']['title'] = translate('Trade Customers');
$leftSubMenu['trade_customers']['url'] = route('admin.tradecustomers');
$leftSubMenu['trade_customers']['icon'] = 'bx bx-grid-alt bx-sm';

$leftSubMenu['customergroups']['title'] = translate('Customer Groups');
$leftSubMenu['customergroups']['url'] = route('admin.customergroups');
$leftSubMenu['customergroups']['icon'] = 'menu-icon tf-icons bx bx-collection';


$leftMenu['orders']['title'] = translate('Order');
$leftMenu['orders']['url'] = route('admin.order');
$leftMenu['orders']['icon'] = 'menu-icon tf-icons bx bx-cart-alt';
$leftMenu['customers_list']['submenu'] = $leftSubMenu;


$leftMenu['content']['title'] = translate('Content');
$leftMenu['content']['url'] = 'javascript:void(0);';
$leftMenu['content']['icon'] = 'menu-icon tf-icons bx bx-file';
$leftSubMenu = [];
$leftSubMenu['pages']['title'] = translate('Pages');
$leftSubMenu['pages']['url'] = route('admin.pages');
$leftSubMenu['pages']['icon'] = 'menu-icon tf-icons bx bx-collection';
$leftMenu['content']['submenu'] = $leftSubMenu;

/*======= Contact Enquiries ==========*/
$leftMenu['contact_enquiries']['title'] = translate('Contact Enquiries');
$leftMenu['contact_enquiries']['url'] = route('admin.contact-enquiries');
$leftMenu['contact_enquiries']['icon'] = 'menu-icon tf-icons bx bx-envelope';


?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo12 " style="margin: 20px 0;justify-content: center;">
        <a href="#" class="app-brand-link" style="display: inline-block;">
            <span class="app-brand-logo demo">
                <?php 
                    $company_logo_height = "height: 65px;";
                    $company_logo = asset('assets/images/logo.png');
                ?>
                <img style=" width: 100%;" src="{{ $company_logo }}" />
                
            </span>
            <!-- <div>
                <div class="company_title">Kiwi Business Products</div>
                <div class="company_tag_line"></div>
            </div> -->
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">

        <!-- Dashboards -->
        @foreach ($leftMenu as $key => $value)
            @php
                $hasSubMenu = 0;
            @endphp
            @if (isset($value['submenu']))
                @php
                    $hasSubMenu = 1;
                @endphp
            @endif

            {{-- menu-item active open --}}
            <li class="menu-item {{ $key == $common['main_menu'] ? 'active open' : '' }} @if ($hasSubMenu) pcoded-hasmenu @endif ">
                <a href="{{ $value['url'] }}" class="menu-link {{ $hasSubMenu ? ' menu-toggle' : '' }}">
                    <i class="menu-icon {{ $value['icon'] }}"></i>
                    <div data-i18n="Dashboards">{{ $value['title'] }}</div>
                </a>

                @isset($value['submenu'])
                    <ul class="menu-sub">
                        @php
                            $count = 1;
                        @endphp
                        @foreach ($value['submenu'] as $subKey => $subValue)
                            <li class="menu-item {{ $subKey == $common['submain_menu'] ? 'active' : '' }}">
                                <a href="{{ $subValue['url'] }}" class="menu-link">
                                    <div data-i18n="CRM">{{ $subValue['title'] }}</div>
                                </a>
                            </li>
                            @php
                                $count++;
                            @endphp
                        @endforeach
                    </ul>
                @endisset
            </li>
        @endforeach
    </ul>
</aside>
