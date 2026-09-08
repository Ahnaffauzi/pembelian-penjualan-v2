@extends('layouts.main')

@section('title', 'Reports')

@section('content')

<h1>Reports</h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sales-report" type="button">
                Sales Report
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#purchase-report" type="button">
                Purchase Report
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="sales-report">
            <table id="sales-report_table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Total Qty</th>
                        <th>Total Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="tab-pane fade" id="purchase-report">
            <table id="purchase-report_table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Total Qty</th>
                        <th>Total Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="report-detail_modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="report-detail_header" class="mb-3"></div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="report-detail_items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    let salesTable;
    let purchaseTable;
    
    let salesEndpoint = 'sales';
    let purchasesEndpoint = 'purchases';

    $(document).ready(function () {
        salesTable = $('#sales-report_table').DataTable({
            processing: true,
            serverSide: true,
            dom:
                "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-3'<'col-md-5'i><'col-md-4'p><'col-md-3 text-end'l>>",
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-success border-0',
                    text: 'Copy',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'csv',
                    className: 'btn btn-info border-0',
                    text: 'CSV',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'excel',
                    className: 'btn btn-warning border-0',
                    text: 'Excel',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger border-0',
                    text: 'PDF',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'print',
                    className: 'btn btn-secondary border-0',
                    text: 'Print',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                }
            ],
            destroy: true,
            pageLength: 10,
            responsive: false,
            scrollX: true,
            order: [[0, 'desc']],
            ajax: {
                url: BASE_URL + '/api/' + salesEndpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    width: '5%',
                    visible: false
                },
                {
                    data: 'number',
                    name: 'number'
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function (data) {
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
                    data: 'total_qty',
                    name: 'total_qty'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    render: function (data) {
                        return 'Rp ' + Number(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        let html = '';
                        html += '<div class="dropdown">';
                        html += '    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown">';
                        html += '        <i class="fa fa-ellipsis-h"></i>';
                        html += '    </button>';
                        html += '    <ul class="dropdown-menu dropdown-menu-end">';
                        html += '        <li><a href="#" class="dropdown-item detail-sale" data-id="' + row.id + '">Detail</a></li>';
                        html += '    </ul>';
                        html += '</div>';

                        return html;
                    }
                }
            ]
        });

        purchaseTable = $('#purchase-report_table').DataTable({
            processing: true,
            serverSide: true,
            dom:
                "<'row mb-3'<'col-md-6'B><'col-md-6'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-3'<'col-md-5'i><'col-md-4'p><'col-md-3 text-end'l>>",
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-success border-0',
                    text: 'Copy',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'csv',
                    className: 'btn btn-info border-0',
                    text: 'CSV',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'excel',
                    className: 'btn btn-warning border-0',
                    text: 'Excel',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger border-0',
                    text: 'PDF',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'print',
                    className: 'btn btn-secondary border-0',
                    text: 'Print',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                }
            ],
            destroy: true,
            pageLength: 10,
            responsive: false,
            scrollX: true,
            order: [[0, 'desc']],
            ajax: {
                url: BASE_URL + '/api/' + purchasesEndpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    width: '5%',
                    visible: false
                },
                {
                    data: 'number',
                    name: 'number'
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function (data) {
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
                    data: 'total_qty',
                    name: 'total_qty'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    render: function (data) {
                        return 'Rp ' + Number(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        let html = '';
                        html += '<div class="dropdown">';
                        html += '    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown">';
                        html += '        <i class="fa fa-ellipsis-h"></i>';
                        html += '    </button>';
                        html += '    <ul class="dropdown-menu dropdown-menu-end">';
                        html += '        <li><a href="#" class="dropdown-item detail-purchase" data-id="' + row.id + '">Detail</a></li>';
                        html += '    </ul>';
                        html += '</div>';

                        return html;
                    }
                }
            ]
        });

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });

    $(document).on('click', '.detail-sale', function (e) {
        e.preventDefault();
        loadReportDetail(BASE_URL + '/api/' + salesEndpoint + '/' + $(this).data('id'));
    });

    $(document).on('click', '.detail-purchase', function (e) {
        e.preventDefault();
        loadReportDetail(BASE_URL + '/api/' + purchasesEndpoint + '/' + $(this).data('id'));
    });

    function loadReportDetail(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (record) {
                let header = '';
                header += '<strong>' + record.number + '</strong><br>';
                header += 'Date: ' + record.date + '<br>';
                header += 'Cashier: ' + (record.user_name ?? record.user_id);

                $('#report-detail_header').html(header);

                let html = '';
                let total = 0;

                record.details.forEach(function (item) {
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

                $('#report-detail_items').html(html);
                $('#report-detail_modal').modal('show');
            }
        });
    }
</script>

@endpush
