<nav class="topbar navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <button class="btn btn-link d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<style>
.topbar {
    padding: 1rem;
    margin-bottom: 0;
}
</style>




