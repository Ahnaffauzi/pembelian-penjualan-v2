<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">MyPos</a>

        <div class="navbar-nav">
            @role('SuperAdmin')
                <a class="nav-link" href="/dashboard">Dashboard</a>

                <a class="nav-link" href="/inventories">Inventories</a>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Sales
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('sales.create') }}">Create Sales</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('sales.index') }}">Sales History</a>
                        </li>
                    </ul>
                </div>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Purchases
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('purchases.create') }}">Create Purchase</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('purchases.index') }}">Purchase History</a>
                        </li>
                    </ul>
                </div>

                <a class="nav-link" href="/reports">Reports</a>
            @endrole

            @role('Sales')
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Sales
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('sales.create') }}">Create Sales</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('sales.index') }}">Sales History</a>
                        </li>
                    </ul>
                </div>
            @endrole

            @role('Purchase')
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Purchases
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('purchases.create') }}">Create Purchase</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('purchases.index') }}">Purchase History</a>
                        </li>
                    </ul>
                </div>
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