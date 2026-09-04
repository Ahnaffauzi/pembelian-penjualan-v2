@extends('layouts.main')

@section('title', 'Sales History')

@section('content')

    <h1>Sales History</h1>

    <button type="button" class="btn btn-primary mb-3" onclick="window.location.href='{{ route('sales.create') }}'">
        Create New Sale
    </button>
    
    <table id="salesTable" class="table table-bordered">

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

    <div class="modal fade" id="saleDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sale Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="saleDetailHeader" class="mb-3"></div>

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
                        <tbody id="saleDetailItems"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    let endpoint = 'sales';

    $(document).ready(function () {
        $('#salesTable').DataTable({
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

    $(document).on('click', '.detail-sale', function () {
        let id = $(this).data('id');

        $.ajax({
            url: BASE_URL + '/api/' + endpoint + '/' + id,
            type: 'GET',
            success: function (sale) {
                $('#saleDetailHeader').html(`
                    <strong>${sale.number}</strong><br>
                    Date: ${sale.date}<br>
                    Cashier: ${sale.user_name ?? sale.user_id}
                `);

                let html = '';
                let total = 0;

                sale.details.forEach(function (item) {
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

                $('#saleDetailItems').html(html);
                $('#saleDetailModal').modal('show');
            }
        });
    });
</script>

@endpush
