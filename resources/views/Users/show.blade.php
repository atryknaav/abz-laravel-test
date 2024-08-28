<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .active-nav-item {
            font-weight: bold;
            color: #007bff; /* Bootstrap primary color */
        }
    </style>
</head>
<body>
<!-- Header with Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">My Application</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('users*') ? 'active-nav-item' : '' }}" href="{{ url('/users') }}">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('positions*') ? 'active-nav-item' : '' }}" href="{{ url('/positions') }}">Positions</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">User Details</h1>

    <!-- Go Back Button -->
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-4">Go Back</a>

    <!-- User Information -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $user->name }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="card-text"><strong>Phone:</strong> {{ $user->phone }}</p>
            <p class="card-text"><strong>Position ID:</strong> {{ $user->position_id }}</p>
            @if($user->photo)
                <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="img-thumbnail" width="150" height="150">
            @endif
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
