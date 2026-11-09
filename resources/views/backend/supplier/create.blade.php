@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Create Supplier
@endsection

@push('css')
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Create Supplier
        @endslot
        @slot('subtitle')
            Create and manage your supplier relationships effectively.
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
        <li class="breadcrumb-item active">Create</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12 d-flex justify-content-between">
                            <h4>Create Supplier</h4>
                            <a href="{{ route('suppliers.index') }}" class="btn btn-primary btn-sm mr-2 float-right">
                                <i class="fa-solid fa-list"></i> Supplier List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('suppliers.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Name" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        placeholder="Phone" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Email" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name"
                                        placeholder="Company Name">
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                        placeholder="Address">
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="note">Note</label>
                                    <input type="text" class="form-control" id="note" name="note"
                                        placeholder="Note">
                                </div>
                            </div>
                            <div class="col-md-4 col-12 mb-3">
                                <div class="form-group">
                                    <label for="previous_due">Previous Due</label>
                                    <input type="number" class="form-control" id="previous_due" name="previous_due"
                                        placeholder="Previous Due">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-12 mb-3">
                            <div class="row">
                                <div class="col-12">
                                    <label for="is_active">Is Active</label>
                                </div>
                                <div class="col-12 text-center">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input switch-size-lg" checked
                                            role="switch" id="is_active" name="is_active"
                                            style="width: 50px; height: 25px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-danger">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('#peoples').addClass('active');
        $('#peoples-submenu').addClass('d-block');
        $('.supplier-menu').addClass('active');
    </script>
@endpush
