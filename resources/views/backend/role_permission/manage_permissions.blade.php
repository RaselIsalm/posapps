
@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - {{ trans('file.manage_permissions') }}
@endsection

@push('css')
@endpush

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title'){{ trans('file.manage_permissions') }}@endslot
    @slot('subtitle'){{ trans('file.manage_permissions_subtitle') }}@endslot
<li class="breadcrumb-item"><a href="{{ route('roles.index') }}">{{ trans('file.roles') }}</a></li>
<li class="breadcrumb-item active">{{ $role->name }}</li>
@endcomponent

<div class="row layout-top-spacing">
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ $role->name }} — {{ trans('file.manage_permissions') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('roles.updatePermissions', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-4">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="custom-control custom-checkbox d-flex align-items-center">
                                <input type="checkbox" class="custom-control-input" id="select-all" onchange="select_all(this)">
                                <label class="custom-control-label mb-0 ms-2" for="select-all">{{ trans('file.select_all') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($permissions as $permission)
                        <div class="col-2 mb-2">
                            <div class="custom-control custom-checkbox d-flex align-items-center">
                                <input type="checkbox" class="custom-control-input" id="{{ $permission->name }}" name="permissions[]" value="{{ $permission->name }}" @if(in_array($permission->name, $rolePermissions)) checked @endif>
                                <label class="custom-control-label mb-0 ms-2" for="{{ $permission->name }}">{{ ucwords(str_replace('_', ' ', $permission->name)) }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ trans('file.save') }}</button>
            </form>
        </div>
    </div>
</div>

</div>
@endsection 

@push('js')
<script>
    $('#settings').addClass('active');
    $('#settings-submenu').addClass('d-block');
    $('.role-settings-menu').addClass('active');

    function select_all(el) {
        var checkboxes = document.getElementsByName('permissions[]');
        for(var i=0; i < checkboxes.length; i++) {
            checkboxes[i].checked = el.checked;
        }
    }
</script>
@endpush
