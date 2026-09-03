@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
    

    <div class="container-fluid">
        <button type="button" class="btn btn-primary mb-3" onclick="window.location.href='{{ route('purchases.index') }}'">
            See Purchase Order History
        </button>
        <div class="row">

            {{-- Left side --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">

                        <h5>Products</h5>

                        <input
                            id="productSearch"
                            type="text"
                            class="form-control mb-3"
                            placeholder="Search product..."
                        >

                        <div class="row">

                            <table id="inventoriesTable" class="table table-bordered">

                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                            </table>

                        </div>

                    </div>
                </div>
            </div>


            {{-- Right side --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">

                        <h5>Current Order</h5>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody id="cartItems">
                                <tr>
                                    <td colspan="4" class="text-muted text-center">
                                        No items selected
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong id="cartTotal">Rp 0</strong>
                        </div>

                        <button type="button" id="purchaseNow" class="btn btn-success w-100 mt-3">
                            Purchase Now
                        </button>

                    </div>
                </div>
            </div>

        </div>
    </div>


@endsection

@push('scripts')

<script>

    let cartItems = [];

    function formatRupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function renderCart() {
        let html = '';
        let total = 0;

        if (cartItems.length === 0) {
            $('#cartItems').html(`
                <tr>
                    <td colspan="4" class="text-muted text-center">
                        No items selected
                    </td>
                </tr>
            `);

            $('#cartTotal').text(formatRupiah(0));
            return;
        }

        cartItems.forEach(function (item) {
            let subtotal = item.price * item.qty;
            total += subtotal;

            html += `
                <tr>
                    <td>
                        <strong>${item.name}</strong><br>
                        <small class="text-muted">${item.code}</small>
                    </td>
                    <td style="width: 120px;">
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary decrease-qty" data-id="${item.id}" type="button">-</button>
                            <input type="number" class="form-control text-center cart-qty" data-id="${item.id}" value="${item.qty}" min="1" max="${item.stock}">
                            <button class="btn btn-outline-secondary increase-qty" data-id="${item.id}" type="button">+</button>
                        </div>
                    </td>
                    <td>${formatRupiah(subtotal)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger remove-item" data-id="${item.id}" type="button">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#cartItems').html(html);
        $('#cartTotal').text(formatRupiah(total));
    }
    $(document).on('click', '.increase-qty', function () {
        let id = $(this).data('id');
        let item = cartItems.find(item => item.id == id);

        item.qty++;

        renderCart();
    });

    $(document).on('click', '.decrease-qty', function () {
        let id = $(this).data('id');
        let item = cartItems.find(item => item.id == id);

        if (item && item.qty > 1) {
            item.qty--;
        }

        renderCart();
    });

    $(document).on('change', '.cart-qty', function () {
        let id = $(this).data('id');
        let item = cartItems.find(item => item.id == id);
        let qty = Number($(this).val());

        item.qty = qty;

        renderCart();
    });

    $(document).on('click', '.remove-item', function () {
        let id = $(this).data('id');

        cartItems = cartItems.filter(item => item.id != id);

        renderCart();
    });
    

    $(document).ready(function () {
        // Initialize DataTable
        let inventoriesTable = $('#inventoriesTable').DataTable({
            processing:true,
            serverSide:true,
            dom: 'lrtip',
            ajax: {
            url: "{{ url('/api/inventories_datatables') }}",
                // url: BASE_URL + '/api/' + endpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                { data: 'code' },
                { data: 'name' },
                { data: 'price' },
                { data: 'stock' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <button
                                type="button"
                                class="btn btn-sm btn-primary add-to-cart"
                                data-id="${row.id}"
                                data-code="${row.code}"
                                data-name="${row.name}"
                                data-price="${row.price}"
                                data-stock="${row.stock}">
                                <i class="fa fa-plus"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        $('#productSearch').on('keyup', function () {
            inventoriesTable.search(this.value).draw();
        });
    });

    $(document).on('click', '.add-to-cart', function () {
        let id = $(this).data('id');

        cartItems.push({
            id: id,
            code: $(this).data('code'),
            name: $(this).data('name'),
            price: Number($(this).data('price')),
            stock: Number($(this).data('stock')),
            qty: 1
        });

        renderCart();
    });

    $(document).on('click', '#purchaseNow', function () {
        if (cartItems.length === 0) {
            Swal.fire('Cart is empty', 'Please add items to the cart before placing the order.', 'warning');
            return;
        }

        let payload = {
            date: new Date().toISOString().slice(0, 10),
            user_id: "{{ auth()->id() }}",
            items: cartItems.map(function (item) {
                return {
                    inventory_id: item.id,
                    qty: item.qty
                };
            })
        };

        Swal.fire({
            title: 'Create this purchase order?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Purchase Now'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('/api/purchases') }}",
                    type: 'POST',
                    data: payload,
                    success: function (response) {
                        cartItems = [];
                        renderCart();

                        $('#inventoriesTable').DataTable().ajax.reload();

                        Swal.fire('Success', response.message, 'success');
                    },
                    error: function (xhr) {
                        let message = 'Failed to create sale.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });


</script>

@endpush
