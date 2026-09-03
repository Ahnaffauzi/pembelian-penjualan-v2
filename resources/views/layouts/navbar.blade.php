<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">MyPos</a>

        <div class="navbar-nav">
            @role('SuperAdmin')
                <a class="nav-link" href="/inventories">Inventories</a>
                <a class="nav-link" href="/sales/create">Sales</a>
                <a class="nav-link" href="/purchases">Purchases</a>
                <a class="nav-link" href="/reports">Reports</a>
            @endrole

            @role('Sales')
                <a class="nav-link" href="/sales">Sales</a>
            @endrole

            @role('Purchase')
                <a class="nav-link" href="/purchases">Purchases</a>
            @endrole

            @role('Manager')
                <a class="nav-link" href="/reports">Reports</a>
            @endrole
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">
                Logout
            </button>
        </form>
    </div>
</nav>