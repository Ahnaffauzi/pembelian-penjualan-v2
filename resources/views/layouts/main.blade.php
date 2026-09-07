<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'MyPos')</title>

        {{-- Google Fonts --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        {{-- Bootstrap CSS, DataTables CSS, SweetAlert2 CSS, Font Awesome CSS, Select2 CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

        {{-- Custom Font Styles --}}
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>

        @stack('styles')
    </head>

    <script type="text/javascript">
        let BASE_URL = '{{ url('/') }}';
    </script>

    <body>
        @auth
            @include('layouts.navbar')
        @endauth

        <main class="container py-4">
            @yield('content')
        </main>

        {{-- jQuery, Bootstrap JS, DataTables JS, SweetAlert2 JS --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // Global function to fetch and build inventory dropdowns
            function getInventories(config) {
                let element = config.element;
                let selected_val = config.selected_val || '';

                $.ajax({
                    url: BASE_URL + '/api/inventories_datatables',
                    type: 'POST',
                    data: {
                        start: 0,
                        length: -1, // Get all data
                        search: '',
                        order: [{column: 1, dir: 'asc'}] // Order by code/name
                    },
                    success: function (response) {
                        let html = '<option value="">-- Select Item --</option>';
                        
                        response.data.forEach(function (item) {
                            let selected = (selected_val == item.id) ? 'selected' : '';
                            html += '<option value="' + item.id + '" data-price="' + item.price + '" data-stock="' + item.stock + '" data-code="' + item.code + '" ' + selected + '>';
                            html += item.code + ' - ' + item.name;
                            html += '</option>';
                        });

                        $(element).html(html);
                        
                        if (selected_val) {
                            $(element).val(selected_val).trigger('change');
                        }
                    }
                });
            }
        </script>
        @stack('scripts')
        
    </body>
</html>
