<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — NutriTrack</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-bg">
  <div class="auth-card">
    <div class="auth-logo">
      <i class="fa-solid fa-seedling"></i>
      <h1>NutriTrack</h1>
      <p>Food Nutrition Analyzer</p>
    </div>

    @if($errors->any())
      <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        {{ $errors->first() }}
      </div>
    @endif

    @if(session('success'))
      <div class="alert alert-success">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <div class="form-group">
        <label class="form-label" for="nim">Student ID (NIM)</label>
        <input
          id="nim" name="nim" type="text" inputmode="numeric"
          class="form-control {{ $errors->has('nim') ? 'is-invalid' : '' }}"
          value="{{ old('nim') }}" placeholder="Enter your NIM" autofocus required>
      </div>
      <div class="form-group">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.25rem">
          <label class="form-label" for="password" style="margin-bottom:0">Password</label>
          <a href="{{ route('forgot-password') }}" style="font-size:.8rem">Forgot password?</a>
        </div>
        <input
          id="password" name="password" type="password"
          class="form-control"
          placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account?
      <a href="{{ route('register') }}">Create one</a>
    </div>
  </div>
</div>
</body>
</html>
