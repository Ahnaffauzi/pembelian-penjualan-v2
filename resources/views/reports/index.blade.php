@extends('layouts.main')

@section('title', 'Reports')

@section('content')

<h1>Reports</h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#salesReport" type="button">
                Sales Report
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#purchaseReport" type="button">
                Purchase Report
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="salesReport">
            <table id="salesReportTable" class="table table-bordered">
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

        <div class="tab-pane fade" id="purchaseReport">
            <table id="purchaseReportTable" class="table table-bordered">
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

    <div class="modal fade" id="reportDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="reportDetailHeader" class="mb-3"></div>

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
                        <tbody id="reportDetailItems"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    let sales_endpoint = 'sales';
    let purchases_endpoint = 'purchases';

    $(document).ready(function () {
        $('#salesReportTable').DataTable({
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
                url: BASE_URL + '/api/' + sales_endpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                { data: 'id', visible: false },
                { data: 'number' },
                { data: 'date',
                    render: function (data) {
                        return new Date(data).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                { data: 'user_name' },
                { data: 'total_qty' },
                {
                    data: 'total_amount',
                    render: function (data) {
                        return 'Rp ' + Number(data).toLocaleString('id-ID');
                    }
                },
                { data: 'action', orderable: false, searchable: false }
            ]
        });

        $('#purchaseReportTable').DataTable({
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
                url: BASE_URL + '/api/' + purchases_endpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                { data: 'id', visible: false },
                { data: 'number' },
                { data: 'date',
                    render: function (data) {
                        return new Date(data).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                { data: 'user_name' },
                { data: 'total_qty' },
                {
                    data: 'total_amount',
                    render: function (data) {
                        return 'Rp ' + Number(data).toLocaleString('id-ID');
                    }
                },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });

    $(document).on('click', '.detail-sale', function () {
        loadReportDetail(BASE_URL + '/api/' + sales_endpoint + '/' + $(this).data('id'));
    });

    $(document).on('click', '.detail-purchase', function () {
        loadReportDetail(BASE_URL + '/api/' + purchases_endpoint + '/' + $(this).data('id'));
    });

    function loadReportDetail(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function (record) {
                $('#reportDetailHeader').html(`
                    <strong>${record.number}</strong><br>
                    Date: ${record.date}<br>
                    Cashier: ${record.user_name ?? record.user_id}
                `);

                let html = '';
                let total = 0;

                record.details.forEach(function (item) {
                    let subtotal = item.qty * item.price;
                    total += subtotal;

                    html += `
                        <tr>
                            <td>${item.inventory_code}</td>
                            <td>${item.inventory_name}</td>
                            <td>${item.qty}</td>
                            <td>Rp ${Number(item.price).toLocaleString('id-ID')}</td>
                            <td>Rp ${Number(subtotal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });

                html += `
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                        <td><strong>Rp ${Number(total).toLocaleString('id-ID')}</strong></td>
                    </tr>
                `;

                $('#reportDetailItems').html(html);
                $('#reportDetailModal').modal('show');
            }
        });
    }
</script>

@endpush
