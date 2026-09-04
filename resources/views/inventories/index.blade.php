@extends('layouts.main')

@section('title', 'Inventory')

@section('content')

    <h1>Inventory</h1>

    <button type="button" class="btn btn-primary mb-3" id="add-button">
        Add Inventory
    </button>
    
    <table id="inventoriesTable" class="table table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
        </thead>

    </table>

    <div class="modal fade" id="inventoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="inventoryForm" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="formModalTitle">Add Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="input-id">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" id="input-code" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="input-name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" id="input-price" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" id="input-stock" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')

<script>
    let endpoint = 'inventories';

    $(document).ready(function () {
        // Initialize DataTable
        $('#inventoriesTable').DataTable({
            processing:true,
            serverSide:true,
            order: [[0, 'desc']],
            ajax: {
                url: BASE_URL + '/api/' + endpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    width: '5%',
                    "visible": false
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'price',
                    name: 'price'
                },
                {
                    data: 'stock',
                    name: 'stock'
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

    $(document).on('click', '#add-button', function () {
        $('#inventoryForm')[0].reset();
        $('#input-id').val('');
        $('#formModalTitle').text('Add Inventory');
        $('#inventoryModal').modal('show');
    });

    $(document).on('click', '.edit-data', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        $.ajax({
            url: BASE_URL + '/api/' + endpoint + '/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#input-id').val(data.id);
                $('#input-code').val(data.code);
                $('#input-name').val(data.name);
                $('#input-price').val(data.price);
                $('#input-stock').val(data.stock);

                $('#formModalTitle').text('Edit Inventory');
                $('#inventoryModal').modal('show');
            }
        });
    });

    $('#inventoryForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: BASE_URL + '/api/' + endpoint,
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $('#inventoryModal').modal('hide');
                $('#inventoryForm')[0].reset();
                $('#inventoriesTable').DataTable().ajax.reload();

                Swal.fire('Success', response.message, 'success');
            }
        });
    });
    $(document).on('click', '.delete-data', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete this data?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/api/' + endpoint + '/' + id,
                    type: 'DELETE',
                    success: function (response) {
                        $('#inventoriesTable').DataTable().ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    }
                });
            }
        });
    });
</script>

@endpush
