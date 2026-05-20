<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — NutriTrack</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="auth-bg">
  <div class="auth-card">
    <div class="auth-logo">
      <i class="fa-solid fa-seedling"></i>
      <h1>Reset Password</h1>
      <p>Verify your identity to continue</p>
    </div>

    @if($errors->any())
      <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('password.reset') }}">
      @csrf
      <div class="form-group">
        <label class="form-label" for="nim">Student ID (NIM)</label>
        <input
          id="nim" name="nim" type="text" inputmode="numeric"
          class="form-control {{ $errors->has('nim') ? 'is-invalid' : '' }}"
          value="{{ old('nim') }}" placeholder="Enter your NIM" autofocus required>
      </div>
      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <input
          id="name" name="name" type="text"
          class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
          value="{{ old('name') }}" placeholder="Enter your registered full name" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">New Password</label>
        <input
          id="password" name="password" type="password"
          class="form-control"
          placeholder="Minimum 6 characters" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm New Password</label>
        <input
          id="password_confirmation" name="password_confirmation" type="password"
          class="form-control"
          placeholder="Repeat new password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem">
        <i class="fa-solid fa-key"></i> Reset Password
      </button>
    </form>

    <div class="auth-footer">
      Remembered your password?
      <a href="{{ route('login') }}">Sign in</a>
    </div>
  </div>
</div>
</body>
</html>
