@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Create User
@endsection

@push('css')
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            {{ trans('Create User') }}
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ trans('Manage Users') }}</a></li>
        <li class="breadcrumb-item active">{{ trans('Create User') }}</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark">Create User</h4>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">
                            <i class="fa-solid fa-users me-1"></i> All Users
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name" value="{{ old('name') }}" autofocus required>
                                            @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" id="email" placeholder="Enter Email" value="{{ old('email') }}" autofocus required>
                                            @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" name="phone" class="form-control" id="phone" placeholder="Enter Phone Number" value="{{ old('phone') }}" autofocus>
                                            @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                            <div class="usr-field-group">
                                                <select name="role" id="role" class="form-control" autofocus required>
                                                    <option value="" disabled selected>Select Role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                                @can('manage_roles')
                                                    <button type="button" class="btn btn-outline-primary btn-usr-add"
                                                        onclick="openUsrRoleModal()" title="Add New Role">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                            @error('role')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password" class="form-control" id="password" placeholder="Enter Password" autofocus required>
                                            @error('password')<span class="text-danger small d-block">{{ $message }}</span>@enderror
                                            <span class="text-danger" id="password_error"></span>
                                            <div class="form-group">
                                                <input type="checkbox" id="show_password" onclick="togglePasswordVisibility()">
                                                <label for="show_password">Show Password</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Enter Confirm Password" autofocus required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Create</button>
                            </form>
                        </div>
                        <div class="col-12 col-lg-6 d-none d-lg-flex justify-content-center align-items-center">
                            <img src="{{ asset('backend/assets/images/create_user.jpg') }}" class="w-100 rounded" style="max-width: 88%;" alt="Create user">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.user.partials.quick_add_role', ['roleSelectId' => 'role'])
@endsection

@push('js')
    <script src="{{ url('backend') }}/assets/js/password.js"></script>
    <script>
        $('#settings').addClass('active');
        $('#settings-submenu').addClass('d-block');
        $('.manage-users-menu').addClass('active');

        $('#password, #password_confirmation').on('keyup', function () {
            var password = $('#password').val();
            var password_confirmation = $('#password_confirmation').val();
            if (password.length < 8) {
                $('#password').removeClass('is-valid').addClass('is-invalid');
                $('#password_error').text(@json(__('Password must be at least 8 characters')));
            } else if (password.search(/[a-z]/i) < 0) {
                $('#password').removeClass('is-valid').addClass('is-invalid');
                $('#password_error').text(@json(__('Password must contain at least one letter')));
            } else if (password.search(/[0-9]/) < 0) {
                $('#password').removeClass('is-valid').addClass('is-invalid');
                $('#password_error').text(@json(__('Password must contain at least one number')));
            } else if (password.search(/[!@#$%^&*]/) < 0) {
                $('#password').removeClass('is-valid').addClass('is-invalid');
                $('#password_error').text(@json(__('Password must contain at least one special character')));
            } else {
                $('#password').removeClass('is-invalid').addClass('is-valid');
                $('#password_error').text('');
            }
            if (password === null || password_confirmation === null) {
                $('#password_confirmation').removeClass('is-invalid is-valid');
            } else if (password === password_confirmation) {
                $('#password_confirmation').removeClass('is-invalid').addClass('is-valid');
            } else {
                $('#password_confirmation').removeClass('is-valid').addClass('is-invalid');
            }
        });
    </script>
@endpush
