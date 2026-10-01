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
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderMeta;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class MyOrderController extends Controller
{
    //
    public function index(Request $request)
    {
        $common = array();
        $id =  Auth::guard('customers')->user()->id;
        return view('front.myorder', compact('common'));
    }

    public function orderDetail($order_id)
    {
        $customer = Auth::guard('customers')->user();

        $order_id  = checkDecrypt($order_id);
        // ── Order ─────────────────────────────────────────────────────────────────
        $order = Order::where('id', $order_id)
                      ->where('customer_id', $customer->id)
                      ->firstOrFail();

        // ── Order Products ────────────────────────────────────────────────────────
        $order_products = OrderProduct::where('order_id', $order_id)->get();

        // ── Order Meta (key => value) ─────────────────────────────────────────────
        $meta_rows  = OrderMeta::where('order_id', $order_id)->get();
        $order_meta = [];
        foreach ($meta_rows as $meta) {
            $order_meta[$meta->meta_key] = $meta->meta_value;
        }

        // ── Status History timeline ───────────────────────────────────────────────
        // If you have a status history table use it; otherwise build from order
        $status_history = [
            ['status' => 'Order Created', 'date' => $order->created_at],
        ];
        if ($order->order_status == 'shipped' || $order->order_status == 'delivered') {
            $status_history[] = ['status' => 'Shipped', 'date' => $order->shipped_at   ?? null];
        }
        if ($order->order_status == 'delivered') {
            $status_history[] = ['status' => 'Delivered', 'date' => $order->delivered_at ?? null];
        }

        // ── Location names ────────────────────────────────────────────────────────
        $billing_country  = Country::find($order->billing_country_id);
        $billing_state    = State::find($order->billing_state_id);
        $billing_city     = City::find($order->billing_city_id);
        $shipping_country = Country::find($order->shipping_country_id);
        $shipping_state   = State::find($order->shipping_state_id);
        $shipping_city    = City::find($order->shipping_city_id);

        return view('front.myorderdetail', compact(
            'order',
            'order_products',
            'order_meta',
            'status_history',
            'billing_country',
            'billing_state',
            'billing_city',
            'shipping_country',
            'shipping_state',
            'shipping_city'
        ));
    }
    
    
    public function invoice_old($order_id)
    {
        $common = array();
        $id =  Auth::guard('customers')->user()->id;
        return view('front.myorder', compact('common'));
    }

    public function invoice($order_id)
    {
        $customer = Auth::guard('customers')->user();

        $order_id  = checkDecrypt($order_id);
        // ── Fetch Order ───────────────────────────────────────────────────────────
        $order = Order::where('id', $order_id)
                      ->where('customer_id', $customer->id)
                      ->firstOrFail();

        // ── Fetch Order Products ──────────────────────────────────────────────────
        $order_products = OrderProduct::where('order_id', $order_id)->get();

        // ── Fetch Order Meta ──────────────────────────────────────────────────────
        $meta_rows  = OrderMeta::where('order_id', $order_id)->get();
        $order_meta = [];
        foreach ($meta_rows as $meta) {
            $order_meta[$meta->meta_key] = $meta->meta_value;
        }

        // ── Location Names ────────────────────────────────────────────────────────
        $billing_country  = Country::find($order->billing_country_id);
        $billing_state    = State::find($order->billing_state_id);
        $billing_city     = City::find($order->billing_city_id);

        // ── Invoice Number (padded) ───────────────────────────────────────────────
        $invoice_no = 'KW-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);

        // ── Generate PDF ──────────────────────────────────────────────────────────
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
        // To stream in browser instead:  return $pdf->stream('invoice-' . $invoice_no . '.pdf');
    }
}
