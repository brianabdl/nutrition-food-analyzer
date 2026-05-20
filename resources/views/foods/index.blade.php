@extends('layouts.app')

@section('title', 'Foods')

@section('content')
<div class="page-header mt-3">
  <h1><i class="fa-solid fa-utensils" style="color:#16a34a"></i> Food Nutrition Database</h1>
  <p>Browse {{ number_format($foods->total()) }} foods — select up to {{ config('nutrition.max_comparison') }} to compare</p>
</div>

<div class="card">
  <form class="search-bar" id="searchForm" method="get" action="{{ route('foods.index') }}">
    <div class="search-input-wrap">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input
        type="text" id="searchInput" name="search" class="search-input"
        placeholder="Search food name…"
        value="{{ $search }}" autocomplete="off">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
    <button type="button" class="btn btn-secondary" id="filterToggleBtn">
      <i class="fa-solid fa-sliders"></i> Filter
      <span id="filterBadge" class="filter-badge" style="display:none"></span>
    </button>
    @if($search)
      <a href="{{ route('foods.index') }}" class="btn btn-secondary">Clear</a>
    @endif
  </form>

  <div class="filter-panel" id="filterPanel">
    <div class="filter-group">
      <label>Energy (kJ)</label>
      <div class="filter-inputs">
        <input type="number" id="min_energy_kj" class="filter-input" placeholder="Min" min="0" step="any">
        <span class="text-muted text-xs">–</span>
        <input type="number" id="max_energy_kj" class="filter-input" placeholder="Max" min="0" step="any">
      </div>
    </div>
    <div class="filter-group">
      <label>Protein (g)</label>
      <div class="filter-inputs">
        <input type="number" id="min_protein_g" class="filter-input" placeholder="Min" min="0" step="any">
        <span class="text-muted text-xs">–</span>
        <input type="number" id="max_protein_g" class="filter-input" placeholder="Max" min="0" step="any">
      </div>
    </div>
    <div class="filter-group">
      <label>Fat (g)</label>
      <div class="filter-inputs">
        <input type="number" id="min_fat_g" class="filter-input" placeholder="Min" min="0" step="any">
        <span class="text-muted text-xs">–</span>
        <input type="number" id="max_fat_g" class="filter-input" placeholder="Max" min="0" step="any">
      </div>
    </div>
    <div class="filter-group">
      <label>Carbs (g)</label>
      <div class="filter-inputs">
        <input type="number" id="min_carbohydrates_g" class="filter-input" placeholder="Min" min="0" step="any">
        <span class="text-muted text-xs">–</span>
        <input type="number" id="max_carbohydrates_g" class="filter-input" placeholder="Max" min="0" step="any">
      </div>
    </div>
    <div class="filter-actions">
      <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">
        <i class="fa-solid fa-check"></i> Apply
      </button>
      <button type="button" class="btn btn-secondary btn-sm" id="clearFiltersBtn">
        <i class="fa-solid fa-xmark"></i> Clear
      </button>
    </div>
  </div>

  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
    <span class="text-sm text-muted">
      Showing <span id="showingStart">{{ $foods->firstItem() ?? 0 }}</span>–<span id="showingEnd">{{ $foods->lastItem() ?? 0 }}</span>
      of <span id="totalCount">{{ number_format($foods->total()) }}</span> results
    </span>
    <span class="text-xs text-muted" style="background:#f0fdf4;border:1px solid #bbf7d0;padding:.25rem .6rem;border-radius:6px;color:#16a34a">
      <i class="fa-solid fa-circle-info"></i>
      Tick 2–{{ config('nutrition.max_comparison') }} foods then click <strong>Compare</strong> to compare side-by-side
    </span>
  </div>

  <div class="table-wrap">
    <table id="foodTable">
      <thead>
        <tr>
          <th style="width:36px"></th>
          <th class="th-sortable sort-active" data-sort="menu">Food Name <i class="fa-solid fa-sort-up sort-icon"></i></th>
          <th class="th-sortable" data-sort="energy_kj">Energy (kJ) <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="th-sortable" data-sort="protein_g">Protein (g) <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="th-sortable" data-sort="fat_g">Fat (g) <i class="fa-solid fa-sort sort-icon"></i></th>
          <th class="th-sortable" data-sort="carbohydrates_g">Carbs (g) <i class="fa-solid fa-sort sort-icon"></i></th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="foodTableBody">
        @foreach($foods as $food)
          <tr>
            <td>
              <input type="checkbox" class="food-checkbox" value="{{ $food->menu }}" title="{{ $food->menu }}">
            </td>
            <td class="food-name">{{ $food->menu }}</td>
            <td class="food-value">{{ $food->energy_kj ?? '—' }}</td>
            <td class="food-value">{{ $food->protein_g ?? '—' }}</td>
            <td class="food-value">{{ $food->fat_g ?? '—' }}</td>
            <td class="food-value">{{ $food->carbohydrates_g ?? '—' }}</td>
            <td>
              <a href="{{ url('/foods/'.urlencode($food->menu).'/nutrition') }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-chart-bar"></i> Analyze
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="pagination-wrap" id="paginationWrap">
    <span class="text-sm text-muted">
      Page <span id="currentPage">{{ $foods->currentPage() }}</span> of <span id="totalPages">{{ $foods->lastPage() }}</span>
    </span>
    <div id="paginationLinks">
      {{ $foods->links('vendor.pagination.custom') }}
    </div>
  </div>
</div>

<!-- Comparison toolbar -->
<div class="comparison-toolbar" id="comparisonToolbar">
  <!-- <div> -->
  <i class="fa-solid fa-scale-balanced"></i>
  <span><i class="fa-solid fa-check-square"></i> <span id="selectedCount">0</span> foods selected</span>
  <button class="btn btn-primary btn-sm" id="compareBtn">
    <i class="fa-solid fa-chart-column"></i> Compare Side-by-Side
  </button>
  <button class="btn btn-secondary btn-sm" id="clearSelectionBtn"><i class="fa-solid fa-xmark"></i> Clear</button>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  let currentPage    = {{ $foods->currentPage() }};
  let currentSearch  = {!! json_encode($search) !!};
  let currentSort    = 'menu';
  let currentSortDir = 'asc';
  let selectedFoods  = new Set();

  // ── Search ──────────────────────────────────────────────
  let searchTimer;
  $('#searchInput').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      currentSearch = $(this).val().trim();
      currentPage   = 1;
      loadFoods();
    }, 350);
  });

  $('#searchForm').on('submit', function (e) {
    e.preventDefault();
    currentSearch = $('#searchInput').val().trim();
    currentPage   = 1;
    loadFoods();
  });

  // ── Sort ────────────────────────────────────────────────
  $('#foodTable').on('click', '.th-sortable', function () {
    const col = $(this).data('sort');
    if (currentSort === col) {
      currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
    } else {
      currentSort    = col;
      currentSortDir = 'asc';
    }
    currentPage = 1;
    updateSortIcons();
    loadFoods();
  });

  function updateSortIcons() {
    $('.th-sortable').each(function () {
      const col  = $(this).data('sort');
      const icon = $(this).find('.sort-icon');
      const active = col === currentSort;
      $(this).toggleClass('sort-active', active);
      if (active) {
        icon.attr('class', 'fa-solid sort-icon ' + (currentSortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down'));
      } else {
        icon.attr('class', 'fa-solid fa-sort sort-icon');
      }
    });
  }

  // ── Filter panel ────────────────────────────────────────
  $('#filterToggleBtn').on('click', function () {
    $('#filterPanel').toggleClass('open');
    $(this).find('i.fa-sliders').toggleClass('fa-sliders fa-sliders-active');
  });

  $('#applyFiltersBtn').on('click', function () {
    currentPage = 1;
    loadFoods();
    updateFilterBadge();
  });

  $('#clearFiltersBtn').on('click', function () {
    $('.filter-input').val('');
    currentPage = 1;
    loadFoods();
    updateFilterBadge();
  });

  function getFilterParams() {
    const params = {};
    ['energy_kj', 'protein_g', 'fat_g', 'carbohydrates_g'].forEach(function (col) {
      const min = $('#min_' + col).val().trim();
      const max = $('#max_' + col).val().trim();
      if (min !== '') params['min_' + col] = min;
      if (max !== '') params['max_' + col] = max;
    });
    return params;
  }

  function countActiveFilters() {
    return ['energy_kj', 'protein_g', 'fat_g', 'carbohydrates_g'].filter(function (col) {
      return $('#min_' + col).val().trim() !== '' || $('#max_' + col).val().trim() !== '';
    }).length;
  }

  function updateFilterBadge() {
    const n = countActiveFilters();
    if (n > 0) {
      $('#filterBadge').text(n).show();
    } else {
      $('#filterBadge').hide();
    }
  }

  // ── Load foods via AJAX ──────────────────────────────────
  function loadFoods() {
    const params = Object.assign(
      { search: currentSearch, page: currentPage, sort_by: currentSort, sort_dir: currentSortDir },
      getFilterParams()
    );

    $.getJSON(APP_ROUTES.foodsSearch, params, function (res) {
      if (!res.success) return;
      const d = res.data;

      $('#showingStart').text(d.pagination.showingStart);
      $('#showingEnd').text(d.pagination.showingEnd);
      $('#totalCount').text(d.pagination.totalCount.toLocaleString());
      $('#currentPage').text(d.pagination.currentPage);
      $('#totalPages').text(d.pagination.totalPages);

      let rows = '';
      d.foods.forEach(function (food) {
        const name    = food['Menu'];
        const checked = selectedFoods.has(name) ? 'checked' : '';
        const energy  = food['Energy (kJ)']       != null ? parseFloat(food['Energy (kJ)']).toFixed(2)       : '—';
        const protein = food['Protein (g)']        != null ? parseFloat(food['Protein (g)']).toFixed(2)       : '—';
        const fat     = food['Fat (g)']            != null ? parseFloat(food['Fat (g)']).toFixed(2)           : '—';
        const carbs   = food['Carbohydrates (g)']  != null ? parseFloat(food['Carbohydrates (g)']).toFixed(2) : '—';
        const href    = APP_ROUTES.nutritionBase + '/' + encodeURIComponent(name) + '/nutrition';
        rows += `<tr>
          <td><input type="checkbox" class="food-checkbox" value="${escHtml(name)}" ${checked}></td>
          <td class="food-name">${escHtml(name)}</td>
          <td class="food-value">${energy}</td>
          <td class="food-value">${protein}</td>
          <td class="food-value">${fat}</td>
          <td class="food-value">${carbs}</td>
          <td><a href="${href}" class="btn btn-outline btn-sm"><i class="fa-solid fa-chart-bar"></i> Analyze</a></td>
        </tr>`;
      });
      $('#foodTableBody').html(rows || '<tr><td colspan="7" class="text-center text-muted" style="padding:2rem">No foods found.</td></tr>');

      renderPagination(d.pagination);

      const url = new URL(window.location);
      url.searchParams.set('search', currentSearch);
      url.searchParams.set('page', currentPage);
      window.history.replaceState({}, '', url);
    });
  }

  function renderPagination(p) {
    if (p.totalPages <= 1) { $('#paginationLinks').html(''); return; }
    let html = '<ul class="pagination">';
    if (p.currentPage > 1) {
      html += `<li><a href="#" data-page="${p.currentPage - 1}"><i class="fa-solid fa-chevron-left"></i></a></li>`;
    } else {
      html += `<li class="disabled"><span><i class="fa-solid fa-chevron-left"></i></span></li>`;
    }
    const start = Math.max(1, p.currentPage - 2);
    const end   = Math.min(p.totalPages, p.currentPage + 2);
    if (start > 1) html += `<li><a href="#" data-page="1">1</a></li>${start > 2 ? '<li class="disabled"><span>…</span></li>' : ''}`;
    for (let i = start; i <= end; i++) {
      if (i === p.currentPage) html += `<li class="active"><span>${i}</span></li>`;
      else html += `<li><a href="#" data-page="${i}">${i}</a></li>`;
    }
    if (end < p.totalPages) {
      html += `${end < p.totalPages - 1 ? '<li class="disabled"><span>…</span></li>' : ''}<li><a href="#" data-page="${p.totalPages}">${p.totalPages}</a></li>`;
    }
    if (p.currentPage < p.totalPages) {
      html += `<li><a href="#" data-page="${p.currentPage + 1}"><i class="fa-solid fa-chevron-right"></i></a></li>`;
    } else {
      html += `<li class="disabled"><span><i class="fa-solid fa-chevron-right"></i></span></li>`;
    }
    html += '</ul>';
    $('#paginationLinks').html(html);
  }

  $(document).on('click', '#paginationLinks a[data-page]', function (e) {
    e.preventDefault();
    currentPage = parseInt($(this).data('page'));
    loadFoods();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ── Checkboxes & comparison toolbar ──────────────────────
  $(document).on('change', '.food-checkbox', function () {
    const name = $(this).val();
    if ($(this).is(':checked')) {
      if (selectedFoods.size >= MAX_COMPARISON) {
        $(this).prop('checked', false);
        alert('You can compare up to ' + MAX_COMPARISON + ' foods at a time.');
        return;
      }
      selectedFoods.add(name);
    } else {
      selectedFoods.delete(name);
    }
    updateToolbar();
  });

  function updateToolbar() {
    const n = selectedFoods.size;
    $('#selectedCount').text(n);
    $('#comparisonToolbar').css('display', n >= 2 ? 'flex' : 'none');
  }

  $('#compareBtn').on('click', function () {
    if (selectedFoods.size < 2) return;
    const params = Array.from(selectedFoods).map(encodeURIComponent).join(',');
    window.location = APP_ROUTES.comparison + '?foods=' + params;
  });

  $('#clearSelectionBtn').on('click', function () {
    selectedFoods.clear();
    $('.food-checkbox').prop('checked', false);
    updateToolbar();
  });

  function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
});
</script>
@endpush
