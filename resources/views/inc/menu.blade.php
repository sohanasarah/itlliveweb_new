<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

        <li class="nav-item">
            <a href="{{ route('home') }}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Home</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('expense-approval')}}" class="nav-link">
                <i class="fas fa-calculator nav-icon"></i>
                <p>Expense Approval</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('raise-indent')}}" class="nav-link">
                <i class="fas fa-list-ol nav-icon"></i>
                <p>Raise Indent</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('open-indents')}}" class="nav-link">
                <i class="fas fa-shipping-fast nav-icon"></i>
                <p>Pending Shipment</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grr-list')}}" class="nav-link">
                <i class="nav-icon fas fa-cubes"></i>
                <p>GRR </p>
            </a>
        </li>

        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-file-invoice"></i>
                <p>
                    Reports
                    <i class="fas fa-angle-left right"></i>
                    <span class="badge badge-info right">6</span>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('salesbySKUbydate')}}" class="nav-link">
                        <i class="fas fa-shopping-cart nav-icon"></i>
                        <p>Sales By SKU By Date</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pages/layout/boxed.html" class="nav-link">
                        <i class="fas fa-store nav-icon"></i>
                        <p>Sales By Customer</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pages/layout/fixed-sidebar.html" class="nav-link">
                        <i class="fas fa-money-bill-alt nav-icon"></i>
                        <p>Collection</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pages/layout/fixed-topnav.html" class="nav-link">
                        <i class="fas fa-coins nav-icon"></i>
                        <p>Expenses</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pages/layout/fixed-footer.html" class="nav-link">
                        <i class="fas fa-landmark nav-icon"></i>
                        <p>Remittance</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pages/layout/collapsed-sidebar.html" class="nav-link">
                        <i class="fas fa-warehouse nav-icon"></i>
                        <p>Stock Reconciliation</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pages/layout/collapsed-sidebar.html" class="nav-link">
                        <i class="fas fa-truck-loading nav-icon"></i>
                        <p>Stock Transfer</p>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</nav>
<!-- /.sidebar-menu -->