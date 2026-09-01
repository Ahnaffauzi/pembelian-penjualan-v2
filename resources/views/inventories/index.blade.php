@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

    <h1>Inventory</h1>

    <table id="inventoriesTable" class="table table-bordered">

        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
            </tr>
        </thead>

    </table>

@endsection

@push('scripts')

<script>
    console.log('Inventory page loaded');
    let endpoint = 'inventories';

    $(document).ready(function () {
        $('#inventoriesTable').DataTable({
            processing:true,
            serverSide:true,
            ajax: {
                url: BASE_URL + '/api/' + endpoint,
                type: 'POST'
            }
        });

    });
</script>

@endpush