<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'NutriTrack') — Food Nutrition Analyzer</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('head-scripts')
</head>
<body>

<nav class="navbar">
  <a href="{{ route('foods.index') }}" class="navbar-brand">
    <i class="fa-solid fa-seedling"></i>
    NutriTrack
  </a>

  <div class="navbar-menu">
    <a href="{{ route('foods.index') }}" class="nav-link {{ request()->routeIs('foods.index') ? 'active' : '' }}">
      <i class="fa-solid fa-utensils"></i> Foods
    </a>
    <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
      <i class="fa-solid fa-circle-info"></i> About
    </a>
  </div>

  <div class="navbar-right">
    <a href="{{ route('profile') }}" class="user-chip" style="text-decoration:none">
      <i class="fa-solid fa-user"></i>
      {{ auth()->user()->name }}
    </a>

    <form method="POST" action="{{ route('logout') }}" style="margin:0">
      @csrf
      <button type="submit" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i></button>
    </form>
  </div>
</nav>

<div class="container">
  @if(session('error'))
    <div class="alert alert-danger mt-2"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="alert alert-success mt-2"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
  @endif

  @yield('content')
</div>

<footer class="footer">
  <p>Data sourced from <a href="https://www.kaggle.com" target="_blank">Kaggle</a> &mdash; Indonesian Food Nutrition Dataset</p>
  <p style="margin-top:.25rem"><a href="{{ route('about') }}">About the Team</a></p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
  const APP_ROUTES = {
    foodsSearch:   "{{ route('api.foods.search') }}",
    comparison:    "{{ route('comparison') }}",
    nutritionBase: "{{ url('/foods') }}",
    foodsIndex:    "{{ route('foods.index') }}",
  };
  const MAX_COMPARISON = {{ config('nutrition.max_comparison') }};
</script>
@stack('scripts')
</body>
</html>
