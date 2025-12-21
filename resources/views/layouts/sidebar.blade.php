<aside class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">POS System</h4>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                    <i class="bi bi-cart"></i> POS
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="bi bi-box"></i> Products
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <i class="bi bi-tags"></i> Categories
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                    <i class="bi bi-receipt"></i> Sales
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('sale-returns.*') ? 'active' : '' }}" href="{{ route('sale-returns.index') }}">
                    <i class="bi bi-arrow-return-left"></i> Product Returns
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                    <i class="bi bi-cash-coin"></i> Payment Receipts
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                    <i class="bi bi-cart-plus"></i> Purchases
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">
                    <i class="bi bi-cash-stack"></i> Expenses
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.sales') }}">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </li>
        </ul>
    </nav>
</aside>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background: #343a40;
    color: #fff;
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 1rem;
    background: #212529;
    border-bottom: 1px solid #495057;
}

.sidebar-header h4 {
    color: #fff;
    margin: 0;
}

.sidebar-nav {
    padding: 1rem 0;
}

.sidebar-nav .nav-link {
    color: #adb5bd;
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s;
    text-decoration: none;
}

.sidebar-nav .nav-link:hover,
.sidebar-nav .nav-link.active {
    background: #495057;
    color: #fff;
}

.sidebar-nav .nav-link i {
    width: 20px;
}

.main-content {
    margin-left: 250px;
    min-height: 100vh;
    background: #f8f9fa;
}
</style>
