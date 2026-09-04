@extends('layouts.main')

@section('title', 'Inventory')

@section('content')

    <h1>Inventory</h1>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#inventoryModal">
        Add Inventory
    </button>
    
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
    // console.log('Inventory page loaded');
    // let endpoint = 'inventories';

    $(document).ready(function () {
        // Initialize DataTable
        $('#inventoriesTable').DataTable({
            processing:true,
            serverSide:true,
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
                { data: 'action', orderable: false, searchable: false }
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
            url: "{{ url('/api/inventories') }}/" + id,
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
            url: "{{ url('/api/inventories') }}",
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
                    url: "{{ url('/api/inventories') }}/" + id,
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
