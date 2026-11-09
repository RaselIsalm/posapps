@extends('backend.layout.main')
@section('title', 'Add Transfer')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        #transfer_table_body tr td,
        #transfer_table_body input {
            color: #000 !important;
            font-weight: 500;
        }

        .swal2-popup {
            font-size: 0.9rem !important;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Add Transfer
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active"> Add Transfer</li>
    @endcomponent
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <form action="{{ route('transfers.store') }}" method="POST" enctype="multipart/form-data"
                    id="transfer-form">
                    @csrf
                    <div class="row mb-3 g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Date</label>
                            <input type="text" class="form-control" value="{{ date('d-m-Y') }}" disabled>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">From Branch *</label>
                            <div class="input-group tr-field-group">
                                <select name="from_branch_id" id="from_branch_id" class="form-select" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @can('create_branches')
                                    <button type="button" class="btn btn-outline-primary btn-tr-add"
                                        onclick="openTrBranchModal('from_branch_id')" title="Add Branch">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">To Branch *</label>
                            <div class="input-group tr-field-group">
                                <select name="to_branch_id" id="to_branch_id" class="form-select" required>
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @can('create_branches')
                                    <button type="button" class="btn btn-outline-primary btn-tr-add"
                                        onclick="openTrBranchModal('to_branch_id')" title="Add Branch">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="sent">Sent (Pending Receive)</option>
                                <option value="request">Request (Requisition)</option>
                                <option value="completed">Completed (Direct Transfer)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Product Search --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Product</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="product_search" class="form-control"
                                placeholder="Type product code or name and select...">
                        </div>
                        {{-- Search Suggestion Box --}}
                        <div id="product_list" class="list-group position-absolute w-100 shadow"
                            style="z-index: 999; display:none;"></div>
                    </div>

                    {{-- Order Table --}}
                    <h6 class="fw-bold mb-3">Order Table *</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle bg-light text-black" style="white-space: nowrap; min-width: 800px;">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th class="text-black">Name</th>
                                    <th class="text-black">Code</th>
                                    <th width="150" class="text-black">Batch No</th>
                                    <th width="100" class="text-black">Quantity</th>
                                    <th width="120" class="text-black">Net Unit Cost</th>
                                    <th width="100" class="text-black">Tax</th>
                                    <th width="120" class="text-black">SubTotal</th>
                                    <th width="50" class="text-danger"><i class="fas fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody id="transfer_table_body" class="text-dark fw-bold">
                                {{-- JS will append rows here --}}
                            </tbody>
                            <tfoot class="bg-white fw-bold text-dark">
                                <tr>
                                    <td colspan="3" class="text-end text-black">Total</td>
                                    <td id="total-qty" class="text-black text-end">0</td>
                                    <td></td>
                                    <td id="total-tax" class="text-black text-end">0.00</td>
                                    <td id="total-subtotal" class="text-black text-end">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Shipping & Document --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Shipping Cost</label>
                            <input type="number" name="shipping_cost" id="shipping_cost" class="form-control"
                                step="any" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Attach Document</label>
                            <input type="file" name="document" class="form-control">
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>

                    <input type="hidden" name="grand_total" id="grand_total_input">

                    {{-- Submit Button --}}
                    <div class="text-start">
                        <button type="submit" class="btn btn-primary px-4">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @include('backend.transfers.partials.quick_add')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {



            // Product Search Logic
            $('#product_search').on('keyup', function() {
                let query = $(this).val();
                let branchId = $('#from_branch_id').val();

                if (!branchId) {
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('warning', @json(__('Please select "From Branch" first!')));
                    }
                    $(this).val('');
                    return;
                }

                if (query.length > 2) {
                    $.ajax({
                        url: "{{ route('transfers.get_product') }}",
                        type: 'GET',
                        data: {
                            search: query,
                            branch_id: branchId
                        },
                        success: function(res) {
                            let html = '';
                            if (res.length > 0) {
                                res.forEach(item => {
                                    let varId = item.variant_id ? item.variant_id : '';

                                    html += `<a href="javascript:void(0)" class="list-group-item list-group-item-action select-product" 
                                                data-id="${item.id}" 
                                                data-variant-id="${varId}"
                                                data-name="${item.name}" 
                                                data-code="${item.code}" 
                                                data-price="${item.price}" 
                                                data-tax="${item.tax}"
                                                data-stock="${item.stock}">
                                                ${item.name} (${item.code}) - Stock: ${item.stock}
                                             </a>`;
                                });
                                $('#product_list').html(html).show();
                            } else {
                                $('#product_list').hide();
                            }
                        }
                    });
                } else {
                    $('#product_list').hide();
                }
            });

            // Add Product to Table
            $(document).on('click', '.select-product', function() {
                let id = $(this).data('id');
                let variantId = $(this).attr('data-variant-id');
                if (variantId === undefined || variantId === 'null') {
                    variantId = '';
                }
                let name = $(this).data('name');
                let code = $(this).data('code');
                let price = parseFloat($(this).data('price'));
                let taxRate = parseFloat($(this).data('tax'));
                let stock = $(this).data('stock');
                let uniqueId = variantId ? `${id}_${variantId}` : `${id}`;
                // Check duplicate using Unique ID
                if ($(`#row_${uniqueId}`).length > 0) {
                    let qtyInput = $(`#qty_${uniqueId}`);
                    qtyInput.val(parseFloat(qtyInput.val()) + 1);
                    calculateRow(uniqueId);
                } else {
                    let row = `
                    <tr id="row_${uniqueId}">
                        <td>
                            ${name}
                            <input type="hidden" name="product_id[]" value="${id}">
                            <input type="hidden" name="variant_id[]" value="${variantId}"> </td>
                        <td>${code}</td>
                        <td><input type="text" name="batch_no[]" class="form-control form-control-sm"></td>
                        <td>
                            <input type="number" name="qty[]" id="qty_${uniqueId}" class="form-control form-control-sm qty-input" value="1" min="1" oninput="calculateRow('${uniqueId}')" data-stock="${stock}">
                            <small class="text-muted">Stock: ${stock}</small>
                        </td>
                        <td class="text-end">
                            ${price.toFixed(2)}
                            <input type="hidden" name="net_unit_cost[]" id="price_${uniqueId}" value="${price}">
                        </td>
                        <td class="text-end">
                            <span id="tax_display_${uniqueId}">0.00</span>
                            <input type="hidden" name="tax[]" id="tax_${uniqueId}" value="0">
                            <input type="hidden" class="tax-rate" value="${taxRate}">
                        </td>
                        <td class="text-end">
                            <span id="subtotal_display_${uniqueId}">0.00</span>
                            <input type="hidden" name="subtotal[]" class="subtotal-input" id="subtotal_${uniqueId}" value="0">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                    $('#transfer_table_body').append(row);
                    calculateRow(uniqueId);
                }
                $('#product_search').val('');
                $('#product_list').hide();
            });

            // Remove Row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calculateGrandTotal();
            });

            // Calculate Row Function
            window.calculateRow = function(uid) {
                let qtyInput = $(`#qty_${uid}`);
                let qty = parseFloat(qtyInput.val()) || 0;
                let stock = parseFloat(qtyInput.data('stock')) || 0;

                if (qty > stock) {
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('You cannot transfer more than available stock (:stock)')).replace(':stock', stock));
                    }
                    qtyInput.val(stock);
                    qty = stock;
                }

                let price = parseFloat($(`#price_${uid}`).val()) || 0;
                let taxRate = parseFloat($(`#row_${uid} .tax-rate`).val()) || 0;

                let taxAmount = (price * taxRate / 100) * qty;
                let subtotal = (price * qty) + taxAmount;

                $(`#tax_${uid}`).val(taxAmount.toFixed(2));
                $(`#tax_display_${uid}`).text(taxAmount.toFixed(2));

                $(`#subtotal_${uid}`).val(subtotal.toFixed(2));
                $(`#subtotal_display_${uid}`).text(subtotal.toFixed(2));

                calculateGrandTotal();
            }

            // Grand Total
            function calculateGrandTotal() {
                let totalQty = 0;
                let totalTax = 0;
                let totalSubtotal = 0;

                $('.qty-input').each(function() {
                    totalQty += parseFloat($(this).val()) || 0;
                });
                $('input[name="tax[]"]').each(function() {
                    totalTax += parseFloat($(this).val()) || 0;
                });
                $('.subtotal-input').each(function() {
                    totalSubtotal += parseFloat($(this).val()) || 0;
                });

                let shipping = parseFloat($('#shipping_cost').val()) || 0;
                let grandTotal = totalSubtotal + shipping;

                $('#total-qty').text(totalQty);
                $('#total-tax').text(totalTax.toFixed(2));
                $('#total-subtotal').text(totalSubtotal.toFixed(2));
                $('#grand_total_input').val(grandTotal.toFixed(2));
            }

            $('#shipping_cost').on('input', calculateGrandTotal);
            $('#from_branch_id').on('change', function() {
                $('#transfer_table_body').empty();
                calculateGrandTotal();
            });

            $('#transfer-form').on('submit', function(e) {
                let valid = true;
                $('.qty-input').each(function() {
                    let qty = parseFloat($(this).val()) || 0;
                    let stock = parseFloat($(this).data('stock')) || 0;
                    if (qty > stock) {
                        valid = false;
                        return false;
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    if(typeof showFloatingAlert === 'function') {
                        showFloatingAlert('danger', @json(__('Please check the product quantities. Some items exceed available stock.')));
                    }
                }
            });
        });

        function showFloatingAlert(type, message) {
            $('.floating-alert').remove(); // Clear existing
            var label = type === 'success' ? @json(__('Success!')) : type === 'warning' ? @json(__('Warning!')) : @json(__('Error!'));
            var bsClass = type === 'error' ? 'danger' : type;
            var $alert = $('<div class="alert alert-' + bsClass + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }
    </script>
@endpush
