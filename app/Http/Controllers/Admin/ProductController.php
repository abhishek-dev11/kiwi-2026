<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Categories;
use App\Models\Products;
use App\Models\ProductGallery;
use App\Models\Attributes;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use App\Models\ProductSubVariants;
use App\Models\ProducSubtVariantItems;
use App\Models\Suppliers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProductImportDispatcherJob;
use App\Jobs\AussiePacificProductImportJob;
use App\Jobs\BanburyProductImportJob;
use App\Jobs\LogoLineProductImportJob;
use App\Jobs\FashionBizProductImportJob;
use App\Jobs\TrendsProductImportJob;
use App\Jobs\PremiumApparelProductImportJob;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{

    // Products List
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Products';
        $common['main_menu']    = 'catalogs';
        $common['submain_menu'] = 'product';
        $common['filter_product_title']        = "";
        $common['filter_product_top_category']     = "";
        $common['filter_product_mid_category']     = "";
        $common['filter_product_bottom_category']     = "";
        $common['filter_product_subbottom_category']     = "";
        $common['filter_product_status']       = "";
        $common['filter_product_supplier']     = "";
        $common['filter_product_type']         = "";


        $Getcategories     = Categories::where(ConditionQuery())->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
        $categories     = array();
        foreach ($Getcategories as $key => $value) {
            $categories[$value->id]     = $value->title;
        }

        $get_suppliers = Suppliers::where(ConditionQuery())->orderBy('full_name', 'asc')->get();

        $filterFields = ['filter_product_title','filter_product_top_category','filter_product_mid_category','filter_product_bottom_category','filter_product_subbottom_category', 'filter_product_status','filter_product_supplier','filter_product_type','is_filter'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $products = Products::whereNull('is_delete')->orderBy('id', 'desc');
        if($common['filter_product_title']){
            $products->where('title','like','%'.$common['filter_product_title'].'%');
        }


        if($common['filter_product_top_category']){
            $products->where('top_level_category_id',$common['filter_product_top_category']);
        }

        if($common['filter_product_mid_category']){
            $products->where('mid_level_category_id',$common['filter_product_mid_category']);
        }
        if($common['filter_product_bottom_category']){
            $products->where(function ($query) use ($common) {
                $query->where('bottom_level_category_id', $common['filter_product_bottom_category'])
                    ->orWhere('category', $common['filter_product_bottom_category']);
            });
        }
        if($common['filter_product_subbottom_category']){
            $products->where('category',$common['filter_product_subbottom_category']);
        }

        if($common['filter_product_status']){
            $products->where('status',$common['filter_product_status']);
        }

        if($common['filter_product_supplier']){
            $products->where('supplier_id',$common['filter_product_supplier']);
        }

        if($common['filter_product_type']){
            $products->where('product_type',$common['filter_product_type']);
        }

        //$products = $products->paginate(config('adminconfig.records_per_page'));
        $products = $products->paginate(20);

        // Batch-fetch every category referenced on this page instead of running 4 lookup
        // queries per row (was up to 80 extra queries per page of 20 products).
        $categoryIds = [];
        foreach ($products as $value) {
            $categoryIds[] = $value->top_level_category_id;
            $categoryIds[] = $value->mid_level_category_id;
            $categoryIds[] = $value->category;
            $categoryIds[] = $value->bottom_level_category_id;
        }
        $categoriesById = Categories::whereIn('id', array_filter(array_unique($categoryIds)))->get()->keyBy('id');

        $supplierIds = $products->pluck('supplier_id')->filter()->unique();
        $suppliersById = Suppliers::whereIn('id', $supplierIds)->get()->keyBy('id');

        $products_list = array();
        foreach ($products as $key => $value) {
            $top_level_category = '-';
            $mid_level_category = '-';
            $bottom_level_category = '-';
            $fourth_level_category = '-';

            if ($get_category = $categoriesById->get($value->top_level_category_id)) {
                $top_level_category = $get_category->title;
            }
            if ($get_category = $categoriesById->get($value->mid_level_category_id)) {
                $mid_level_category = $get_category->title;
            }
            if ($get_category = $categoriesById->get($value->category)) {
                if ($get_category->bottom_level_category_id) {
                    $fourth_level_category = $get_category->title;
                } else {
                    $bottom_level_category = $get_category->title;
                }
            }
            if ($get_category = $categoriesById->get($value->bottom_level_category_id)) {
                $bottom_level_category = $get_category->title;
            }
            $value['top_level_category'] = $top_level_category;
            $value['mid_level_category'] = $mid_level_category;
            $value['bottom_level_category'] = $bottom_level_category;
            $value['fourth_level_category'] = $fourth_level_category;
            $value['supplier_name'] = optional($suppliersById->get($value->supplier_id))->full_name ?? '-';
            $products_list[] = $value;
        }

        $top_categories = Categories::whereNull('is_delete')->where('status', 'Active')->whereNull('top_level_category_id')->whereNull('top_level_category_id')->get();
        return view('admin.product.shop', compact('common', 'products', 'products_list', 'top_categories', 'get_suppliers'));
    }

    // Add Or Update Product
    public function store(Request $request, $id = ""){
        if ($id != "") {          
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }
            $id  = checkDecrypt($id);
            $get_product = Products::where('id', $id)->first();
            
            if (!$get_product) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
        }

        if ($request->isMethod('post')) {

            // A product only counts as "variable" when explicitly set that way AND an
            // attribute family is selected. The attribute/variant fields stay in the DOM
            // (just hidden) when switching back to "simple", so they can still arrive in
            // the request - product_type is the real source of truth, not field presence.
            // Computed once here so validation and the save logic below agree on shape.
            $isVariable = ($request->product_type == 'variable') && $request->filled('attribute_select_id');
            $isSubVariant = $isVariable && $request->has('sub_variant_checkbox_id');

            $req_fields = array();
            $req_fields['title']   = "required";
            $req_fields['quantity']         = "required|int|min:0";
            $req_fields['regular_price']    = "required|decimal:0,2";

            // Weight/dimensions feed the freight/courier rate calculation - kept optional for
            // now so existing products can still be edited before their catalog data is backfilled.
            $req_fields['weight']    = "nullable|numeric|min:0";
            $req_fields['length']    = "nullable|numeric|min:0";
            $req_fields['width']     = "nullable|numeric|min:0";
            $req_fields['height']    = "nullable|numeric|min:0";

            if ($request->id == "") {
                $req_fields['image']    = "required|image|mimes:jpeg,jpg,png,webp|max:5120";
            } else {
                $req_fields['image']    = "nullable|image|mimes:jpeg,jpg,png,webp|max:5120";
            }

            if ($request->filled('discount_price')) {
                $req_fields['discount_price']   = "lt:regular_price|decimal:0,2";
            }

            $req_fields['top_level_category_id']    = "required";
            $req_fields['mid_level_category_id']    = "required";
            //$req_fields['category']               = "required";
            $req_fields['sku']                      = "required";
            $req_fields['files.*']                  = "image|mimes:jpeg,jpg,png,webp|max:5120";
            $req_fields['variant_image.*.*']        = "image|mimes:jpeg,jpg,png,webp|max:5120";

            // Weight/dimensions for a variable product live on the sellable combination
            // (alongside price/stock) since two sizes of the same color can weigh
            // differently. Shape matches price/stock's: nested when sub-variants are on,
            // flat otherwise.
            if ($isSubVariant) {
                $req_fields['item_weight.*.*'] = "nullable|numeric|min:0";
                $req_fields['item_length.*.*'] = "nullable|numeric|min:0";
                $req_fields['item_width.*.*']  = "nullable|numeric|min:0";
                $req_fields['item_height.*.*'] = "nullable|numeric|min:0";
            } else {
                $req_fields['item_weight.*'] = "nullable|numeric|min:0";
                $req_fields['item_length.*'] = "nullable|numeric|min:0";
                $req_fields['item_width.*']  = "nullable|numeric|min:0";
                $req_fields['item_height.*'] = "nullable|numeric|min:0";
            }

            if( $request->product_type == 'variable' ){
                $req_fields['attribute_select_id'] = "required";
                //$req_fields['city.*']   = "required";
            }


            $errormsg = array();
            $errormsg['title'] = translate("Title");
            $errormsg['regular_price'] = translate("Regular Price");
            $errormsg['discount_price'] = translate("Discount Price");
            $errormsg['top_level_category_id'] = translate("Top Category");
            $errormsg['mid_level_category_id'] = translate("Mid Category");
            $errormsg['sku'] = translate("SKU");
            $errormsg['weight'] = translate("Weight");
            $errormsg['length'] = translate("Length");
            $errormsg['width'] = translate("Width");
            $errormsg['height'] = translate("Height");
            if( $request->product_type == 'variable' ){
                $errormsg['attribute_select_id'] = translate("Attribute"); 
            }

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );


            if ($validation->fails()) {
                return response()->json(['error' => $validation->getMessageBag()->toArray() ]);
            }

            try {
                $result = DB::transaction(function () use ($request, $isVariable, $isSubVariant) {

                if ($request->id != "") {
                    $message    = translate("Update Successfully");
                    $status     = "success";
                    $Product = Products::find($request->id);
                } else {
                    $message    = translate("Add Successfully");
                    $status     = "success";
                    $Product = new Products();
                }

                if($request->hasFile('image')){
                    $image = image_upload($request->file('image'), 'Product');
                    $Product->image   = $image;
                }

                $Product->title            = $request->title;
                if ($request->id != "") {
                    $Product->slug = createSlug('products', $request->title, $request->id);
                } else {
                    $Product->slug = createSlug('products', $request->title);
                }
                $Product->regular_price         = $request->regular_price;
                $Product->discount_price        = $request->discount_price;
                $Product->description           = $request->description;
                $Product->short_decripation     = $request->short_decripation;
                $Product->quantity              = $request->quantity;
                $Product->weight                 = $request->filled('weight') ? $request->weight : NULL;
                $Product->length                 = $request->filled('length') ? $request->length : NULL;
                $Product->width                  = $request->filled('width') ? $request->width : NULL;
                $Product->height                 = $request->filled('height') ? $request->height : NULL;
                $Product->top_level_category_id = ($request->top_level_category_id) ? $request->top_level_category_id : NULL;
                $Product->mid_level_category_id = ($request->mid_level_category_id) ? $request->mid_level_category_id : NULL;
                $Product->bottom_level_category_id = ($request->bottom_level_category_id) ? $request->bottom_level_category_id : NULL;
                $Product->category              = ($request->category) ? $request->category : (($request->bottom_level_category_id) ? $request->bottom_level_category_id : NULL);
                $Product->product_type          = ($request->product_type) ? $request->product_type : NULL;
                $Product->status                = $request->status;
                $Product->sku                   = ($request->sku) ? $request->sku : NULL;
                //$Product->is_popular          = ($request->is_popular) ? 1 : NULL;
                $Product->attribute_id          = $isVariable ? $request->attribute_select_id : NULL;
                $Product->sub_variant_attribute_id  = $isSubVariant ? $request->vatiant_attribute_select_id : NULL;
                $Product->save();
                $ProductID = $Product->id;

                if (isset($request->image_id)) {
                    ProductGallery::where('product_id', $ProductID)
                        ->whereNotIn('id', $request->image_id)
                        ->delete();
                } else {
                    ProductGallery::where('product_id', $ProductID)->delete();
                }

                if ($request->hasFile('files')) {
                    $files = $request->file('files');
                    foreach ($files as $fileKey => $image) {
                        $ProductGallery = new ProductGallery();
                        $img = image_upload($image, 'ProductGallery');
                        $ProductGallery->image   = $img;
                        $ProductGallery->product_id = $ProductID;
                        $ProductGallery->save();
                    }
                }

                // Any variant not present in the submitted ids is stale - either the user
                // removed it in the UI, or the product was switched from variable back to
                // simple. Clean it up (and its images/price rows) instead of leaving orphans.
                $submittedVariantIds = $isVariable ? array_filter($request->product_variant_id ?? []) : [];
                $staleVariants = ProductVariants::with('images')
                    ->where('product_id', $ProductID)
                    ->whereNotIn('id', $submittedVariantIds)
                    ->get();

                foreach ($staleVariants as $staleVariant) {
                    foreach ($staleVariant->images as $staleImage) {
                        $filePath = public_path('uploads/ProductVariant/'.$staleImage->imageUrl);
                        if (!empty($staleImage->imageUrl) && file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    ProductVariantImages::where('product_variant_id', $staleVariant->id)->delete();
                    ProducSubtVariantItems::where('product_variant_id', $staleVariant->id)->delete();
                    $staleVariant->delete();
                }

                if (!$isSubVariant) {
                    // Sub variant option turned off (or never on) - drop any leftover sub
                    // variants/price rows from a previous save instead of leaving them stale.
                    ProductSubVariants::where('product_id', $ProductID)->delete();
                    ProducSubtVariantItems::where('product_id', $ProductID)->whereNotNull('product_sub_variant_id')->delete();
                }

                $subVariantIds = [];
                if ($isVariable && isset($request->product_variant_id)) {

                    if ($isSubVariant) {

                        ProductSubVariants::where('product_id', $ProductID)->delete();
                        ProducSubtVariantItems::where('product_id', $ProductID)->delete();

                        if (isset($request->product_sub_variant_id)) {
                            foreach ($request->product_sub_variant_id as $key => $SubvariantId) {
                                $ProductSubVariant = new ProductSubVariants();
                                $ProductSubVariant->value = $request->sub_variant_name[$key] ?? null;
                                $ProductSubVariant->product_id = $ProductID;
                                $ProductSubVariant->save();
                                $subVariantIds[$key] = $ProductSubVariant->id;
                            }
                        }
                    }

                    foreach ($request->product_variant_id as $key => $variantId) {

                        /** -----------------------------------------
                         * 1. CREATE OR UPDATE VARIANT
                         * ----------------------------------------- */
                        if (!empty($variantId)) {
                            $ProductVariant = ProductVariants::find($variantId);
                        } else {
                            $ProductVariant = new ProductVariants();
                        }

                        $ProductVariant->product_id = $ProductID;
                        $ProductVariant->value = $request->variant_name[$key] ?? null;
                        $ProductVariant->attribute_id = $request->attribute_select_id;
                        $ProductVariant->save();

                        $ProductVariantID = $ProductVariant->id;

                        if (!$ProductVariantID) continue;


                        /** -----------------------------------------
                         * 2. GET EXISTING IMAGE IDS FROM FORM
                         * ----------------------------------------- */
                        $existingImages = $request->existing_variant_image_id[$key] ?? [];
                        /** -----------------------------------------
                         * 3. UPLOAD NEW IMAGES
                         * ----------------------------------------- */

                        if ($request->has('image_variant_id')) {
                            // checkbox is checked
                            if (isset($request->variant_image[$key])) {
                                foreach ($request->variant_image[$key] as $image) {
                                    if ($image->isValid()) {
                                        $uploadPath = image_upload($image, 'ProductVariant');
                                        $newImage = new ProductVariantImages();
                                        $newImage->product_id = $ProductID;
                                        $newImage->product_variant_id = $ProductVariantID;
                                        $newImage->imageUrl = $uploadPath;
                                        $newImage->save();
                                        // Add new image ID to existing list
                                        $existingImages[] = $newImage->id;
                                    }
                                }
                            }

                            /** -----------------------------------------
                             * 4. DELETE REMOVED IMAGES (DB + FILE)
                             * ----------------------------------------- */
                            $imagesToDelete = ProductVariantImages::where('product_variant_id', $ProductVariantID)
                                ->whereNotIn('id', $existingImages)
                                ->get();

                            foreach ($imagesToDelete as $img) {

                                // Delete file from storage
                                $filePath = public_path('uploads/ProductVariant/'.$img->imageUrl);

                                if (!empty($img->imageUrl) && file_exists($filePath)) {
                                    unlink($filePath);
                                }

                                // Delete DB record
                                $img->delete();
                            }
                        }

                        if ($isSubVariant) {
                            foreach ($subVariantIds as $subKey => $ProductSubVariantID) {

                                if (isset($request->price[$key][$subKey])) {
                                    ProducSubtVariantItems::updateOrCreate(
                                        [
                                            'product_id' => $ProductID,
                                            'product_variant_id' => $ProductVariantID,
                                            'product_sub_variant_id' => $ProductSubVariantID,
                                        ],
                                        [
                                            'price'  => $request->price[$key][$subKey],
                                            'stock'  => $request->stock[$key][$subKey] ?? 0,
                                            'weight' => ($request->item_weight[$key][$subKey] ?? '') !== '' ? $request->item_weight[$key][$subKey] : NULL,
                                            'length' => ($request->item_length[$key][$subKey] ?? '') !== '' ? $request->item_length[$key][$subKey] : NULL,
                                            'width'  => ($request->item_width[$key][$subKey] ?? '') !== '' ? $request->item_width[$key][$subKey] : NULL,
                                            'height' => ($request->item_height[$key][$subKey] ?? '') !== '' ? $request->item_height[$key][$subKey] : NULL,
                                        ]
                                    );
                                }
                            }
                        } else {
                            ProducSubtVariantItems::updateOrCreate(
                                [
                                    'product_id' => $ProductID,
                                    'product_variant_id' => $ProductVariantID,
                                    'product_sub_variant_id' => null,
                                ],
                                [
                                    'price'  => $request->price[$key] ?? 0,
                                    'stock'  => $request->stock[$key] ?? 0,
                                    'weight' => ($request->item_weight[$key] ?? '') !== '' ? $request->item_weight[$key] : NULL,
                                    'length' => ($request->item_length[$key] ?? '') !== '' ? $request->item_length[$key] : NULL,
                                    'width'  => ($request->item_width[$key] ?? '') !== '' ? $request->item_width[$key] : NULL,
                                    'height' => ($request->item_height[$key] ?? '') !== '' ? $request->item_height[$key] : NULL,
                                ]
                            );
                        }
                    }
                }

                return [
                    'status' => $status,
                    'message' => $message,
                    'subVariantIds' => $subVariantIds,
                ];
                });
            } catch (\Throwable $e) {
                \Log::error('Product save failed: '.$e->getMessage(), ['exception' => $e]);
                return response()->json(['error' => ['title' => [translate('Something went wrong. Please try again.')]] ]);
            }

            return response()->json([$result['status'] => $result['message'], 'error' => false, 'subVariantIds' => $result['subVariantIds'] ]);
        }

        $common = array();
        $common['title']        = 'Product Add';
        $common['main_menu']    = 'catalogs';
        $common['submain_menu'] = 'product';

        $get_product                = getTableColumn('products');
        $get_product_gallery        = [];
        $get_product_variant        = [];
        $get_product_variant_images = [];
        $get_product_sub_variant    = [];
        $get_sub_variant_items      = [];
        
        $top_level_categories     = Categories::where(ConditionQuery())->whereNull('top_level_category_id')->whereNull('mid_level_category_id')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
        
        if ($id != "") {
            $get_product                = Products::where('id', $id)->first();
            $get_product_gallery        = ProductGallery::where('product_id', $id)->get();
            //$get_product_variant        = ProductVariants::where('product_id', $id)->get();
            $get_product_variant        = ProductVariants::with('images')
                                        ->where('product_id', $id)
                                        ->get();
            $get_product_variant_images = ProductVariantImages::where('product_id', $id)->get();
            $get_product_sub_variant = ProductSubVariants::where('product_id', $id)->get();
            $get_sub_variant_items = ProducSubtVariantItems::where('product_id', $id)->get();
        }

        $get_attributes = Attributes::whereNull('is_delete')->orderBy('id', 'desc')->get();
        return view('admin.product.store', compact('common', 'get_product','get_product_gallery','top_level_categories','get_attributes','get_product_variant','get_product_variant_images','get_product_sub_variant','get_sub_variant_items'));
    }

    // Delete Product
    public function delete($id){
        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }
        $id = checkDecrypt($id);
        $status       = 'error';
        $message      = translate('Something went wrong!');
        $get_product =  Products::where(['id' => $id])->whereNull('is_delete')->first();
        if ($get_product) {
            $get_product->is_delete = 1;
            $get_product->save();
        }
        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }  

    public function delete_seleted(Request $request){
        if (isset($request->prodcut_ids)) {
            if (count($request->prodcut_ids)>0) {
               
            }else{
                return back()->withErrors(["error" => translate("Please select at least one product to delete.")]);
            }
        }else{
            return back()->withErrors(["error" => translate("Please select at least one product to delete.")]);
        }
        foreach ($request->prodcut_ids as $key => $value) {
           $get_product =  Products::where(['id' => $value])->first();
           if ($get_product) {
               $get_product->is_delete = 1;
               $get_product->save();
           }
        }

        $status  = 'success';
        $message = translate('Delete Successfully');
        return back()->withErrors([$status => $message]);
    }    

    public function get_child_category(Request $request){
        $html = '<option value="">'.$request->title.'</option>';
        if ($request->isMethod('post')) {
            $getcategory =  Categories::where(['id' => $request->category])->whereNull('is_delete')->first();
            if (!$getcategory) {
                $get_category = collect();
            } elseif ($getcategory->bottom_level_category_id) {
                $get_category = collect();
            } elseif ($getcategory->mid_level_category_id) {
                $get_category = Categories::where(['bottom_level_category_id' => $request->category])->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            } elseif ($getcategory->top_level_category_id) {
                $get_category =  Categories::where(['mid_level_category_id' => $request->category])->whereNull('bottom_level_category_id')->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            }else{
                $get_category =  Categories::where(['top_level_category_id' => $request->category])->whereNull('mid_level_category_id')->whereNull('is_delete')->orderBy('sort_number', 'asc')->orderBy('id', 'desc')->get();
            }
            foreach ($get_category as $key => $value) {
                $html .='<option value="'.$value->id.'" '.getSelected($value->id, $request->child).' >'.$value->title.'</option>';
            }
        }
        echo $html;
    }

    public function getProgress($id)
    {
        return response()->json(
            cache()->get("import_progress_$id")
        );
    }

    public function products_import(Request $request)
    {
        $common = [
            'title'        => 'Products Import',
            'main_menu'    => 'catalogs',
            'submain_menu' => 'products_import',
        ];

        $get_suppliers = Suppliers::where(ConditionQuery())
            ->where('type', 'csv')
            ->orderBy('id', 'desc')
            ->get();

        if ($request->isMethod('post')) {

            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                return response()->json(['status' => false, 'message' => 'Invalid file']);
            }

            $extension = strtolower($request->file('file')->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'xlsx'])) {
                return response()->json(['status' => false, 'message' => 'Please upload a CSV or XLSX file.']);
            }

            \File::ensureDirectoryExists(storage_path('app/imports'));

            $file     = $request->file('file');
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path     = $file->storeAs('imports', $fileName, 'local');

            $importId   = (string) \Str::uuid();
            $supplierId = (int) $request->supplier_id;

            // ── Count valid rows first (fast operation) ──────────
            $rowCount = $this->countImportRows($path);

            $chunkSize   = 500;
            $totalChunks = max(1, (int) ceil($rowCount / $chunkSize));

            // ── Store accurate meta BEFORE dispatching ────────────
            cache()->put("import_meta_{$importId}", [
                'status'           => 'queued',
                'total_chunks'     => $totalChunks,
                'completed_chunks' => 0,
                'total_rows'       => $rowCount,
                'processed_rows'   => 0,
                'percentage'       => 0,
            ], now()->addHours(3));

            // cache()->put("import_chunks_{$importId}", 0, now()->addHours(3));
            // cache()->put("import_rows_{$importId}", 0, now()->addHours(3));

            \Log::info("Controller [{$importId}]: {$rowCount} rows, {$totalChunks} chunks. Dispatching...");

            // ── Dispatch one job per chunk directly ───────────────
            $currentLine = 2; // line 1 = header 

            // Supplier 3 (jbswear), 8 (Augmento), 9 (pH7), and 13 (BMV) each upload their
            // own native column layout rather than the generic template — their
            // *FileImportJob classes remap those columns before the shared
            // ProductImportJob upsert/category/pricing logic runs.
            $importJobClass = match ($supplierId) {
                3       => \App\Jobs\JbswearFileImportJob::class,
                8       => \App\Jobs\AugmentoFileImportJob::class,
                9       => \App\Jobs\Ph7FileImportJob::class,
                12      => \App\Jobs\BmvFileImportJob::class,
                13      => \App\Jobs\BannersInkFileImportJob::class,
                14      => \App\Jobs\JohnBullFileImportJob::class,
                default => \App\Jobs\ProductImportJob::class,
            };


            for ($i = 0; $i < $totalChunks; $i++) {
                $startRow = $currentLine;
                $endRow   = $currentLine + $chunkSize - 1;

                // dispatch(new \App\Jobs\ProductImportJob(
                //     $path,
                //     $importId,
                //     $supplierId,
                //     $startRow,
                //     $endRow,
                //     $totalChunks
                // ))->onQueue('imports');

                dispatch(new $importJobClass(
                    $path,
                    $importId,
                    $supplierId,
                    $startRow,
                    $endRow,
                    $totalChunks
                ))->afterResponse();

                $currentLine = $endRow + 1;

                \Log::info("Controller [{$importId}]: Dispatched chunk " . ($i + 1) . "/{$totalChunks} (lines {$startRow}-{$endRow})");
            }

            return response()->json([
                'status'      => true,
                'import_id'   => $importId,
                'total_rows'  => $rowCount,
                'chunks'      => $totalChunks,
                'message'     => "{$rowCount} rows queued in {$totalChunks} chunks.",
            ]);
        }

        return view('admin.product.import', compact('common', 'get_suppliers'));
    }

    private function countImportRows(string $path): int
    {
        $filePath = Storage::disk('local')->path($path);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rowCount = 0;

        if ($extension === 'csv') {
            $handle = fopen($filePath, 'r');
            $isHeader = true;

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                if (!empty($row[0]) || !empty($row[3])) {
                    $rowCount++;
                }
            }

            fclose($handle);
            return $rowCount;
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($filePath)->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $sku = $sheet->getCell("A{$rowNumber}")->getValue();
            $title = $sheet->getCell("D{$rowNumber}")->getValue();

            if (!empty($sku) || !empty($title)) {
                $rowCount++;
            }
        }

        return $rowCount;
    }

    public function import_progress($importId)
    {
        $meta = cache()->get("import_meta_{$importId}");

        if (!$meta) {
            return response()->json([
                'status'     => 'not_found',
                'percentage' => 0,
                'done'       => false,
                'message'    => 'Import not found or expired.',
            ]);
        }

        $totalChunks     = (int)($meta['total_chunks']     ?? 1);
        $completedChunks = (int)($meta['completed_chunks'] ?? 0);
        $totalRows       = (int)($meta['total_rows']       ?? 0);
        $processedRows   = (int)($meta['processed_rows']   ?? 0);

        // ✅ Percentage based on CHUNKS only
        $percentage = (int) round(($completedChunks / $totalChunks) * 100);
        $percentage = min(100, $percentage);
        $done       = $meta['status'] === 'completed';

        return response()->json([
            'status'           => $meta['status']  ?? 'queued',
            'total_chunks'     => $totalChunks,
            'completed_chunks' => $completedChunks,
            'total_rows'       => $totalRows,
            'processed_rows'   => $processedRows,
            'percentage'       => $percentage,
            'done'             => $done,
            'message'          => $done
                ? "Completed! {$processedRows} rows imported."
                : "Chunk {$completedChunks} of {$totalChunks} completed ({$percentage}%)",
        ]);
    }

    // =========================================================
    // OPTIONAL: Cancel / Clear a stuck import
    // Route: POST /admin/import-cancel/{importId}
    // =========================================================
    public function import_cancel($importId)
    {
        cache()->forget("import_meta_{$importId}");

        return response()->json([
            'status'  => true,
            'message' => 'Import progress cleared.',
        ]);
    }

    // =========================================================
    // AUSSIE PACIFIC API SYNC
    // Route: POST /admin/aussiepacific-import
    // =========================================================
    public function aussiepacific_import(Request $request)
    {
        $importId = $this->startAussiePacificImport();

        return response()->json([
            'status'    => true,
            'import_id' => $importId,
            'chunks'    => 1,
            'message'   => 'Aussie Pacific sync started.',
        ]);
    }

    public function startAussiePacificImport(): string
    {
        // $supplier = Suppliers::firstOrCreate(
        //     ['full_name' => 'Aussiepacific'],
        //     ['email' => 'info@aussiepacific.com', 'status' => 'Active', 'type' => 'csv']
        // );

        $supplier = Suppliers::findOrFail(4);

        $importId = (string) Str::uuid();

        cache()->put("import_meta_{$importId}", [
            'status'           => 'queued',
            'total_chunks'     => 1,
            'completed_chunks' => 0,
            'total_rows'       => 0,
            'processed_rows'   => 0,
            'percentage'       => 0,
        ], now()->addHours(3));

        dispatch(new AussiePacificProductImportJob($importId, $supplier->id, 1))->afterResponse();
        //dispatch(new AussiePacificProductImportJob($importId, $supplier->id, 1))->onQueue('imports');

        \Log::info("Controller [{$importId}]: Aussie Pacific sync dispatched (page 1).");

        return $importId;
    }

     // =========================================================
    // BANBURY API SYNC
    // Route: POST /admin/banbury-import
    // =========================================================
    public function banbury_import(Request $request)
    {
        $importId = $this->startBanburyImport();

        return response()->json([
            'status'    => true,
            'import_id' => $importId,
            'chunks'    => 1,
            'message'   => 'Cloke Clothing sync started.',
        ]);
    }

    public function startBanburyImport(): string
    {
        $supplier = Suppliers::findOrFail(5);

        $importId = (string) Str::uuid();

        cache()->put("import_meta_{$importId}", [
            'status'           => 'queued',
            'total_chunks'     => 1,
            'completed_chunks' => 0,
            'total_rows'       => 0,
            'processed_rows'   => 0,
            'percentage'       => 0,
        ], now()->addHours(3));

        //dispatch(new BanburyProductImportJob($importId, $supplier->id, 1))->onQueue('imports');
        dispatch(new BanburyProductImportJob($importId, $supplier->id, 1))->afterResponse();

        \Log::info("Controller [{$importId}]: Banbury sync dispatched (page 1).");

        return $importId;
    }

    // =========================================================
    // LOGO LINE API SYNC
    // Route: POST /admin/logoline-import
    // =========================================================
    public function logoline_import(Request $request)
    {
        $importId = $this->startLogoLineImport();
        return response()->json([
            'status'    => true,
            'import_id' => $importId,
            'chunks'    => 1,
            'message'   => 'Logo Line sync started.',
        ]);
    }

    public function startLogoLineImport(): string
    {
        $supplier = Suppliers::findOrFail(6);

        $importId = (string) Str::uuid();

        cache()->put("import_meta_{$importId}", [
            'status'           => 'queued',
            'total_chunks'     => 1,
            'completed_chunks' => 0,
            'total_rows'       => 0,
            'processed_rows'   => 0,
            'percentage'       => 0,
        ], now()->addHours(3));

        //cache()->put('logoline_sync_cooldown', true, now()->addMinutes(10));

        //dispatch(new LogoLineProductImportJob($importId, $supplier->id))->onQueue('imports');
        dispatch(new LogoLineProductImportJob($importId, $supplier->id, 1))->afterResponse();

        \Log::info("Controller [{$importId}]: Logo Line sync dispatched.");

        return $importId;
    }

    // =========================================================
    // FASHION BIZ (BIZ COLLECTION) API SYNC
    // Route: POST /admin/fashionbiz-import
    // =========================================================
    public function fashionbiz_import(Request $request)
    {
        $importId = $this->startFashionBizImport();
        return response()->json([
            'status'    => true,
            'import_id' => $importId,
            'chunks'    => 1,
            'message'   => 'Biz Collection sync started.',
        ]);
    }

    public function startFashionBizImport(): string
    {
        $supplier = Suppliers::findOrFail(10);

        $importId = (string) Str::uuid();

        cache()->put("import_meta_{$importId}", [
            'status'           => 'queued',
            'total_chunks'     => 1,
            'completed_chunks' => 0,
            'total_rows'       => 0,
            'processed_rows'   => 0,
            'percentage'       => 0,
        ], now()->addHours(3));

        //dispatch(new FashionBizProductImportJob($importId, $supplier->id, 1))->onQueue('imports');
        dispatch(new FashionBizProductImportJob($importId, $supplier->id, 1))->afterResponse();

        \Log::info("Controller [{$importId}]: Fashion Biz (Biz Collection) sync dispatched (page 1).");

        return $importId;
    }

    // =========================================================
    // TRENDS API SYNC
    // Route: POST /admin/trends-import
    // =========================================================
    public function trends_import(Request $request)
    {
        $importId = $this->startTrendsImport();
        return response()->json([
            'status'    => true,
            'import_id' => $importId,
            'chunks'    => 1,
            'message'   => 'Trends sync started.',
        ]);
    }

    public function startTrendsImport(): string
    {
        $supplier = Suppliers::findOrFail(7);
        $importId = (string) Str::uuid();
        cache()->put("import_meta_{$importId}", [
            'status'           => 'queued',
            'total_chunks'     => 1,
            'completed_chunks' => 0,
            'total_rows'       => 0,
            'processed_rows'   => 0,
            'percentage'       => 0,
        ], now()->addHours(3));

        //dispatch(new TrendsProductImportJob($importId, $supplier->id, 1))->onQueue('imports');
        dispatch(new TrendsProductImportJob($importId, $supplier->id, 1))->afterResponse();

        \Log::info("Controller [{$importId}]: Trends sync dispatched (page 1).");

        return $importId;
    }

    // =========================================================
    // PREMIUM APPAREL API SYNC
    // Route: POST /admin/premiumapparel-import
    // =========================================================
    public function premiumapparel_import(Request $request)
    {
        $importId = $this->startPremiumApparelImport();

        return response()->json([
            'status'    => true,
            'import_id' => $importId,
            'chunks'    => 1,
            'message'   => 'Premium Apparel sync started.',
        ]);
    }

    public function startPremiumApparelImport(): string
    {
        $supplier = Suppliers::findOrFail(11);
        $importId = (string) Str::uuid();
        cache()->put("import_meta_{$importId}", [
            'status'           => 'queued',
            'total_chunks'     => 1,
            'completed_chunks' => 0,
            'total_rows'       => 0,
            'processed_rows'   => 0,
            'percentage'       => 0,
        ], now()->addHours(3));

        //dispatch(new PremiumApparelProductImportJob($importId, $supplier->id))->onQueue('imports');
        dispatch(new PremiumApparelProductImportJob($importId, $supplier->id, 1))->afterResponse();
        \Log::info("Controller [{$importId}]: Premium Apparel sync dispatched.");
        return $importId;
    }


}
