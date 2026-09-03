@extends('layouts.app')

@section('title', 'Sales History')

@section('content')

    <h1>Sales History</h1>

    <button type="button" class="btn btn-primary mb-3" onclick="window.location.href='{{ route('sales.create') }}'">
        Create New Sale
    </button>
    
    <table id="salesTable" class="table table-bordered">

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

@endsection

@push('scripts')

<script>
    $(document).ready(function () {
        $('#salesTable').DataTable({
            processing:true,
            serverSide:true,
            ajax: {
            url: "{{ url('/api/sales_datatables') }}",
                // url: BASE_URL + '/api/' + endpoint + '_datatables',
                type: 'POST'
            },
            columns: [
                { data: 'number' },
                { data: 'date' },
                { data: 'user_id' },
                { data: 'action', orderable: false, searchable: false }
            ]
        });
    });

@endpush