@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="page-header mt-3">
  <h1><i class="fa-solid fa-user" style="color:#16a34a"></i> Profile</h1>
  <p>Your account details and session information</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem">

  <div class="card">
    <div style="display:flex;flex-direction:column;align-items:center;padding:1rem 0">
      <div class="profile-avatar">
        {{ strtoupper(substr($user->name, 0, 1)) }}
      </div>
      <h2 style="font-size:1.15rem;font-weight:700;color:#1e293b">{{ $user->name }}</h2>
      <p class="text-muted text-sm">NIM: {{ $user->nim }}</p>
    </div>

    <div class="profile-detail mt-2">
      <div class="profile-row">
        <span class="profile-row-label"><i class="fa-solid fa-id-badge" style="margin-right:.4rem;color:#16a34a"></i>NIM</span>
        <span class="profile-row-value">{{ $user->nim }}</span>
      </div>
      <div class="profile-row">
        <span class="profile-row-label"><i class="fa-solid fa-user" style="margin-right:.4rem;color:#16a34a"></i>Name</span>
        <span class="profile-row-value">{{ $user->name }}</span>
      </div>
      <div class="profile-row">
        <span class="profile-row-label"><i class="fa-solid fa-calendar" style="margin-right:.4rem;color:#16a34a"></i>Joined</span>
        <span class="profile-row-value">{{ $user->created_at?->format('d M Y') ?? 'N/A' }}</span>
      </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:1.25rem">
      @csrf
      <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center">
        <i class="fa-solid fa-right-from-bracket"></i> Sign Out
      </button>
    </form>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-shield" style="color:#16a34a;margin-right:.4rem"></i>Current Session</span>
    </div>
    @if($userSession)
      <div class="profile-detail">
        <div class="profile-row">
          <span class="profile-row-label">Status</span>
          <span class="badge badge-normal"><i class="fa-solid fa-circle"></i> Active</span>
        </div>
        <div class="profile-row">
          <span class="profile-row-label">Login Time</span>
          <span class="profile-row-value">{{ $userSession->login_time->format('d M Y H:i') }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-row-label">Last Activity</span>
          <span class="profile-row-value">{{ $userSession->last_activity->diffForHumans() }}</span>
        </div>
        <div class="profile-row">
          <span class="profile-row-label">IP Address</span>
          <span class="profile-row-value">{{ $userSession->ip_address ?? 'Unknown' }}</span>
        </div>
      </div>
    @else
      <p class="text-muted text-sm">No active session info available.</p>
    @endif
  </div>

</div>
@endsection
