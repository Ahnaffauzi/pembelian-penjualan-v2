@extends('layouts.main')

@section('title', 'Sales History')

@section('content')

    <h1>Sales History</h1>

        <button type="button" id="btn-create_sale" class="btn btn-primary mb-3">
            Create New Sale
        </button>

        <!-- Create Sale Modal -->
        <div class="modal fade" id="create-sale_modal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <form id="create-sale_form" class="modal-content">
                    <input type="hidden" name="id" id="input-id">
                    <input type="hidden" name="date" id="input-date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="user_id" id="input-user_id" value="{{ auth()->id() }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sale-modal_title">Create Sale</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-add_row">
                                Add Item
                            </button>
                        </div>
                        
                        <!-- Dynamic rows go here -->
                        <div id="items-wrapper"></div>
                        
                        <hr>
                        <div class="text-end">
                            <h5>Total: <span id="grand-total">Rp 0</span></h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success" id="btn-save_sale">Save Sale</button>
                    </div>
                </form>
            </div>
        </div>
    
    <table id="sales-table" class="table table-bordered">

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

    <div class="modal fade" id="sale-detail_modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sale Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="sale-detail_header" class="mb-3"></div>

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
                        <tbody id="sale-detail_items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    let endpoint = 'sales';
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

    function setSaleDefaultFields(dateValue = getCurrentDate(), userValue = '{{ auth()->id() }}') {
        $('#input-date').val(dateValue).attr('value', dateValue);
        $('#input-user_id').val(userValue).attr('value', userValue);
    }

    function getSalesInventories(config) {
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


    // Recalculates the total by looping through every element with class '.subtotal'
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

        getSalesInventories({
            element: '#input-inventory_id_' + rowCount,
            selectedVal: inventoryId
        });

        $('#input-inventory_id_' + rowCount).select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#create-sale_modal') 
        });

        rowCount++;
        calculateGrandTotal();
    }

    $('#btn-create_sale').on('click', function() {
        $('#create-sale_form')[0].reset();
        $('#input-id').val('').attr('value', '');
        setSaleDefaultFields();
        $('#sale-modal_title').text('Create Sale');
        $('#btn-save_sale').text('Save Sale');
        $('#items-wrapper').empty();
        rowCount = 0;
        $('#grand-total').text('Rp 0');
        addRow();
        $('#create-sale_modal').modal('show');
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
        
        $('#input-price_' + index).val(price);
        
        let subtotal = price * qty;
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

    function drawDatatable() {
        dt = $('#sales-table').addClass('nowrap').DataTable({
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
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/api/' + endpoint + '_datatables',
                type: 'POST',
                data: function (d) {
                    @if(auth()->user()->hasRole('Sales'))
                        d.filter = {
                            user_id: "{{ auth()->id() }}"
                        };
                    @endif
                }
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
    };

    $(document).ready(function () {
        drawDatatable();
    });

    $('#create-sale_form').on('submit', function(e) {
        e.preventDefault();
        if (!$('#input-date').val() || !$('#input-user_id').val()) {
            setSaleDefaultFields();
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
                $('#create-sale_modal').modal('hide');
                dt.ajax.reload(null, false);
                Swal.fire('Success', 'Sale saved successfully!', 'success');
            }
        });
    });

    $(document).on('click', '.edit-data', function (e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: BASE_URL + '/api/' + endpoint + '/' + id,
            type: 'GET',
            success: function (sale) {
                $('#create-sale_form')[0].reset();
                $('#input-id').val(sale.id).attr('value', sale.id);
                setSaleDefaultFields(
                    sale.date ? sale.date.substring(0, 10) : getCurrentDate(),
                    sale.user_id || '{{ auth()->id() }}'
                );
                $('#sale-modal_title').text('Edit Sale');
                $('#btn-save_sale').text('Update Sale');
                $('#items-wrapper').empty();
                rowCount = 0;
                $('#grand-total').text('Rp 0');

                if (sale.details && sale.details.length > 0) {
                    sale.details.forEach(function (item) {
                        addRow(item);
                    });
                } else {
                    addRow();
                }

                $('#create-sale_modal').modal('show');
            }
        });
    });

    $(document).on('click', '.delete-data', function (e) {
        e.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete Sale?',
            text: 'This sale will be deleted and stock will be restored.',
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
                        Swal.fire('Deleted', 'Sale deleted successfully.', 'success');
                    }
                });
            }
        });
    });
</script>

@endpush
