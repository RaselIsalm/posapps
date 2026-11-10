@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Create Branch
@endsection

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Create Branch
        @endslot
        @slot('subtitle')
            Create and manage your branch locations effectively.
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Branches</a></li>
        <li class="breadcrumb-item active">Create</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Create Branch</h4>
                            <a href="{{ route('branches.index') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-list"></i> Branch List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('branches.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Name"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="manager_id" class="form-label">Manager <span class="text-danger">*</span></label>
                                <div class="input-group br-field-group">
                                    <select class="form-control" id="manager_id" name="manager_id" required>
                                        <option value="" disabled {{ old('manager_id') ? '' : 'selected' }}>Select Manager</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ (string) old('manager_id') === (string) $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('manage_users')
                                        <button class="btn btn-outline-primary btn-br-add" type="button"
                                            onclick="openBrManagerModal()" title="Add Manager">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                                @error('manager_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                <div class="input-group br-field-group">
                                    <select class="form-control" id="account_id" name="account_id" required>
                                        <option value="" disabled {{ old('account_id') ? '' : 'selected' }}>Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ (string) old('account_id') === (string) $account->id ? 'selected' : '' }}>
                                                {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button class="btn btn-outline-primary btn-br-add" type="button"
                                            onclick="openBrAccountModal()" title="Add Account">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                                @error('account_id')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone (Mobile)</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Mobile"
                                    value="{{ old('phone') }}">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="telephone" class="form-label">Telephone (Landline)</label>
                                <input type="text" class="form-control" id="telephone" name="telephone"
                                    placeholder="Landline" value="{{ old('telephone') }}">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="bin_number" class="form-label">BIN No</label>
                                <input type="text" class="form-control" id="bin_number" name="bin_number"
                                    placeholder="BIN number" value="{{ old('bin_number') }}">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                                    value="{{ old('email') }}">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="text" class="form-control" id="website" name="website"
                                    placeholder="https://example.com" value="{{ old('website') }}">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Address"
                                    value="{{ old('address') }}" required>
                                @error('address')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="branch_logo" class="form-label">Branch Logo</label>
                                <input type="file" class="form-control" id="branch_logo" name="branch_logo"
                                    accept="image/*">
                                @error('branch_logo')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label class="form-label d-block" for="is_active">Is Active</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input switch-size-lg" type="checkbox" checked role="switch"
                                        id="is_active" name="is_active" style="width: 50px; height: 25px;">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('branches.index') }}" class="btn btn-danger">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('backend.branch.partials.quick_add')
@endsection
