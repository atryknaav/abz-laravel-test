<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
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
    <h1 class="mb-4">User List</h1>
    
    <!-- User Registration Form -->
    <div class="mb-4">
        <h2>Register New User</h2>
        <form action="/users" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" >
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" >
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" >
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="position_id" class="form-label">Position</label>
                <select class="form-select @error('position_id') is-invalid @enderror" id="position_id" name="position_id" >
                    <option value="">Select Position</option>
                    <option value="1">1 - Lawyer</option>
                    <option value="2">2 - Content Manager</option>
                    <option value="3">3 - Security</option>
                    <option value="4">4 - Designer</option>
                </select>
                @error('position_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Photo</label>
                <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*" >
                @error('photo')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <form action="/token" method="get">
            <button type="submit" style="
                background-color: green; 
                border-radius: 10px; 
                color: white; 
                padding: 10px 20px; 
                font-size: 16px; 
                font-weight: bold; 
                border: none; 
                cursor: pointer; 
                transition: background-color 0.3s ease;
                margin-top: 2px;">
                GET ACCESS
            </button>
        </form>
    </div>
    
    <!-- User List Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Position</th>
            <th>With us since</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr>
                <a href={{"/users/" . $user->id}} style="position: absolute; width: 100%;"></a>
                    <td>{{ $user->id }}</td>
                    <td><img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" width="50" height="50" style="border: lightgray solid 1px;"></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone }}</td>
                    <td>{{ $user->position->name }}</td>
                    <td>{{ substr($user->email_verified_at, 0, 10) }}</td>
                    <td><a href={{"/users/" . $user->id}} style="text-decoration: none;">View</td>
                </tr>
        @endforeach
        </tbody>
        A{{$usersResponse['count']}}
    </table>
    <form action="/users?page={{$usersResponse['page']}}&count={{$usersResponse['count']}}">
        <label for="count">Users per page</label>
        <input type="text" name="count" value='{{$usersResponse['count']}}'>
        <input type="text" name="page" value='{{$usersResponse['page']}}' style="display: none;">
        <button type="submit">Apply</button>
    </form>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $users->links('pagination::bootstrap-4') }}
    </div>
</div>

<!-- Bootstrap JS and dependencies (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
