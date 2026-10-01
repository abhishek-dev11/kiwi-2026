<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0   d-xl-none ">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    @php
        $get_user = App\Models\User::where('id', auth()->user()->id)->first();
    @endphp

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse" style="gap: 20px;justify-content: space-between;">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <span class="header_shop_name">Welcome Kiwi Business Products!</span>
            </div>
        </div>
    

        <div class="d-flex align-items-center" style="gap: 15px;">
           
            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">

                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">

                        <div class="avatar avatar-online">

                            @php
                                $asset = asset('assets/img/avatars/1.png');
                                if ($get_user->image != '') {
                                    $asset = url('uploads/users', $get_user->image);
                                }
                            @endphp

                            <img src="{{ $asset }}" alt class="w-px-40  h-auto rounded-circle">

                        </div>

                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>

                            <a class="dropdown-item" href="#">

                                <div class="d-flex">

                                    <div class="flex-shrink-0 me-3">

                                        <div class="avatar avatar-online">

                                            <img src="{{ $asset }}" alt class="w-px-40 h-auto rounded-circle">

                                        </div>

                                    </div>

                                    <div class="flex-grow-1">

                                        <span class="fw-medium d-block">{{ $get_user['name'] }}</span>

                                        <small class="text-muted">Admin</small>

                                    </div>

                                </div>

                            </a>

                        </li>

                        <li>

                            <div class="dropdown-divider"></div>

                        </li>

                        <li>

                            <a class="dropdown-item" href="{{ route('admin.profile') }}">

                                <i class="bx bx-user me-2"></i>

                                <span class="align-middle">My Profile</span>

                            </a>

                        </li>

                        <li>

                            <div class="dropdown-divider"></div>

                        </li>

                        <li>

                            <a class="dropdown-item" href="{{ route('admin.logout') }}">

                                <i class="bx bx-power-off me-2"></i>

                                <span class="align-middle">Log Out</span>

                            </a>

                        </li>

                    </ul>
                </li>
                <!--/ User -->
            </ul>
        </div>
        

    </div>

</nav>