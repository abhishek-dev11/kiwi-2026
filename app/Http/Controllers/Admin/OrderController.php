<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Products;
use App\Models\ProducSubtVariantItems;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderMeta;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Stripe\Stripe;
use Stripe\Refund;
use Stripe\Exception\ApiErrorException;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    // Orders in these statuses are final - the admin can no longer change them.
    const TERMINAL_STATUSES = ['Completed', 'Order Cancelled', 'Refunded'];

    // Order List
    public function index(Request $request){
        $common = array();
        $common['title'] = 'Orders';
        $common['main_menu']           = 'orders';
        $common['submain_menu']        = 'order';
        $common['filter_order_id']     = "";
        $common['filter_order_status'] = "";
        $getOrders = Order::orderBy('id', 'desc');

        $filterFields = ['filter_order_id', 'filter_order_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        if($common['filter_order_id']){
            $getOrders->where('id',$common['filter_order_id']);
        }
        if($common['filter_order_status']){
            $getOrders->where('order_status',$common['filter_order_status']);
        }
        $getOrders =  $getOrders->paginate(config('adminconfig.records_per_page'));

        // Batch-fetch sub_total/total meta for every order on this page instead of
        // running 2 lookup queries per row (was up to 40 extra queries per page of 20).
        $orderMetaByOrder = OrderMeta::whereIn('order_id', $getOrders->pluck('id'))
            ->whereIn('meta_key', ['sub_total', 'total'])
            ->get()
            ->groupBy('order_id');

        $orders = array();
        foreach ($getOrders as $value) {
            $meta = $orderMetaByOrder->get($value->id, collect());
            $order = array();
            $order['id']             = $value->id;
            $order['created_at']     = $value->created_at;
            $order['full_name']      = trim($value->billing_first_name.' '.$value->billing_last_name);
            $order['payment_method'] = $value->payment_method;
            $order['order_status']   = $value->order_status;
            $order['sub_total']      = optional($meta->firstWhere('meta_key', 'sub_total'))->meta_value ?? 0;
            $order['total']          = optional($meta->firstWhere('meta_key', 'total'))->meta_value ?? 0;
            $order['status_color']   = $this->orderStatusColor($value->order_status);
            $orders[] = $order;
        }

        return view('admin.order.index', compact('common','orders','getOrders'));
    }

    private function orderStatusColor($status){
        return match ($status) {
            'Completed' => 'table-success',
            'Order Cancelled', 'Refunded' => 'table-danger',
            'Returned' => 'table-warning',
            default => '',
        };
    }

    public function details(Request $request, $id = ""){
        $common = array();
        $common['title']        = 'Order Details';
        $common['main_menu']    = 'orders';
        $common['submain_menu'] = 'order';
        if ($id != "") {
            if (checkDecrypt($id) == false) {
                return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
            }

            $id     = checkDecrypt($id);
            $Orders = Order::with(['Orderproducts'])->where('id', $id)->first();

            if (!$Orders) {
                return back()->withErrors(["error" => translate("Something went wrong")]);
            }
            $get_order_total = OrderMeta::where(['order_id' => $id])->get();

            return view('admin.order.details', compact('common','Orders','get_order_total'));
        }else{
            return back()->withErrors(["error" => translate("Something went wrong")]);
        }
    }

    public function invoice(Request $request, $id = ""){
        if ($id == "") {
            return back()->withErrors(["error" => translate("Something went wrong")]);
        }

        if (checkDecrypt($id) == false) {
            return redirect()->back()->withErrors(["error" => translate("No Record Found")]);
        }

        $id    = checkDecrypt($id);
        $order = Order::where('id', $id)->first();

        if (!$order) {
            return back()->withErrors(["error" => translate("Something went wrong")]);
        }

        $order_products = OrderProduct::where('order_id', $id)->get();

        $meta_rows  = OrderMeta::where('order_id', $id)->get();
        $order_meta = [];
        foreach ($meta_rows as $meta) {
            $order_meta[$meta->meta_key] = $meta->meta_value;
        }

        $billing_country = Country::find($order->billing_country_id);
        $billing_state    = State::find($order->billing_state_id);
        $billing_city     = City::find($order->billing_city_id);

        $invoice_no = 'KW-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('front.order.invoice', compact(
            'order',
            'order_products',
            'order_meta',
            'billing_country',
            'billing_state',
            'billing_city',
            'invoice_no'
        ))
            // Product images are absolute S3 URLs - DomPDF blocks remote fetches by
            // default, which is why they rendered as blank/alt-text instead of images.
            ->setOption('isRemoteEnabled', true)
            ->setHttpContext(stream_context_create([
                'http'  => ['timeout' => 5],
                'https' => ['timeout' => 5],
            ]))
            ->setPaper('a4', 'portrait');

        return $pdf->download('invoice-' . $invoice_no . '.pdf');
    }

    public function updateOrderStatus(Request $request, $id=""){
        $req_fields = array(
            'order_status' => 'required|in:'.implode(',', getOrderStatus(false)),
        );
        $errormsg = array(
            'order_status' => "Status",
        );
        $validator = Validator::make(
            $request->all(),
            $req_fields,
            [
                'required' => 'The :attribute field is required.',
                'in'       => 'The selected :attribute is invalid.',
            ],
            $errormsg
        );
        if ($validator->fails()) {
            $validator->errors()->add('error', 'Something is wrong with required field!');
            return back()->withErrors($validator)->withInput();
        }

        $order = Order::whereId($id)->first();
        if (!$order) {
            return back()->withErrors(["error" => translate("Something went wrong!")]);
        }

        if (in_array($order->order_status, self::TERMINAL_STATUSES)) {
            return back()->withErrors(["error" => translate("This order is already ".$order->order_status." and can no longer be updated.")]);
        }

        $newStatus = $request->order_status;

        if ($newStatus == 'Order Cancelled') {
            return $this->cancelOrder($order);
        }

        $order->order_status = $newStatus;
        $order->save();
        return back()->withErrors(["success" => translate("Order updated successfully!")]);
    }

    // Cancelling an order restocks every line item and, if the order was already
    // paid via Stripe, refunds the payment - COD orders (never charged) are just
    // marked cancelled. The Stripe call is made before the DB transaction since it's
    // an external side effect that must not be silently rolled back.
    private function cancelOrder(Order $order){
        $orderProducts = OrderProduct::where('order_id', $order->id)->get();

        $refundError = null;
        if ($order->payment_method == 'stripe' && !empty($order->payment_intent_id)) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $refund = Refund::create(['payment_intent' => $order->payment_intent_id]);

                $order->refund_id    = $refund->id;
                $order->refunded_at  = now();
                $order->payment_status = 'refunded';
                $order->order_status = 'Refunded';
            } catch (ApiErrorException $e) {
                Log::error("Order [{$order->id}]: Stripe refund failed - ".$e->getMessage());
                $refundError = $e->getMessage();
                $order->order_status = 'Order Cancelled';
            }
        } else {
            $order->order_status = 'Order Cancelled';
        }

        DB::transaction(function () use ($order, $orderProducts) {
            foreach ($orderProducts as $item) {
                $this->restockOrderItem($item);
            }
            $order->save();
        });

        if ($refundError) {
            return back()->withErrors(["error" => translate("Order cancelled, but the Stripe refund failed: ".$refundError." Please process the refund manually.")]);
        }

        return back()->withErrors(["success" => translate("Order cancelled successfully!")]);
    }

    private function restockOrderItem(OrderProduct $item){
        if (!empty($item->product_variant_id)) {
            $variantStockQuery = ProducSubtVariantItems::where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id);
            if (!empty($item->product_sub_variant_id)) {
                $variantStockQuery->where('product_sub_variant_id', $item->product_sub_variant_id);
            } else {
                $variantStockQuery->whereNull('product_sub_variant_id');
            }
            $variantStock = $variantStockQuery->first();

            if ($variantStock) {
                $variantStock->stock = $variantStock->stock + $item->quantity;
                if ($variantStock->stock > 0 && $variantStock->status == 'inactive') {
                    $variantStock->status = 'active';
                }
                $variantStock->save();
            }
        } else {
            $product = Products::where('id', $item->product_id)->first();
            if ($product) {
                $product->quantity = $product->quantity + $item->quantity;
                $product->save();
            }
        }
    }
}
