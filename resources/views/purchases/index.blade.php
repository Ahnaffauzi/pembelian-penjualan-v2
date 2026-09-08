@extends('layouts.main')

@section('title', 'Purchases History')

@section('content')

    <h1>Purchase History</h1>

    <button type="button" id="btn-create_purchase" class="btn btn-primary mb-3">
        Create New Purchase
    </button>

    <div class="modal fade" id="create-purchase_modal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form id="create-purchase_form" class="modal-content">
                <input type="hidden" name="id" id="input-id">
                <input type="hidden" name="date" id="input-date" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="user_id" id="input-user_id" value="{{ auth()->id() }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchase-modal_title">Create Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add_row">
                            <i class="fa fa-plus"></i> Add Item
                        </button>
                    </div>

                    <div id="items-wrapper"></div>

                    <hr>
                    <div class="text-end">
                        <h5>Total: <span id="grand-total">Rp 0</span></h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="btn-save_purchase">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
    
    <table id="purchases-table" class="table table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>Number</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Action</th>
            </tr>
        </thead>

    </table>

    <div class="modal fade" id="purchase-detail_modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Purchase Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="purchase-detail_header" class="mb-3"></div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-detail_items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    let endpoint = 'purchases';
    let dt;
    let rowCount = 0;

    function formatCurrencyIdr(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function getCurrentDate() {
        let now = new Date();
        let month = String(now.getMonth() + 1).padStart(2, '0');
        let day = String(now.getDate()).padStart(2, '0');

        return now.getFullYear() + '-' + month + '-' + day;
    }

    function setPurchaseDefaultFields(dateValue = getCurrentDate(), userValue = '{{ auth()->id() }}') {
        $('#input-date').val(dateValue).attr('value', dateValue);
        $('#input-user_id').val(userValue).attr('value', userValue);
    }

    function getPurchaseInventories(config) {
        let element = config.element;
        let selectedVal = config.selectedVal || '';

        $.ajax({
            url: BASE_URL + '/api/inventories_datatables',
            type: 'POST',
            dataType: 'json',
            data: {
                start: 0,
                length: -1,
                search: {
                    value: ''
                },
                order: [
                    {
                        column: 0,
                        dir: 'desc'
                    }
                ]
            },
            success: function (response) {
                let html = '<option value="">-- Select Item --</option>';

                response.data.forEach(function (item) {
                    let selected = (selectedVal == item.id) ? 'selected' : '';
                    html += '<option value="' + item.id + '" data-price="' + item.price + '" data-stock="' + item.stock + '" data-code="' + item.code + '" ' + selected + '>';
                    html += item.code + ' - ' + item.name;
                    html += '</option>';
                });

                $(element).html(html);

                if (selectedVal) {
                    $(element).val(selectedVal).trigger('change.select2');
                } else {
                    $(element).trigger('change');
                }
            }
        });
    }

    function calculateGrandTotal() {
        let total = 0;
        $('.subtotal').each(function() {
            total += Number($(this).data('value')) || 0;
        });
        $('#grand-total').text(formatCurrencyIdr(total));
    }

    function addRow(defaultValue = null) {
        let inventoryId = defaultValue?.inventory_id ?? '';
        let qty = defaultValue?.qty ?? 1;
        let price = defaultValue?.price ?? 0;
        let subtotalVal = qty * price;

        let html = '';
        html += '<div class="row g-2 align-items-end mb-2" id="row-' + rowCount + '">';
        html += '    <div class="col-5">';
        html += '        <label>Inventory Item</label>';
        html += '        <select class="form-select inventory-select" name="items[' + rowCount + '][inventory_id]" data-index="' + rowCount + '" id="input-inventory_id_' + rowCount + '" required></select>';
        html += '    </div>';
        html += '    <div class="col-2">';
        html += '        <label>Price</label>';
        html += '        <input class="form-control price" name="items[' + rowCount + '][price]" id="input-price_' + rowCount + '" value="' + price + '" readonly>';
        html += '    </div>';
        html += '    <div class="col-2">';
        html += '        <label>Qty</label>';
        html += '        <input type="number" class="form-control qty" name="items[' + rowCount + '][qty]" data-index="' + rowCount + '" id="input-qty_' + rowCount + '" value="' + qty + '" min="1" required>';
        html += '    </div>';
        html += '    <div class="col-2 text-end fw-bold subtotal" id="subtotal_' + rowCount + '" data-value="' + subtotalVal + '">' + formatCurrencyIdr(subtotalVal) + '</div>';
        html += '    <div class="col-1 text-end">';
        html += '        <button type="button" class="btn btn-danger btn-sm remove-row" data-row="' + rowCount + '">';
        html += '            <i class="fa fa-trash-o"></i>';
        html += '        </button>';
        html += '    </div>';
        html += '</div>';

        $('#items-wrapper').append(html);

        getPurchaseInventories({
            element: '#input-inventory_id_' + rowCount,
            selectedVal: inventoryId
        });

        $('#input-inventory_id_' + rowCount).select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#create-purchase_modal')
        });

        rowCount++;
        calculateGrandTotal();
    }

    $('#btn-create_purchase').on('click', function() {
        $('#create-purchase_form')[0].reset();
        $('#input-id').val('').attr('value', '');
        setPurchaseDefaultFields();
        $('#purchase-modal_title').text('Create Purchase');
        $('#btn-save_purchase').text('Save Purchase');
        $('#items-wrapper').empty();
        rowCount = 0;
        $('#grand-total').text('Rp 0');
        addRow();
        $('#create-purchase_modal').modal('show');
    });

    $('#btn-add_row').on('click', function() {
        addRow();
    });

    $(document).on('click', '.remove-row', function() {
        let row = $(this).data('row');
        $('#row-' + row).remove();
        calculateGrandTotal();
    });

    $(document).on('change', '.inventory-select', function() {
        let index = $(this).data('index');
        let selectedOption = $(this).find('option:selected');
        let price = selectedOption.data('price') || 0;
        let qty = $('#input-qty_' + index).val() || 1;
        let subtotal = price * qty;

        $('#input-price_' + index).val(price);
        $('#subtotal_' + index).data('value', subtotal).text(formatCurrencyIdr(subtotal));
        calculateGrandTotal();
    });

    $(document).on('input', '.qty', function() {
        let index = $(this).data('index');
        let price = $('#input-price_' + index).val() || 0;
        let qty = $(this).val() || 1;
        let subtotal = price * qty;

        $('#subtotal_' + index).data('value', subtotal).text(formatCurrencyIdr(subtotal));
        calculateGrandTotal();
    });

    $(document).ready(function () {
        dt = $('#purchases-table').DataTable({
            processing:true,
            serverSide:true,
            dom:
                "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-3'<'col-md-5'i><'col-md-4'p><'col-md-3 text-end'l>>",
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-success border-0',
                    text: 'Copy',
                    exportOptions: { columns: [1, 2, 3] }
                },
                {
                    extend: 'csv',
                    className: 'btn btn-info border-0',
                    text: 'CSV',
                    exportOptions: { columns: [1, 2, 3] }
                },
                {
                    extend: 'excel',
                    className: 'btn btn-warning border-0',
                    text: 'Excel',
                    exportOptions: { columns: [1, 2, 3] }
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger border-0',
                    text: 'PDF',
                    exportOptions: { columns: [1, 2, 3] }
                },
                {
                    extend: 'print',
                    className: 'btn btn-secondary border-0',
                    text: 'Print',
                    exportOptions: { columns: [1, 2, 3] }
                }
            ],
            destroy: true,
            pageLength: 10,
            responsive: false,
            scrollX: true,
            order: [[0, 'desc']],
            ajax: {
                url: BASE_URL + '/api/' + endpoint + '_datatables',
                type: 'POST',
                data: function (d) {
                    @if(auth()->user()->hasRole('Purchase'))
                        d.filter = {
                            user_id: "{{ auth()->id() }}"
                        };
                    @endif
                },
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    width: '5%',
                    "visible": false
                },
                {
                    data: 'number',
                    name: 'number'
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function (data) {
                        if (!data) {
                            return '-';
                        }

                        return new Date(data).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end'
                }
            ]
        });

    });

    $('#create-purchase_form').on('submit', function(e) {
        e.preventDefault();
        if (!$('#input-date').val() || !$('#input-user_id').val()) {
            setPurchaseDefaultFields();
        }

        let id = $('#input-id').val();
        let method = id ? 'PATCH' : 'POST';
        let url = BASE_URL + '/api/' + endpoint;

        if (id) {
            url += '/' + id;
        }

        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(response) {
                $('#create-purchase_modal').modal('hide');
                dt.ajax.reload(null, false);
                Swal.fire('Success', 'Purchase saved successfully!', 'success');
            }
        });
    });

    $(document).on('click', '.edit-data', function (e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: BASE_URL + '/api/' + endpoint + '/' + id,
            type: 'GET',
            success: function (purchase) {
                $('#create-purchase_form')[0].reset();
                $('#input-id').val(purchase.id).attr('value', purchase.id);
                setPurchaseDefaultFields(
                    purchase.date ? purchase.date.substring(0, 10) : getCurrentDate(),
                    purchase.user_id || '{{ auth()->id() }}'
                );
                $('#purchase-modal_title').text('Edit Purchase');
                $('#btn-save_purchase').text('Update Purchase');
                $('#items-wrapper').empty();
                rowCount = 0;
                $('#grand-total').text('Rp 0');

                if (purchase.details && purchase.details.length > 0) {
                    purchase.details.forEach(function (item) {
                        addRow(item);
                    });
                } else {
                    addRow();
                }

                $('#create-purchase_modal').modal('show');
            }
        });
    });

    $(document).on('click', '.delete-data', function (e) {
        e.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete Purchase?',
            text: 'This purchase will be deleted and stock will be reduced.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/' + endpoint + '/' + id,
                    type: 'DELETE',
                    success: function () {
                        dt.ajax.reload(null, false);
                        Swal.fire('Deleted', 'Purchase deleted successfully.', 'success');
                    }
                });
            }
        });
    });

    $(document).on('click', '.detail-purchase', function (e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: BASE_URL + '/api/' + endpoint + '/' + id,
            type: 'GET',
            success: function (purchase) {
                let header = '';
                header += '<strong>' + purchase.number + '</strong><br>';
                header += 'Date: ' + purchase.date + '<br>';
                header += 'Cashier: ' + (purchase.user_name ?? purchase.user_id);

                $('#purchase-detail_header').html(header);

                let html = '';
                let total = 0;

                purchase.details.forEach(function (item) {
                    let subtotal = item.qty * item.price;
                    total += subtotal;

                    html += '<tr>';
                    html += '    <td>' + item.inventory_code + '</td>';
                    html += '    <td>' + item.inventory_name + '</td>';
                    html += '    <td>' + item.qty + '</td>';
                    html += '    <td>Rp ' + Number(item.price).toLocaleString('id-ID') + '</td>';
                    html += '    <td>Rp ' + Number(subtotal).toLocaleString('id-ID') + '</td>';
                    html += '</tr>';
                });

                html += '<tr>';
                html += '    <td colspan="4" class="text-end"><strong>Total</strong></td>';
                html += '    <td><strong>Rp ' + Number(total).toLocaleString('id-ID') + '</strong></td>';
                html += '</tr>';

                $('#purchase-detail_items').html(html);
                $('#purchase-detail_modal').modal('show');
            }
        });
    });
</script>

@endpush
