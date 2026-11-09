@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Update User
@endsection

@push('css')
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            {{ trans('Update User') }}
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ trans('Manage Users') }}</a></li>
        <li class="breadcrumb-item active">{{ trans('Update User') }}</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark">Update User</h4>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">
                            <i class="fa-solid fa-users me-1"></i> All Users
                        </a>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name', $user->name) }}" id="name" placeholder="Enter Name"
                                                autofocus required>
                                            @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control"
                                                value="{{ old('email', $user->email) }}" id="email" placeholder="Enter Email"
                                                autofocus required>
                                            @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" name="phone" class="form-control"
                                                value="{{ old('phone', $user->phone) }}" id="phone" placeholder="Enter Phone Number"
                                                autofocus>
                                            @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                            <div class="usr-field-group">
                                                <select name="role" id="role" class="form-control" autofocus required>
                                                    <option value="" disabled>Select Role</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}"
                                                            {{ old('role', $user->hasRole($role->name) ? $role->name : '') == $role->name ? 'selected' : '' }}>
                                                            {{ $role->name }}
                                                        </option>
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

                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Update</button>
                            </form>
                        </div>
                        <div class="col-12 col-lg-6 d-none d-lg-flex justify-content-center align-items-center">
                            <img src="{{ asset('backend/assets/images/create_user.jpg') }}" class="w-100 rounded"
                                style="max-width: 88%;" alt="Update user">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('backend.user.partials.quick_add_role', ['roleSelectId' => 'role'])
@endsection

@push('js')
    <script>
        $('#settings').addClass('active');
        $('#settings-submenu').addClass('d-block');
        $('.manage-users-menu').addClass('active');
    </script>
@endpush
