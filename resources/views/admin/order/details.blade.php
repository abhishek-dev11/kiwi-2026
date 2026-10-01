@extends('admin.layout.master')
@section('content')
<style type="text/css">
a.download_personalized {
    position: relative;
    text-align: center;
    display: flex;
    align-items: center;
    flex-direction: column;
    gap: 6px;
}
 
a.download_personalized img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}
</style>
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
      <div class="d-flex flex-column justify-content-center">
         <h5 class="mb-1 mt-3">Order #{{ $Orders['id'] }} <span class="badge bg-label-success me-2 ms-2">{{ strtoupper($Orders['payment_method']) }}</span> <span class="badge bg-label-info">{{ strtoupper($Orders['order_status']) }}</span></h5>
         <p class="text-body">{{ date('F d,Y , H:i:s',strtotime($Orders['created_at'])) }}</p>
      </div>
      <div class="d-flex align-content-center flex-wrap gap-2">
            <a href="{{ route('admin.order') }}" class="btn btn-secondary float-end">
                <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
            </a>
      </div>
   </div> 
   <div class="row">
      <div class="col-12 col-lg-8">
         <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="card-title m-0">Order details</h5>
            </div>
            <div class="card-datatable table-responsive">
               <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                  <table class="datatables-order-details table dataTable no-footer dtr-column" id="DataTables_Table_0">
                     <thead>
                        <tr>
                           <th>#</th>
                           <th class="w-40 sorting_disabled" rowspan="1" colspan="1" aria-label="products">Image</th>
                           <th class="w-40 sorting_disabled" rowspan="1" colspan="1" aria-label="products">Name</th>
                           <th class="w-20 sorting_disabled" rowspan="1" colspan="1" aria-label="price">price</th>
                           <th class="w-20 sorting_disabled" rowspan="1" colspan="1"  aria-label="qty">qty</th>
                           <th class="w-20 sorting_disabled" rowspan="1" colspan="1" aria-label="total">total</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($Orders['Orderproducts'] as $key => $Product)
                           <?php
                              $images = array(); 
                              if ($Product->image) {
                                  $images[] = $Product->image;
                              }
                               
                           ?>
                           <tr class="odd">
                              <td>{{ $key + 1 }}</td>
                              <td>
                                 <div class="images"> 
                                   
                                    <?php if(count($images)>0){  ?>                                                         
                                         <div id="carouselExample{{ $key }}" class="carousel slide_bk" data-bs-ride="carousel">
                                             <div class="carousel-inner">
                                                <?php foreach ($images as $key_1 => $value) { ?>
                                                    <div class="carousel-item <?php if($key_1==0){ ?>  active <?php } ?>" >
                                                        <a href="<?php echo $value; ?>" class="glightbox" data-gallery="gallery{{ $key_1 }}">
                                                            <img src="<?php echo $value; ?>" class="d-block " alt="" style="width: 100px; height: 100px;">
                                                        </a>
                                                    </div>
                                                 <?php } ?>                                                   
                                             </div>
                                         </div>
                                    <?php } ?>
                                 </div> 
                              </td>
                              <td>
                                 {{ strtoupper($Product['title']) }}
                                 @if($Product['attribute_title'] && $Product['product_variant_name'])
                                    <div class="text-muted" style="font-size: 12px;">{{ $Product['attribute_title'] }}: {{ $Product['product_variant_name'] }}</div>
                                 @endif
                                 @if($Product['sub_attribute_title'] && $Product['product_sub_variant_name'])
                                    <div class="text-muted" style="font-size: 12px;">{{ $Product['sub_attribute_title'] }}: {{ $Product['product_sub_variant_name'] }}</div>
                                 @endif
                              </td>
                              <td>
                                 <p style="margin-bottom: 0;"> {{get_currency_symbol()}} <?php echo get_price_format($Product['price']); ?></p>
                              </td>
                              <td>{{ $Product['quantity'] }}</td>
                              <td>{{get_currency_symbol()}} <?php echo get_price_format($Product['quantity'] * $Product['price']); ?></td>
                           </tr>
                           
                        @endforeach
                      
                     </tbody>
                  </table>
               </div>
               <div class="d-flex justify-content-end align-items-center m-3 mb-2 p-1">
                  <div class="order-calculations">
                     <?php
                        $orderTotalLabels = [
                           'sub_total'          => 'Sub Total',
                           'discount'           => 'Discount',
                           'gst'                => 'GST (15%)',
                           'total'              => 'Total',
                           'shipping'           => 'Shipping',
                           'shipping_method_id' => 'Shipping Method',
                           'currency'           => 'Currency',
                        ];
                     ?>
                     @foreach($get_order_total as $value)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="w-px-200">{{ $orderTotalLabels[$value['meta_key']] ?? ucwords(str_replace('_', ' ', $value['meta_key'])) }}:</span>
                            <span class="text-heading">
                                @if($value['meta_key'] == 'currency')
                                    {{ strtoupper($value['meta_value']) }}
                                @elseif($value['meta_key'] == 'shipping_method_id')
                                    @if($value['meta_value'])
                                        {{ \App\Models\ShippingMethod::find($value['meta_value'])?->title ?? ('#' . $value['meta_value']) }}
                                    @else
                                        —
                                    @endif
                                @else
                                    {{ get_currency_symbol() }} {{ get_price_format($value['meta_value']) }}
                                @endif
                            </span>
                        </div>
                     @endforeach
                  </div>
               </div>
            </div>
         </div>
         
      </div>
      <div class="col-12 col-lg-4">
         <div class="card mb-4">
            <div class="card-header d-flex justify-content-between" style="padding-bottom: 10px;">
               <h6 class="card-title m-0">Billing Contact</h6>
            </div>
            <div class="card-body" style="padding-bottom: 10px;">
               <p class="mb-0">{{ $Orders['billing_first_name'] }} {{ $Orders['billing_last_name'] }}</p>
               <p class="mb-0">{{ $Orders['billing_email'] }} </p>
               <p class="mb-0">{{ $Orders['billing_phone'] }} </p>
               <p class="mb-0">{{ $Orders['billing_street_address'] }}</p>
            </div>
            <div class="card-header d-flex justify-content-between" style="padding-top: 10px; padding-bottom: 10px;">
               <h6 class="card-title m-0">Shipping Address</h6>
            </div>
            <div class="card-body" >
               <p class="mb-0">{{ $Orders['shipping_first_name'] }} {{ $Orders['shipping_last_name'] }}</p>
               <p class="mb-0">{{ $Orders['shipping_email'] }} </p>
               <p class="mb-0">{{ $Orders['shipping_phone'] }} </p>
               <p class="mb-0">{{ $Orders['shipping_street_address'] }}</p>
            </div>
         </div>

          
         <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="card-title m-0">Update Order Status</h5>
            </div>
            
            <div class="card-body">
               <form action="{{ route('admin.order.order_status', ['id' => $Orders['id']]) }}" method="post">
                  @csrf
                  <div class="mb-3">
                     <label for="order_status" class="form-label">Order Status <span class="badge bg-label-info">{{ strtoupper($Orders['order_status']) }}</span></label>
                     <?php
                        $getOrderStatus = getOrderStatus(false);
                        $disabled = in_array($Orders['order_status'], ['Completed', 'Order Cancelled', 'Refunded']);
                     ?>
                     <select class="form-select single-select" name="order_status" id="order_status" <?php if ($disabled) { ?> disabled="disabled" <?php }else{ ?> required <?php } ?>>
                        @foreach($getOrderStatus as $OrderStatus)
                        <option value="{{ $OrderStatus }}" {{ getSelected($OrderStatus, $Orders['order_status']) }}>{{ strtoupper($OrderStatus) }}</option>
                        @endforeach
                     </select>
                     @error('order_status')
                        <div
                              class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                              <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}
                              </div>
                        </div>
                     @enderror
                  </div>
                  <button type="submit" class="btn btn-primary" <?php if ($disabled) { ?> disabled="disabled" <?php } ?>>Update Status</button>
               </form>
            </div>
         </div>


        
      </div>
   </div>

   <div class="row">
        <div class="col-md-8"></div>
        <div class="col-md-4 ">
            
        </div>
   </div>
</div>
@endsection
@section('script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
  const lightbox = GLightbox({
    selector: '.glightbox'
  });
</script>
@endsection