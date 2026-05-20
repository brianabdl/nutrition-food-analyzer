@extends('layouts.app')

@section('title', 'Compare Foods')

@push('head-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
@endpush

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-top:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
  <a href="{{ route('foods.index') }}" class="btn btn-secondary btn-sm">
    <i class="fa-solid fa-arrow-left"></i> Back
  </a>
  <div>
    <h1 style="font-size:1.4rem;font-weight:700;color:#14532d">Food Comparison</h1>
    <p class="text-muted text-sm">Comparing {{ $foods->count() }} foods side by side</p>
  </div>
</div>

<!-- Food chips -->
<div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem">
  @foreach($foods as $food)
    <span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#14532d;padding:.3rem .75rem;border-radius:20px;font-size:.85rem;font-weight:500">
      <i class="fa-solid fa-bowl-food" style="margin-right:.3rem;color:#16a34a"></i>{{ $food->menu }}
    </span>
  @endforeach
</div>

<!-- Chart -->
<div class="card mb-2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-chart-bar" style="color:#16a34a;margin-right:.4rem"></i>Multi-Nutrient Comparison Chart</span>
  </div>
  <div class="chart-wrap" style="height:380px">
    <canvas id="multiComparisonChart"></canvas>
  </div>
</div>

<!-- Comparison table -->
<div class="card mb-2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-table" style="color:#16a34a;margin-right:.4rem"></i>Nutrient Values</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nutrient</th>
          <th>Standard Range</th>
          @foreach($foods as $food)
            <th>{{ Str::limit($food->menu, 20) }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($standards as $nutrient => $standard)
          @php $col = \App\Models\Food::NUTRIENT_MAP[$nutrient] ?? null; @endphp
          <tr>
            <td class="food-name">{{ $nutrient }}</td>
            <td class="text-xs text-muted">
              {{ $standard->minimum ?? '?' }} – {{ $standard->maximum ?? '?' }}
            </td>
            @foreach($foods as $food)
              @php
                $val = $col ? $food->$col : null;
                $min = $standard->minimum;
                $max = $standard->maximum;
                $status = 'no-standard';
                if ($min !== null || $max !== null) {
                  if ($val === null) $status = 'no-data';
                  elseif ($max !== null && $val > $max) $status = 'excess';
                  elseif ($min !== null && $val < $min) $status = 'deficiency';
                  else $status = 'normal';
                }
              @endphp
              <td>
                <span class="{{ in_array($status, ['normal','excess','deficiency']) ? 'badge badge-'.$status : 'text-muted text-xs' }}">
                  {{ $val !== null ? number_format((float)$val, 2) : '—' }}
                </span>
              </td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Insights -->
@if(count($insights))
<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-lightbulb" style="color:#d97706;margin-right:.4rem"></i>Insights</span>
  </div>
  <div class="insights-list">
    @foreach($insights as $insight)
      <div class="insight-item">
        <i class="fa-solid fa-{{ $insight['type'] === 'energy' ? 'bolt' : 'dumbbell' }}"></i>
        <span>{{ $insight['message'] }}</span>
      </div>
    @endforeach
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const chartData = @json($chartData);

const CHART_COLORS = [
  'rgba(22,163,74,.75)',
  'rgba(37,99,235,.75)',
  'rgba(217,119,6,.75)',
  'rgba(220,38,38,.75)',
  'rgba(139,92,246,.75)',
];

chartData.datasets.forEach(function(ds, i) {
  ds.backgroundColor = CHART_COLORS[i % CHART_COLORS.length];
  ds.borderColor     = CHART_COLORS[i % CHART_COLORS.length].replace('.75', '1');
  ds.borderWidth     = 1;
  ds.borderRadius    = 3;
});

new Chart(document.getElementById('multiComparisonChart'), {
  type: 'bar',
  data: chartData,
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'top' },
    },
    scales: {
      x: {
        ticks: { maxRotation: 45, font: { size: 10 } },
      },
      y: { beginAtZero: true },
    },
  },
});
</script>
@endpush
