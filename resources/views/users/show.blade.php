@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Details - {{ $user->name }}</h2>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit User
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</p>
                            @if($user->id === auth()->id())
                                <span class="badge bg-info">This is your account</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Roles</h5>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        <div class="mb-3">
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary me-2 mb-2" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                        <div>
                            <h6>Permissions from Roles:</h6>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @php
                                    $allPermissions = $user->getAllPermissions();
                                @endphp
                                @if($allPermissions->count() > 0)
                                    <div class="row">
                                        @foreach($allPermissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <span class="badge bg-secondary">{{ $permission->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No permissions assigned</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-muted">No roles assigned to this user</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-pencil"></i> Edit User
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete User
                            </button>
                        </form>
                    @else
                        <button class="btn btn-danger w-100" disabled>
                            <i class="bi bi-trash"></i> Cannot Delete Own Account
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





