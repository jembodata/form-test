{{-- resources/views/livewire/report-stats.blade.php --}}
<div class="p-1 text-black bg-transparent" wire:poll.10s>
    <div class="mx-auto w-full max-w-7xl space-y-4">
      <header>
        {{-- <h2 class="text-2xl font-semibold tracking-tight">Laporan — KPI</h2>
        <p class="text-sm text-slate-500">Counts by shift &amp; total</p> --}}
      </header>
  
      {{-- Pure responsive grid: 1 col (mobile) → 2 (tablet) → 4 (desktop) --}}
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
        @php $numClass = 'kpi-number text-4xl sm:text-5xl'; @endphp
  
        {{-- Total --}}
        <div class="card">
          <div class="accent-bar accent-slate"></div>
          <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-2">
              {{-- inline employee + clock icon (neutral) --}}
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                   fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round"
                   class="h-5 w-5 text-slate-500">
                <circle cx="8" cy="7" r="4"/>
                <path d="M2 21v-2a6 6 0 0 1 6-6h0a6 6 0 0 1 6 6v2"/>
                <circle cx="18" cy="13" r="5"/>
                <path d="M18 11v2l1.5 1.5"/>
              </svg>
              <div class="card-title">Laporan</div>
            </div>
            <span class="badge badge-slate">TOTAL</span>
          </div>
          <div class="card-content">
            <div class="{{ $numClass }}">{{ $this->stats['total'] }}</div>
          </div>
        </div>
  
        {{-- Shift 1 --}}
        <div class="card">
          <div class="accent-bar accent-rose"></div>
          <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                   fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round"
                   class="h-5 w-5 text-rose-500">
                <circle cx="8" cy="7" r="4"/>
                <path d="M2 21v-2a6 6 0 0 1 6-6h0a6 6 0 0 1 6 6v2"/>
                <circle cx="18" cy="13" r="5"/>
                <path d="M18 11v2l1.5 1.5"/>
              </svg>
              <div class="card-title">Shift 1</div>
            </div>
            <span class="badge badge-rose">S1</span>
          </div>
          <div class="card-content">
            <div class="{{ $numClass }} text-rose-900">{{ $this->stats['s1'] }}</div>
          </div>
        </div>
  
        {{-- Shift 2 --}}
        <div class="card">
          <div class="accent-bar accent-amber"></div>
          <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                   fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round"
                   class="h-5 w-5 text-amber-500">
                <circle cx="8" cy="7" r="4"/>
                <path d="M2 21v-2a6 6 0 0 1 6-6h0a6 6 0 0 1 6 6v2"/>
                <circle cx="18" cy="13" r="5"/>
                <path d="M18 11v2l1.5 1.5"/>
              </svg>
              <div class="card-title">Shift 2</div>
            </div>
            <span class="badge badge-amber">S2</span>
          </div>
          <div class="card-content">
            <div class="{{ $numClass }} text-amber-900">{{ $this->stats['s2'] }}</div>
          </div>
        </div>
  
        {{-- Shift 3 --}}
        <div class="card">
          <div class="accent-bar accent-emerald"></div>
          <div class="card-header flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                   fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round"
                   class="h-5 w-5 text-emerald-500">
                <circle cx="8" cy="7" r="4"/>
                <path d="M2 21v-2a6 6 0 0 1 6-6h0a6 6 0 0 1 6 6v2"/>
                <circle cx="18" cy="13" r="5"/>
                <path d="M18 11v2l1.5 1.5"/>
              </svg>
              <div class="card-title">Shift 3</div>
            </div>
            <span class="badge badge-emerald">S3</span>
          </div>
          <div class="card-content">
            <div class="{{ $numClass }} text-emerald-900">{{ $this->stats['s3'] }}</div>
          </div>
        </div>
      </div>
  
      {{-- <div wire:loading class="text-xs text-slate-500 animate-pulse">Updating…</div> --}}
    </div>
  </div>
  