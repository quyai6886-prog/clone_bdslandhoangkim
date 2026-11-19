<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · MrKimLand</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favico.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favico.png') }}">
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/light-theme.css') }}?v={{ filemtime(public_path('css/light-theme.css')) }}">
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="MrKimLand - Dashboard">
        <img class="brand-logo" src="{{ asset('images/logo_and_nameweb.png') }}" alt="MrKimLand">
    </a>
    <button class="mobile-nav-toggle" type="button" data-mobile-nav-toggle aria-expanded="false" aria-controls="main-navigation" aria-label="Mở menu">☰</button>
    <nav class="nav-links" id="main-navigation" data-mobile-nav>
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="{{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}">Hoạt động</a>
        @can('admin')<a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Người dùng</a>@endcan
        <details class="account-menu">
            <summary>♟ {{ auth()->user()->name }}</summary>
            <div class="account-popover">
                <div class="account-summary"><strong>{{ auth()->user()->username ?: auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small><span class="role-chip">{{ strtoupper(auth()->user()->role) }}</span></div>
                <a href="{{ route('profile.show') }}">👤 Hồ sơ</a>
                <a href="{{ route('notes.history') }}">📝 Lịch sử ghi chú</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">Đăng xuất</button></form>
            </div>
        </details>
    </nav>
</header>

@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif

<main>@yield('content')</main>
<script src="{{ asset('js/crm.js') }}?v={{ filemtime(public_path('js/crm.js')) }}"></script>
@stack('scripts')
</body>
</html>
