<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'mypos')</title>

        {{-- Bootstrap CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        @stack('styles')
    </head>

    <script type="text/javascript">
    let BASE_URL = '{{ env('APP_URL') }}';
    </script>

    <body>
        @auth
            @include('layouts.navbar')
        @endauth

        <main class="container py-4">
            @yield('content')
        </main>

        {{-- jQuery and Bootstrap JS --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        @stack('scripts')
    </body>
</html>