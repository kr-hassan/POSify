<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test Page - If you see this, the system is working!</h1>
        <p>User: {{ auth()->user()->name ?? 'Not logged in' }}</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>


