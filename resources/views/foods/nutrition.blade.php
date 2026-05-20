@extends('layouts.app')

@section('title', $food->menu)

@push('head-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
@endpush

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-top:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
  <a href="{{ route('foods.index') }}" class="btn btn-secondary btn-sm">
    <i class="fa-solid fa-arrow-left"></i> Back
  </a>
  <div>
    <h1 style="font-size:1.4rem;font-weight:700;color:#14532d">{{ $food->menu }}</h1>
    <p class="text-muted text-sm">Nutritional analysis &amp; daily standard comparison</p>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-list"></i></div>
    <div>
      <div class="stat-value">{{ $totalNutrients }}</div>
      <div class="stat-label">Total Nutrients</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    <div>
      <div class="stat-value">{{ $safeNutrients }}</div>
      <div class="stat-label">In Safe Range</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon {{ $safetyPct >= 70 ? 'green' : ($safetyPct >= 40 ? 'amber' : 'red') }}">
      <i class="fa-solid fa-shield-halved"></i>
    </div>
    <div>
      <div class="stat-value">{{ $safetyPct }}%</div>
      <div class="stat-label">Safety Score</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon amber"><i class="fa-solid fa-bolt"></i></div>
    <div>
      <div class="stat-value">{{ $food->energy_kj ?? '—' }}</div>
      <div class="stat-label">Energy (kJ)</div>
    </div>
  </div>
</div>

<!-- Chart -->
<div class="card mb-2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-chart-bar" style="color:#16a34a;margin-right:.4rem"></i>Nutrient Values vs Standard Range</span>
  </div>
  <div class="chart-wrap">
    <canvas id="nutritionChart"></canvas>
  </div>
</div>

<!-- Comparison Table -->
<div class="card mb-2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-table" style="color:#16a34a;margin-right:.4rem"></i>Detailed Comparison</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nutrient</th>
          <th>Value</th>
          <th>Min</th>
          <th>Max</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($comparisons as $row)
          <tr>
            <td class="food-name">{{ $row['nutrient'] }}</td>
            <td class="food-value">{{ $row['food_value'] !== null ? number_format((float)$row['food_value'], 2) : '—' }}</td>
            <td class="food-value text-muted">{{ $row['standard']->minimum ?? '—' }}</td>
            <td class="food-value text-muted">{{ $row['standard']->maximum ?? '—' }}</td>
            <td>
              @php $s = $row['status']; @endphp
              <span class="badge badge-{{ $s }}">
                @if($s === 'normal') <i class="fa-solid fa-check"></i>
                @elseif($s === 'excess') <i class="fa-solid fa-arrow-up"></i>
                @elseif($s === 'deficiency') <i class="fa-solid fa-arrow-down"></i>
                @else <i class="fa-solid fa-minus"></i>
                @endif
                {{ $row['status_text'] }}
              </span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Nutrient detail cards (standards with info) -->
@php
  $detailedRows = array_filter($comparisons, fn($r) =>
    $r['standard']->fungsi_zat || $r['standard']->dampak_kelebihan || $r['standard']->dampak_kekurangan
  );
@endphp
@if(count($detailedRows))
<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-circle-info" style="color:#16a34a;margin-right:.4rem"></i>Nutrient Details</span>
  </div>
  <div class="nutrient-grid">
    @foreach(array_slice($detailedRows, 0, 6) as $row)
      @php
        $val = $row['food_value'];
        $min = $row['standard']->minimum;
        $max = $row['standard']->maximum;
        $pct = 0;
        if ($val !== null && $max) {
          $pct = min(100, round(($val / $max) * 100));
        }
        $fillClass = match($row['status']) {
          'excess'     => 'fill-excess',
          'deficiency' => 'fill-deficiency',
          default      => 'fill-normal',
        };
      @endphp
      <div class="nutrient-card">
        <div class="nutrient-card-header">
          <span class="nutrient-name">{{ $row['nutrient'] }}</span>
          <span class="badge badge-{{ $row['status'] }}">{{ $row['status_text'] }}</span>
        </div>
        <div class="nutrient-bar-bg">
          <div class="nutrient-bar-fill {{ $fillClass }}" style="width:{{ $pct }}%"></div>
        </div>
        <div class="nutrient-range">
          Value: <strong>{{ $val !== null ? number_format((float)$val, 2) : 'N/A' }}</strong>
          @if($min || $max) &nbsp;| Range: {{ $min ?? '?' }} – {{ $max ?? '?' }} @endif
        </div>
        @if($row['standard']->fungsi_zat)
          <p class="text-xs text-muted mt-1"><strong>Function:</strong> {{ $row['standard']->fungsi_zat }}</p>
        @endif
        @if($row['standard']->rekomendasi_harian)
          <p class="text-xs text-muted mt-1"><strong>Daily rec.:</strong> {{ $row['standard']->rekomendasi_harian }}</p>
        @endif
      </div>
    @endforeach
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const comparisons = @json($comparisons);

const labels    = comparisons.map(c => c.nutrient);
const values    = comparisons.map(c => c.food_value !== null ? parseFloat(c.food_value) : null);
const minValues = comparisons.map(c => c.standard.minimum !== null ? parseFloat(c.standard.minimum) : null);
const maxValues = comparisons.map(c => c.standard.maximum !== null ? parseFloat(c.standard.maximum) : null);

const bgColors = comparisons.map(c => {
  if (c.status === 'normal')      return 'rgba(22,163,74,.7)';
  if (c.status === 'excess')      return 'rgba(220,38,38,.7)';
  if (c.status === 'deficiency')  return 'rgba(217,119,6,.7)';
  return 'rgba(100,116,139,.5)';
});

new Chart(document.getElementById('nutritionChart'), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [
      {
        label: 'Food Value',
        data: values,
        backgroundColor: bgColors,
        borderRadius: 4,
        order: 1,
      },
      {
        label: 'Max Standard',
        data: maxValues,
        type: 'line',
        borderColor: 'rgba(220,38,38,.6)',
        borderDash: [5,3],
        pointRadius: 0,
        fill: false,
        order: 0,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'top' },
      tooltip: {
        callbacks: {
          afterBody: function(ctx) {
            const i    = ctx[0].dataIndex;
            const comp = comparisons[i];
            return [
              `Status: ${comp.status_text}`,
              `Min: ${comp.standard.minimum ?? 'N/A'}`,
              `Max: ${comp.standard.maximum ?? 'N/A'}`,
            ];
          },
        },
      },
    },
    scales: {
      x: { ticks: { maxRotation: 45, font: { size: 10 } } },
      y: { beginAtZero: true },
    },
  },
});
</script>
@endpush
