<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">MyPos</a>

        <div class="navbar-nav">
            @role('SuperAdmin')
                <a class="nav-link" href="/dashboard">Dashboard</a>

                <a class="nav-link" href="/inventories">Inventories</a>

                <a class="nav-link" href="{{ url('/sales') }}">Sales</a>

                <a class="nav-link" href="{{ url('/purchases') }}">Purchase </a>

                <a class="nav-link" href="/reports">Reports</a>
            @endrole

            @role('Sales')
                <a class="nav-link" href="{{ url('/sales') }}">Sales</a>
            @endrole

            @role('Purchase')
                <a class="nav-link" href="{{ url('/purchases') }}">Purchase</a>
            @endrole

            @role('Manager')
                <a class="nav-link" href="{{ url('/reports') }}">Reports</a>
            @endrole
        </div>

        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">
                Logout
            </button>
        </form>
    </div>
</nav>