@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <!-- FormValidation -->
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">User Profile</h5>
                    <div class="card-body">

                        <form id="{{ route('admin.profile') }}" method="post" class="row g-3" enctype="multipart/form-data">
                   
                         @csrf
                            <div class="col-md-4">
                                <label class="form-label" for="formValidationName">Full Name</label>
                                <input type="text" id="formValidationName" class="form-control {{ $errors->has('name') ? 'is-invalid':'' }}" placeholder="Enter Name"
                                    name="name" value="{{ $get_user->name }}" />
                                    @error('name')
                                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                            <div data-field="formValidationName" data-validator="notEmpty">{{ $message }}</div>
                                        </div>
                                    @enderror
                            </div>


                            <div class="col-md-4">
                                <label class="form-label" for="formValidationEmail">Email</label>
                                <input readonly class="form-control" type="email" id="formValidationEmail"
                                    placeholder="Email" value="{{ $get_user->email }}" />
                            </div>

                            <div class="col-md-4">
                                <label for="formValidationFile" class="form-label">Profile Pic</label>
                                @php
                                    $asset = asset('assets/img/avatars/1.png');
                                    if($get_user->image != ""){
                                        $asset = url('uploads/users',$get_user->image);
                                    }
                                @endphp
                                <input class="form-control" onchange="loadFile(event,'image_1')" type="file" id="formValidationFile" name="profile_pic"> 
                                <div class="mx-auto mb-3 mt-3" >
                                    <img src="{{ $asset }}" id="image_1" alt="Avatar Image" class="rounded-circle w-px-100">
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="switch switch-primary">
                                        <input type="checkbox" name="is_change_password" {{ old("is_change_password") == 'on'?'checked':'' }} class="switch-input " id="changePasswordCheckbox" >
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on"></span>
                                            <span class="switch-off"></span>
                                        </span>
                                        <span class="switch-label">Change Password</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4 password-field {{ old("is_change_password") == 'on'?'d-block':'d-none' }}">
                                <div class="form-password-toggle">
                                    <label class="form-label" for="formValidationPass">Old Password</label>
                                    <div class="input-group input-group-merge">
                                        <input class="form-control {{ $errors->has('old_password') ? 'is-invalid':'' }}"  type="password" id="formValidationPass"
                                            name="old_password" placeholder=""
                                            aria-describedby="multicol-password2" value="{{ old('old_password') }}" />
                                        <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                                class="bx bx-hide"></i></span>
                                    </div>
                                   
                                    <div class="col-form-alert-label text-danger">
                                        @if ($errors->has('old_password'))
                                            {{ $errors->first('old_password') }}
                                        @endif
                                    </div>   

                                </div>

                            </div>

                            <div class="col-md-4 password-field {{ old("is_change_password") == 'on'?'d-block':'d-none' }}">
                                <div class="form-password-toggle">
                                    <label class="form-label" for="formValidationPass">New Password</label>
                                    <div class="input-group input-group-merge">
                                        <input class="form-control {{ $errors->has('new_password') ? 'is-invalid':'' }}" type="password" id="formValidationPass"
                                            name="new_password" placeholder=""
                                            aria-describedby="multicol-password2" />
                                        <span class="input-group-text cursor-pointer" id="multicol-password2"><i
                                                class="bx bx-hide"></i></span>
                                    </div>
                                   
                                    <div class="col-form-alert-label text-danger">
                                        @if ($errors->has('new_password'))
                                            {{ $errors->first('new_password') }}
                                        @endif
                                    </div>  

                                </div>
                            </div>

                            <div class="col-md-4 password-field {{ old("is_change_password") == 'on'?'d-block':'d-none' }}">
                                <div class="form-password-toggle">
                                    <label class="form-label" for="formValidationConfirmPass">Confirm Password</label>
                                    <div class="input-group input-group-merge">
                                        <input class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid':'' }}" type="password" id="formValidationConfirmPass"
                                            name="password_confirmation" placeholder=""
                                            aria-describedby="multicol-confirm-password2" />
                                        <span class="input-group-text cursor-pointer" id="multicol-confirm-password2"><i
                                                class="bx bx-hide"></i></span>
                                    </div>
                                    
                                    <div class="col-form-alert-label text-danger">
                                        @if ($errors->has('password_confirmation'))
                                            {{ $errors->first('password_confirmation') }}
                                        @endif
                                    </div>  

                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="submitButton" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /FormValidation -->
        </div>
    </div>

    @section('script')  
        <script>
            $(document).ready(function() {
                $('.password-field').addClass('d-block');
                $('#changePasswordCheckbox').change(function() {
                    if ($(this).prop('checked')) {
                        $('.password-field').removeClass('d-none').addClass('d-block');
                    } else {
                        $('.password-field').removeClass('d-block').addClass('d-none');
                    }
                });
            });
        </script>
    @endsection

@endsection

