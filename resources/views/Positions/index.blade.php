<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Positions List</title>
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
        <a class="navbar-brand" href="#">ABZ.AGENCY LARAVEL TEST</a>
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
    <h1 class="mb-4">Positions List</h1>

    <!-- Positions Table -->
    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Position Name</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($positions as $position)
            <tr>
                <td>{{ $position->id }}</td>
                <td>{{ $position->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>



<!-- Bootstrap JS and dependencies (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
