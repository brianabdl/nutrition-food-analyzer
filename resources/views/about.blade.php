@extends('layouts.app')

@section('title', 'About')

@section('content')
<div class="page-header mt-3">
  <h1><i class="fa-solid fa-circle-info" style="color:#16a34a"></i> About NutriTrack</h1>
  <p>Food Nutrition Analyzer — Web Programming Project</p>
</div>

<div style="display:grid;gap:1.5rem">

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-seedling" style="color:#16a34a;margin-right:.4rem"></i>About This Project</span>
    </div>
    <p style="color:#475569;font-size:.9rem;line-height:1.8">
      NutriTrack is a web-based food nutrition analyzer built as a campus project for the Web Programming course.
      It allows users to search an Indonesian food nutrition database, view detailed nutrient analysis for each food,
      and compare multiple foods side by side against daily standard nutritional requirements for children aged 1–5 years.
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-top:1.25rem">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-database"></i></div>
        <div>
          <div class="stat-value">1,000+</div>
          <div class="stat-label">Food Items</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-vials"></i></div>
        <div>
          <div class="stat-value">21</div>
          <div class="stat-label">Nutrients Tracked</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-scale-balanced"></i></div>
        <div>
          <div class="stat-value">5</div>
          <div class="stat-label">Max Comparison</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-layer-group" style="color:#16a34a;margin-right:.4rem"></i>Tech Stack</span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem">
      @foreach(['Laravel 11', 'PHP 8.2', 'MySQL', 'jQuery 3.7', 'Chart.js 3.9', 'Font Awesome 6'] as $tech)
        <span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#14532d;padding:.3rem .75rem;border-radius:20px;font-size:.85rem;font-weight:500">
          {{ $tech }}
        </span>
      @endforeach
    </div>
  </div>


  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-brands fa-kaggle" style="color:#20beff;margin-right:.4rem"></i>Data Source</span>
    </div>
    <p class="text-sm" style="color:#475569">
      Nutrition data sourced from the
      <a href="https://www.kaggle.com" target="_blank">Kaggle</a>
      Indonesian Food Nutrition Dataset.
      Standards are based on daily nutritional requirements for children aged 1–5 years.
    </p>
  </div>

</div>
@endsection
