 <!-- OVERLAY -->
<div class="cart_overlay" onclick="toggleCart()"></div>
<!-- CART SIDEBAR -->
<div class="cart_sidebar" id="cartSidebar">
    <div class="cart_header">
        <h2>Shopping Cart</h2>
        <span onclick="toggleCart()">
            <img src="{{ asset('assets/dist/images/shopcrossicon.svg') }}">
         </span>
    </div>

    <div class="cart_body" id="sidebar-cart-items" >
        @include('front.cart.cart-items')
    </div>

    <div class="cart_footer">
        <div class="shopping_cartbtn">
            <a href="{{ route('cart') }}" class="view_cart">View Cart</a>
            <a href="{{ route('checkout') }}" class="checkout">Checkout</a>
        </div>
    </div>

</div>