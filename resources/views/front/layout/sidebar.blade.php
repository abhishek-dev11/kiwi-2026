 
<div class="order_leftcol">
    <h4>My Account</h4>
    <ul>
        <li class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <a href="{{ route('user.dashboard') }}">
                <img src="{{ asset('assets/dist/images/my-orders.svg') }}">My Orders
            </a>
        </li>
        <li class="{{ request()->routeIs('user.address') ? 'active' : '' }}">
            <a href="{{ route('user.address') }}">
                <img src="{{ asset('assets/dist/images/my-addresses.svg') }}">My Addresses
            </a>
        </li>
        <li class="{{ request()->routeIs('user.wishlist') ? 'active' : '' }}">
            <a href="{{ route('user.wishlist') }}">
                <img src="{{ asset('assets/dist/images/wishlist.svg') }}">My Wishlist
            </a>
        </li>
        <li class="{{ request()->routeIs('user.profile') ? 'active' : '' }}">
            <a href="{{ route('user.profile') }}">
                <img src="{{ asset('assets/dist/images/account-settings.svg') }}">Account Settings
            </a>
        </li>
    </ul>
    <div class="logout_btn">
        <a href="{{ route('user.logout') }}">
            <img src="{{ asset('assets/dist/images/logout.svg') }}">Logout
        </a>
    </div>
</div>