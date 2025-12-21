<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'POS System'))</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous" onerror="console.error('Bootstrap CSS failed to load')">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* Fallback styles in case CDN fails */
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .sidebar { position: fixed; left: 0; top: 0; width: 250px; height: 100vh; background: #343a40; color: #fff; overflow-y: auto; }
        .main-content { margin-left: 250px; min-height: 100vh; background: #f8f9fa; }
        .topbar { background: #fff; padding: 1rem; border-bottom: 1px solid #dee2e6; }
        .nav-link { color: #adb5bd; padding: 0.75rem 1.5rem; display: block; text-decoration: none; }
        .nav-link:hover, .nav-link.active { background: #495057; color: #fff; }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
        @include('layouts.sidebar')
        <div class="main-content">
            @include('layouts.topbar')
            <main class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    @else
        @yield('content')
    @endauth

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
