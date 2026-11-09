@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Edit Customer
@endsection

@push('css')
@endpush

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title')Edit Customer @endslot
    @slot('subtitle')Edit and manage your customer relationships effectively. @endslot
<li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
<li class="breadcrumb-item active">Edit</li>
@endcomponent

<div class="row layout-top-spacing">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12 d-flex justify-content-between">
                        <h4>Edit Customer</h4>
                        <a href="{{ route('customers.index') }}" class="btn btn-primary btn-sm mr-2 float-right">
                            <i class="fa-solid fa-list"></i> Customer List
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{{ $customer->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ $customer->email }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" value="{{ $customer->phone }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Address" value="{{ $customer->address }}" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="note">Note</label>
                                <input type="text" class="form-control" id="note" name="note" placeholder="Note" value="{{ $customer->note }}">
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="customer_group_id">Customer Group</label>
                                <div class="input-group cg-field-group">
                                    <select class="form-control" id="customer_group_id" name="customer_group_id">
                                        <option value="">Select Group</option>
                                        @foreach ($customer_groups as $customer_group)
                                            <option value="{{ $customer_group->id }}"
                                                {{ (string) $customer->customer_group_id === (string) $customer_group->id ? 'selected' : '' }}>
                                                {{ $customer_group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_customer_groups')
                                        <button type="button" class="btn btn-outline-primary btn-cg-add"
                                            onclick="openCustEditCustomerGroupModal()" title="Add New Customer Group">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="previous_due">Previous Due</label>
                                <input type="number" class="form-control" id="previous_due" name="previous_due" placeholder="Previous Due" value="{{ $customer->previous_due }}">
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <div class="form-group">
                                <label for="status">Is Active</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input switch-size-lg" type="checkbox" role="switch" id="status" name="status" style="width: 50px; height: 25px;" {{ $customer->status == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

@include('backend.customer.partials.quick_add_group')
@endsection 

@push('js')
<script>
    $('#peoples').addClass('active');
    $('#peoples-submenu').addClass('d-block');
    $('.customer-menu').addClass('active');
</script>
@endpush
