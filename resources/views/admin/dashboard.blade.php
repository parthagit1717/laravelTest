@extends('admin.layouts.app2')
@section('title', 'Admin Dashboard')

@section('content')

 <div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            👋 Welcome back, {{ $admin->name }}
        </h2>
        
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-2">Total Users</h5>
                    <h2 class="fw-bolder">{{ $userCount }}</h2>
                    <i class="bi bi-people-fill fs-1 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-success mb-2">Total Posts</h5>
                    <h2 class="fw-bolder">{{ $postCount }}</h2>
                    <i class="bi bi-file-earmark-text-fill fs-1 text-success"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold text-warning mb-2">Total Comments</h5>
                    <h2 class="fw-bolder">{{ $commentCount }}</h2>
                    <i class="bi bi-chat-dots-fill fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data Section -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Recent Users</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($recentUsers as $user)
                            <li class="list-group-item d-flex align-items-center">
                                <img src="{{ $user->image ? asset('storage/images/user_image/' . $user->image) : asset('assets/images/users/7.png') }}"
                                     class="rounded-circle me-3 border" width="40" height="40">
                                <div>
                                    <strong>{{ $user->name }}</strong><br>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No recent users found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Recent Posts</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($recentPosts as $post)
                            <li class="list-group-item">
                                <strong>{{ $post->title }}</strong><br>
                                <small class="text-muted">
                                    by {{ $post->user->name ?? 'Unknown' }} • {{ $post->created_at->diffForHumans() }}
                                </small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No recent posts available.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
