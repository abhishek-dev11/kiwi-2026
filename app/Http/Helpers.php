<?php
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;
use App\Models\User;
use App\Models\Customers;
use App\Models\CustomerGroups;
use App\Models\Categories;
use App\Models\Products;
use App\Models\ProductGallery;
use App\Models\Attributes;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use App\Models\ProductSubVariants;
use App\Models\ProducSubtVariantItems;
use App\Models\Pagemeta;
use Carbon\Carbon;




if (!function_exists('getOrderStatus')) {
    // Matches the `order_status` enum on kw_orders. 'Refunded' is set automatically
    // by the system (after a successful Stripe refund on cancel), never picked
    // manually, so it's excluded unless $includeSystemStatuses is true.
    function getOrderStatus($includeSystemStatuses = true) {
        $Order_status = ['Pending','Shipped','Delivered','Completed','Returned','Order Cancelled'];
        if ($includeSystemStatuses) {
            $Order_status[] = 'Refunded';
        }
        return $Order_status;
    }
}

if (!function_exists('getBusinessDiscountPercentage')) {

    function getBusinessDiscountPercentage()
    {
        static $discountPercentage = null;
        if ($discountPercentage !== null) {
            return $discountPercentage;
        }
        $discountPercentage = 0;
        if (Auth::guard('customers')->check()) {
            $customer = Auth::guard('customers')->user();
            if ($customer->customer_type == 'business') {
                $CustomerGroups = CustomerGroups::select('discount_percentage')
                    ->where(ConditionQuery())
                    ->where('id', $customer->customer_group_id)
                    ->first();

                $discountPercentage = $CustomerGroups->discount_percentage ?? 0;
            }
        }
        return $discountPercentage;
    }
}

if (!function_exists('calculateProductPrice')) {

    function calculateProductPrice($regularPrice, $discountPrice = null)
    {
        $finalPrice = $discountPrice ?: $regularPrice;

        $discountPercentage = getBusinessDiscountPercentage();

        if ($discountPercentage > 0) {

            $finalPrice -= (
                $finalPrice * $discountPercentage
            ) / 100;
        }

        return $finalPrice;
    }
}


// GST rate applied storewide (checkout totals + product Excl./Incl. GST display).
// All prices stored on products (regular_price/discount_price/variant price) are
// GST-exclusive - this is the single place the rate is defined.
if (!function_exists('getGstRate')) {
    function getGstRate()
    {
        return 0.15;
    }
}

if (!function_exists('calculateGstAmount')) {
    function calculateGstAmount($amount)
    {
        return round($amount * getGstRate(), 2);
    }
}

if (!function_exists('calculatePriceInclGst')) {
    function calculatePriceInclGst($amount)
    {
        return round($amount + calculateGstAmount($amount), 2);
    }
}

if (!function_exists('custome_Breadcrumbs')) {
    function custome_Breadcrumbs($parent_slug = null, $child_slug = null, $sub_child_slug = null, $fourth_slug = null)
    {
        $breadcrumbs = [];

        // Parent Category
        if ($parent_slug) {
            $parent = Categories::where('slug', $parent_slug)->first();
            if ($parent) {
                $breadcrumbs[] = [
                    'slug'  => $parent_slug,
                    'title' => $parent->title,
                    'route' => 'mid-level-category',
                    'params' => [
                        'slug' => $parent_slug
                    ]
                ];
            }
        }

        // Child Category
        if ($child_slug) {
            $child = Categories::where('slug', $child_slug)->first();
            if ($child) {
                $breadcrumbs[] = [
                    'slug'  => $child_slug,
                    'title' => $child->title,
                    'route' => 'bottom-level-category',
                    'params' => [
                        'parent_slug' => $parent_slug,
                        'child_slug'  => $child_slug
                    ]
                ];
            }
        }

        // Sub Child Category
        if ($sub_child_slug) {
            $sub_child = Categories::where('slug', $sub_child_slug)->first();
            if ($sub_child) {
                $breadcrumbs[] = [
                    'slug'  => $sub_child_slug,
                    'title' => $sub_child->title,
                    'route' => 'products',
                    'params' => [
                        'parent_slug' => $parent_slug,
                        'child_slug'  => $child_slug,
                        'sub_child_slug' => $sub_child_slug,
                    ]
                ];
            }
        }

        if ($fourth_slug) {
            $fourth = Categories::where('slug', $fourth_slug)->first();
            if ($fourth) {
                $breadcrumbs[] = [
                    'slug'  => $fourth_slug,
                    'title' => $fourth->title,
                    'route' => 'fourth-level-products',
                    'params' => [
                        'parent_slug' => $parent_slug,
                        'child_slug'  => $child_slug,
                        'sub_child_slug' => $sub_child_slug,
                        'fourth_slug' => $fourth_slug,
                    ]
                ];
            }
        }

        return $breadcrumbs;
    }
}


if (!function_exists('print_die')) {
    function print_die($arr = [])
    {
        echo '<pre>';
        print_r($arr);
        echo '<pre/>';
        die();
    }
}

// get Tablee Column
if (!function_exists('getTableColumn')) {
    function getTableColumn($table)
    {
        $tableColumn = Schema::getColumnListing($table);
        foreach ($tableColumn as $TC) {
            $ColumnArr[$TC] = '';
        }
        return $ColumnArr;
    }
}

// Decrypt Data
if (!function_exists('checkDecrypt')) {
    function checkDecrypt($el)
    {
        try {
            return Crypt::decrypt($el);
        } catch (DecryptException $e) {
            return false;
        }
    }
}

// get Languages
if (!function_exists('translate')) {
    function translate($el)
    {
        return $el;
    }
}

// Append Status Badge
if (!function_exists('checkStatus')) {
    function checkStatus($status)
    {
        if ($status == 'Active' || $status == 'active' || $status == 'verified' || $status == 'success' || $status == 'Success' || $status == 'SUCCESS' ||
            $status == "Credited" || $status == "Resolved" || $status == "Closed") {
            return "<span class='badge bg-label-success'>" . $status . '</span>';
        } elseif ($status == 'Draft' || $status == 'draft' || $status == "Pending" || $status == "In Progress") {
            return "<span class='badge bg-label-warning'>" . $status . '</span>';
        } elseif ($status == 'Deactive' ) {
            $status = 'In Active';
            return "<span class='badge bg-label-danger'>" . $status . '</span>';
        } else {
            return "<span class='badge bg-label-danger'>" . $status . '</span>';
        }
    }
}

// Get Selected VAlue
if (!function_exists('getSelected')) {
    function getSelected($option, $value)
    {
        if ($option == $value) {
            return ' selected';
        }
    }
}

// Get Checked VAlue
if (!function_exists('getChecked')) {
    function getChecked($option, $value)
    {
        if ($option == $value) {
            return ' checked';
        }
    }
}

// Get PRoduct Settting Value
if (!function_exists('getSelectedInArray')) {
    function getSelectedInArray($value, $array)
    {
        if (!is_array($array)) {
            $array = explode(',', $array);
        }

        if (in_array($value, $array)) {
            return ' selected';
        }
    }
}

if (!function_exists('get_image_upload')) {
    function get_image_upload($img){
        return url('uploads/'.$img);
    }
}

// Resolve a product/gallery/variant image that may be either a locally-uploaded
// filename or an already-absolute external URL (e.g. imported from a supplier API).
// Passing an absolute URL through get_image_upload() would wrongly prefix it with
// the local uploads path, producing a broken image src.
if (!function_exists('resolve_product_image')) {
    function resolve_product_image($image, $folder = 'Product', $placeholder = 'placeholder/product/image.png')
    {
        if (empty($image)) {
            return asset($placeholder);
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return get_image_upload($folder . '/' . $image);
    }
}



// A variable product's colour/style variant (product_variant_id) can have its own
// uploaded photo in product_variant_images - cart/order line items must show that
// photo instead of the base product image, otherwise every colour of the same
// product looks identical in the cart, order history, and invoices.
if (!function_exists('resolve_cart_item_image')) {
    function resolve_cart_item_image($productId, $productVariantId, $fallbackImage)
    {
        if ($productVariantId) {
            $variantImage = ProductVariantImages::where('product_id', $productId)
                ->where('product_variant_id', $productVariantId)
                ->value('imageUrl');

            if ($variantImage) {
                return resolve_product_image($variantImage, 'ProductVariant');
            }
        }

        return resolve_product_image($fallbackImage);
    }
}

if (!function_exists('image_upload')) {
    function image_upload($img, $path, $oldImage = null){
        if ($oldImage) {
            image_delete($path.'/'.$oldImage);
        }
        $random_no  = uniqid();
        $ext        = $img->getClientOriginalExtension();
        $new_name   = time() . '_' . $random_no . '.' . $ext;
        $destinationPath = public_path('uploads/'.$path);
        $upload_path = $img->move($destinationPath, $new_name);
        return $new_name;
    }
}

if (!function_exists('image_delete')) {
    function image_delete($image_name){
        if ($image_name != '') {
            $image_path = public_path($image_name);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
}

 

if (!function_exists('updateSessionFilters')) {
    function updateSessionFilters(Request $request, array $filterFields, array $common)
    {
        foreach ($filterFields as $field) {
            if ($request->isMethod('post')) {
                if ($request->has('reset')) {
                    Session::forget($field);
                }

                if ($request->has('is_filter')) {
                    Session::put($field, $request->$field);
                }
            }
            if (session($field)) {
                $common[$field] = session($field);
            }
        }
        return $common;
    }
}

if (!function_exists('get_price_format')) {
    function get_price_format($price)
    {
        $price_exp = explode('.', $price);
        if (count($price_exp) > 1) {
            $price = $price_exp[0];
            if (strlen($price_exp[1]) == 1) {
                $price .= '.' . $price_exp[1] . "0";
            } else {
                $price .= '.' . substr($price_exp[1], 0, 2);
            }
        } else {
            $price = $price . ".00";
        }
        return $price;
    }
}

if (!function_exists('createSlug')) {
    function createSlug($table, $title, $id = 0)
    {
        $slug = url_slug($title);
        $allSlugs = getRelatedSlugs($table, $slug, $id);

        if (!$allSlugs->contains('slug', $slug)) {
            return $slug;
        }
        $i = 1;
        $is_contain = true;
        do {
            $newSlug = $slug . '-' . $i;
            if (!$allSlugs->contains('slug', $newSlug)) {
                $is_contain = false;
                return $newSlug;
            }
            $i++;
        } while ($is_contain);
    }
}

if (!function_exists('url_slug')) {
    function url_slug($str, $options = [])
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        // $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = [
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => [],
            'transliterate' => false,
        ];

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = [
            // Latin
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ő' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ű' => 'U',
            'Ý' => 'Y',
            'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ő' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ű' => 'u',
            'ý' => 'y',
            'þ' => 'th',
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A',
            'Β' => 'B',
            'Γ' => 'G',
            'Δ' => 'D',
            'Ε' => 'E',
            'Ζ' => 'Z',
            'Η' => 'H',
            'Θ' => '8',
            'Ι' => 'I',
            'Κ' => 'K',
            'Λ' => 'L',
            'Μ' => 'M',
            'Ν' => 'N',
            'Ξ' => '3',
            'Ο' => 'O',
            'Π' => 'P',
            'Ρ' => 'R',
            'Σ' => 'S',
            'Τ' => 'T',
            'Υ' => 'Y',
            'Φ' => 'F',
            'Χ' => 'X',
            'Ψ' => 'PS',
            'Ω' => 'W',
            'Ά' => 'A',
            'Έ' => 'E',
            'Ί' => 'I',
            'Ό' => 'O',
            'Ύ' => 'Y',
            'Ή' => 'H',
            'Ώ' => 'W',
            'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a',
            'β' => 'b',
            'γ' => 'g',
            'δ' => 'd',
            'ε' => 'e',
            'ζ' => 'z',
            'η' => 'h',
            'θ' => '8',
            'ι' => 'i',
            'κ' => 'k',
            'λ' => 'l',
            'μ' => 'm',
            'ν' => 'n',
            'ξ' => '3',
            'ο' => 'o',
            'π' => 'p',
            'ρ' => 'r',
            'σ' => 's',
            'τ' => 't',
            'υ' => 'y',
            'φ' => 'f',
            'χ' => 'x',
            'ψ' => 'ps',
            'ω' => 'w',
            'ά' => 'a',
            'έ' => 'e',
            'ί' => 'i',
            'ό' => 'o',
            'ύ' => 'y',
            'ή' => 'h',
            'ώ' => 'w',
            'ς' => 's',
            'ϊ' => 'i',
            'ΰ' => 'y',
            'ϋ' => 'y',
            'ΐ' => 'i',
            // Turkish
            'Ş' => 'S',
            'İ' => 'I',
            'Ç' => 'C',
            'Ü' => 'U',
            'Ö' => 'O',
            'Ğ' => 'G',
            'ş' => 's',
            'ı' => 'i',
            'ç' => 'c',
            'ü' => 'u',
            'ö' => 'o',
            'ğ' => 'g',
            // Russian
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'Yo',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'J',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sh',
            'Ъ' => '',
            'Ы' => 'Y',
            'Ь' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sh',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye',
            'І' => 'I',
            'Ї' => 'Yi',
            'Ґ' => 'G',
            'є' => 'ye',
            'і' => 'i',
            'ї' => 'yi',
            'ґ' => 'g',
            // Czech
            'Č' => 'C',
            'Ď' => 'D',
            'Ě' => 'E',
            'Ň' => 'N',
            'Ř' => 'R',
            'Š' => 'S',
            'Ť' => 'T',
            'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c',
            'ď' => 'd',
            'ě' => 'e',
            'ň' => 'n',
            'ř' => 'r',
            'š' => 's',
            'ť' => 't',
            'ů' => 'u',
            'ž' => 'z',
            // Polish
            'Ą' => 'A',
            'Ć' => 'C',
            'Ę' => 'e',
            'Ł' => 'L',
            'Ń' => 'N',
            'Ó' => 'o',
            'Ś' => 'S',
            'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A',
            'Č' => 'C',
            'Ē' => 'E',
            'Ģ' => 'G',
            'Ī' => 'i',
            'Ķ' => 'k',
            'Ļ' => 'L',
            'Ņ' => 'N',
            'Š' => 'S',
            'Ū' => 'u',
            'Ž' => 'Z',
            'ā' => 'a',
            'č' => 'c',
            'ē' => 'e',
            'ģ' => 'g',
            'ī' => 'i',
            'ķ' => 'k',
            'ļ' => 'l',
            'ņ' => 'n',
            'š' => 's',
            'ū' => 'u',
            'ž' => 'z',
        ];

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, $options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8'), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }
}

if (!function_exists('getRelatedSlugs')) {
    function getRelatedSlugs($table, $slug, $id = 0)
    {
        return DB::table($table)
            ->select('slug')
            ->where('slug', 'like', '%' . $slug . '%')
            ->where('id', '<>', $id)
            ->get();
    }
}

if (!function_exists('ConditionQuery')) {
    function ConditionQuery($val = '', $column = '')
    {
        return $arr = [
            'status' => 'Active',
            'is_delete' => null,
        ];
    }
}

// Number of items a visitor sees after clicking a category:
// its sub-categories when it has any, otherwise its products.
if (!function_exists('categoryDisplayCount')) {
    function categoryDisplayCount(Categories $category): int
    {
        $children = Categories::whereNull('is_delete');

        if (is_null($category->top_level_category_id)) {
            $children->where('top_level_category_id', $category->id)->whereNull('mid_level_category_id');
        } elseif (is_null($category->mid_level_category_id)) {
            $children->where('mid_level_category_id', $category->id)->whereNull('bottom_level_category_id');
        } elseif (is_null($category->bottom_level_category_id)) {
            $children->where('bottom_level_category_id', $category->id);
        } else {
            $children = null;
        }

        $childCount = $children ? $children->count() : 0;
        if ($childCount > 0) {
            return $childCount;
        }

        return Products::where(ConditionQuery())
            ->where(function ($query) use ($category) {
                $query->where('category', $category->id);

                if (is_null($category->top_level_category_id)) {
                    $query->orWhere('top_level_category_id', $category->id);
                } elseif (is_null($category->mid_level_category_id)) {
                    $query->orWhere('mid_level_category_id', $category->id);
                } elseif (is_null($category->bottom_level_category_id)) {
                    $query->orWhere('bottom_level_category_id', $category->id);
                }
            })
            ->distinct('id')
            ->count('id');
    }
}

if (!function_exists('get_date_format')) {
    function get_date_format($date){
        $date = date("Y-m-d H:i:s",strtotime($date));
        $date = Carbon::parse($date);        
        $currentDate = Carbon::now();
        $duration = $date->diff($currentDate);
        $date_new = '';
        if ($duration->y) {
            if ($duration->y==1) {
                $date_new = $duration->y.' year ago';
            }else{
                $date_new = $duration->y.' years ago';
            }
        }else{
            if ($duration->m) {
                if ($duration->m==1) {
                    $date_new = $duration->m.' month ago';
                }else{
                    $date_new = $duration->m.' months ago';
                }
            }else{
                if ($duration->d) {
                    if ($duration->d==1) {
                        $date_new = $duration->d.' day ago';
                    }else{
                        $date_new = $duration->d.' day ago';
                    }
                }else{
                    if ($duration->h) {
                        if ($duration->h==1) {
                            $date_new = $duration->h.' hours ago';
                        }else{
                            $date_new = $duration->h.' hour ago';
                        }
                    }else{
                        if ($duration->i) {
                            if ($duration->i==1) {
                                $date_new = $duration->i.' minutes ago';
                            }else{
                                $date_new = $duration->i.' minute ago';
                            }
                        }else{
                            $date_new = 'just now';
                        }
                    }
                }
            }
        }
        return $date_new;
    }
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol(){   
       return '$';
    }
}

function getPagemeta($arr,$page_id,$url=false){
    $data = array();
    foreach ($arr as $key => $value) {
       $data[$value] = '';
    }

    if ($page_id) {
        $Pagemeta =  Pagemeta::where(['page_id' => $page_id])->whereIn('meta_key', $arr)->get();
    }else{
        $Pagemeta =  Pagemeta::whereNull('page_id')->whereIn('meta_key', $arr)->get();
    }
    foreach ($Pagemeta as $key => $value) {
        if ($value->type=='File' AND $url) {
            //$data[$value->meta_key] = asset('uploads/pages').'/'.$value->meta_value;
            $data[$value->meta_key] = get_image_upload($value->meta_value);
        }else{
            $data[$value->meta_key] = $value->meta_value;
        }
    }
    return $data;
}