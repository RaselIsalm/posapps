@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Terms & Conditions
@endsection

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title')Terms & Conditions @endslot
    @slot('subtitle')Manage your terms and conditions. @endslot
<li class="breadcrumb-item">Settings</li>
<li class="breadcrumb-item active">Terms & Conditions</li>
@endcomponent
<div class="container-fluid default-dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('settings.termsUpdate') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Terms & Conditions List</h5>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-primary btn-sm add-term" style="font-weight: 500;">
                                    <i class="fas fa-plus"></i> Add Row
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="terms_table">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th style="width: 5%; text-align: center; vertical-align: middle;">#</th>
                                        <th style="width: 85%; vertical-align: middle;">Term / Condition</th>
                                        <th style="width: 10%; text-align: center; vertical-align: middle;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="terms_container">
                                    @if(isset($terms) && is_array($terms) && count($terms) > 0)
                                        @foreach($terms as $index => $term)
                                            <tr class="term-row">
                                                <td class="row-index" style="text-align: center; vertical-align: middle; font-weight: bold;">{{ $index + 1 }}</td>
                                                <td>
                                                    <input type="text" class="form-control" name="terms[]" value="{{ $term }}" placeholder="Enter term or condition details" required>
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <button type="button" class="btn btn-danger btn-sm remove-term" style="background-color: #e74c3c; border-color: #e74c3c;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="term-row">
                                            <td class="row-index" style="text-align: center; vertical-align: middle; font-weight: bold;">1</td>
                                            <td>
                                                <input type="text" class="form-control" name="terms[]" placeholder="Enter term or condition details" required>
                                            </td>
                                            <td style="text-align: center; vertical-align: middle;">
                                                <button type="button" class="btn btn-danger btn-sm remove-term" style="background-color: #e74c3c; border-color: #e74c3c;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Function to update row indexes
        function updateIndexes() {
            $('#terms_container .term-row').each(function(index) {
                $(this).find('.row-index').text(index + 1);
            });
        }

        // Add new term row
        $(document).on('click', '.add-term', function() {
            var html = `
                <tr class="term-row">
                    <td class="row-index" style="text-align: center; vertical-align: middle; font-weight: bold;"></td>
                    <td>
                        <input type="text" class="form-control" name="terms[]" placeholder=@json(__('Enter term or condition details')) required>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <button type="button" class="btn btn-danger btn-sm remove-term" style="background-color: #e74c3c; border-color: #e74c3c;">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#terms_container').append(html);
            updateIndexes();
        });

        // Remove term row
        $(document).on('click', '.remove-term', function() {
            $(this).closest('.term-row').remove();
            updateIndexes();
        });
    });
</script>
@endpush
