@extends('layouts.app')

@section('title', 'Purchases History')

@section('content')

    <h1>Purchase History</h1>

    <button type="button" class="btn btn-primary mb-3" onclick="window.location.href='{{ route('purchases.create') }}'">
        Create New Purchase
    </button>
    
    <table id="purchasesTable" class="table table-bordered">

        <thead>
            <tr>
                <th>Number</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Action</th>
            </tr>
        </thead>

    </table>

    <div class="modal fade" id="purchaseDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Purchase Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="purchaseDetailHeader" class="mb-3"></div>

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
                        <tbody id="purchaseDetailItems"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    $(document).ready(function () {
        $('#purchasesTable').DataTable({
            processing:true,
            serverSide:true,
            order: [[0, 'desc']],
            ajax: {
                url: "{{ url('/api/purchases_datatables') }}",
                type: 'POST',
                data: function (d) {
                    @if(auth()->user()->hasRole('Purchase'))
                        d.filter = {
                            user_id: "{{ auth()->id() }}"
                        };
                    @endif
                }
            },
            columns: [
                { data: 'number' },
                { data: 'date' },
                { data: 'user_name' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });

    $(document).on('click', '.detail-purchase', function () {
        let id = $(this).data('id');

        $.ajax({
            url: "{{ url('/api/purchases') }}/" + id,
            type: 'GET',
            success: function (purchase) {
                $('#purchaseDetailHeader').html(`
                    <strong>${purchase.number}</strong><br>
                    Date: ${purchase.date}<br>
                    Cashier: ${purchase.user_name ?? purchase.user_id}
                `);

                let html = '';
                let total = 0;

                purchase.details.forEach(function (item) {
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

                $('#purchaseDetailItems').html(html);
                $('#purchaseDetailModal').modal('show');
            }
        });
    });
</script>

@endpush