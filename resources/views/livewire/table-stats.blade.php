<div wire:init="load" wire:poll.10s class="w-full">
    <!-- Container: fixed width & centered -->
    <div class="mx-auto w-full max-w-[1024px] px-3 sm:px-4">
        <!-- Header -->
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            {{-- <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight">TableStats</h2>
                <p class="text-sm text-gray-500">Latest Laporan — minimal, cepat, dan energizing.</p>
            </div>
            <div class="text-sm text-gray-600">
                Total: <span class="font-medium">{{ number_format($total) }}</span>
            </div> --}}
        </div>

        <!-- Card -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">

            <!-- TABLE (tablet & desktop) -->
            <div class="hidden md:block">
                <!-- IMPORTANT: overflow-x-auto ensures horizontal scroll when needed -->
                <div class="relative overflow-x-auto">
                    <!-- IMPORTANT: min-w forces scroll on tablet when cols overflow -->
                    <table class="lg:min-w-0 table-fixed text-sm">
                        <thead class="bg-gray-50/80 text-gray-600">
                            <tr>
                                <th class="w-32 px-4 py-3 text-left font-medium">Plant</th>
                                <th class="w-40 px-4 py-3 text-left font-medium">Mesin</th>
                                <th class="w-20 px-4 py-3 text-left font-medium">Shift</th>
                                <th class="w-[28rem] px-4 py-3 text-left font-medium">Karyawan / NIK / OP</th>
                                <th class="w-40 px-4 py-3 text-center font-medium">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 bg-white">
                            @if (!$readyToLoad)
                                @for ($i = 0; $i < 5; $i++)
                                    <tr>
                                        @for ($j = 0; $j < 5; $j++)
                                            <td class="px-4 py-3">
                                                <div class="h-4 w-24 rounded bg-gray-100 animate-pulse"></div>
                                            </td>
                                        @endfor
                                    </tr>
                                @endfor
                            @else
                                @forelse($laporan as $row)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 truncate">{{ $row->mesins->nama_plant ?? '—' }}</td>
                                        <td class="px-4 py-3 truncate">{{ $row->mesins->nama_mesin ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $row->shift ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="space-y-1">
                                                {{-- Karyawan (badge biru) --}}
                                                @php $names = $row->karyawans->pluck('nama')->filter()->values(); @endphp
                                                @if ($names->isNotEmpty())
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach ($names as $nm)
                                                            <span
                                                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                                                                bg-blue-50 text-blue-700 border-blue-200">
                                                                {{ $nm }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif

                                                {{-- NIK (badge abu) --}}
                                                @php $niks = $row->karyawans->pluck('nik')->filter()->values(); @endphp
                                                @if ($niks->isNotEmpty())
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach ($niks as $nik)
                                                            <span
                                                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                                                                bg-gray-50 text-gray-700 border-gray-200">
                                                                {{ $nik }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                {{-- OP (badge hijau) --}}
                                                @php
                                                    // Ambil hanya field 'op' dari setiap item detail_produksi
                                                    $ops = collect($row->detail_produksi ?? [])
                                                        ->map(fn($item) => is_array($item) ? $item['op'] ?? null : null)
                                                        ->filter()
                                                        ->values();
                                                @endphp

                                                @if ($ops->isNotEmpty())
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach ($ops as $op)
                                                            <span
                                                                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                bg-emerald-50 text-emerald-700 border-emerald-200">
                                                                {{ $op }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a
                                                href="{{ route('laporan.pdf', $row) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-7 4h8M7 8h10M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h9l5 5v9a2 2 0 01-2 2z"/>
                                                </svg>
                                                Stream PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No data yet.</td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MOBILE (<md): stack cards -->
            <div class="md:hidden divide-y divide-gray-100">
                @if (!$readyToLoad)
                    @for ($i = 0; $i < 3; $i++)
                        <div class="p-4 space-y-2">
                            <div class="h-4 w-1/3 bg-gray-100 rounded animate-pulse"></div>
                            <div class="h-4 w-2/3 bg-gray-100 rounded animate-pulse"></div>
                            <div class="h-4 w-1/2 bg-gray-100 rounded animate-pulse"></div>
                            <div class="h-9 w-28 bg-gray-100 rounded animate-pulse"></div>
                        </div>
                    @endfor
                @else
                    @forelse($laporan as $row)
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 space-y-2">
                                    <div class="text-sm text-gray-700">
                                        <span class="font-medium">Plant:</span> {{ $row->mesins->nama_plant ?? '—' }},
                                        <span class="font-medium">Mesin:</span> {{ $row->mesins->nama_mesin ?? '—' }},
                                        <span class="font-medium">Shift:</span> {{ $row->shift ?? '—' }}
                                    </div>

                                    {{-- Badges --}}
                                    @php $names = $row->karyawans->pluck('nama')->filter()->values(); @endphp
                                    @if ($names->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($names as $nm)
                                                <span
                                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                                                    bg-blue-50 text-blue-700 border-blue-200">
                                                    {{ $nm }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @php $niks = $row->karyawans->pluck('nik')->filter()->values(); @endphp
                                    @if ($niks->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($niks as $nik)
                                                <span
                                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                                                    bg-gray-50 text-gray-700 border-gray-200">
                                                    {{ $nik }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @php $ops = $row->ops; @endphp
                                    @if (!empty($ops))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($ops as $op)
                                                <span
                                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                                                    bg-emerald-50 text-emerald-700 border-emerald-200">
                                                    {{ $op }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                {{-- <a
                                    href="{{ route('laporan.pdf', $row) }}"
                                    target="_blank"
                                    class="shrink-0 inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-800 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300"
                                >PDF</a> --}}
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">No data yet.</div>
                    @endforelse
                @endif
            </div>

            <!-- Footer -->
            <div
                class="flex flex-col gap-3 px-4 py-3 border-t border-gray-100 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-600">
                    @if ($readyToLoad && $laporan instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                        Showing <span class="font-medium">{{ $laporan->firstItem() }}</span>–<span
                            class="font-medium">{{ $laporan->lastItem() }}</span>
                        of <span class="font-medium">{{ number_format($laporan->total()) }}</span>
                    @else
                        Loading…
                    @endif
                </div>

                {{-- Inline condensed pagination: ≤5 => 1..N, >5 => 1 2 … (last-1) last --}}
                @if ($readyToLoad && $laporan instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    @php
                        $last = $laporan->lastPage();
                        $current = $laporan->currentPage();
                        $pages = $last <= 5 ? range(1, $last) : [1, 2, 'ellipsis', $last - 1, $last];

                        $btn = function ($active = false, $edge = false) {
                            if ($active) {
                                return 'inline-flex items-center justify-center min-w-[2.25rem] h-9 rounded-md border border-gray-300 bg-gray-900 text-white px-3 text-sm font-medium';
                            }
                            $base =
                                'inline-flex items-center justify-center min-w-[2.25rem] h-9 rounded-md border px-3 text-sm font-medium';
                            $muted = $edge
                                ? ' border-gray-200 text-gray-500 bg-white hover:bg-gray-50'
                                : ' border-gray-200 text-gray-700 bg-white hover:bg-gray-50';
                            return $base . $muted;
                        };
                    @endphp

                    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-end gap-1">
                        {{-- Prev --}}
                        @if ($laporan->onFirstPage())
                            <span class="{{ $btn(false, true) }} cursor-not-allowed opacity-60">Prev</span>
                        @else
                            <button type="button" wire:click="previousPage"
                                class="{{ $btn(false, true) }}">Prev</button>
                        @endif

                        {{-- Numbers --}}
                        @foreach ($pages as $p)
                            @if ($p === 'ellipsis')
                                <span class="px-2 text-gray-400 select-none">…</span>
                            @else
                                @if ($p == $current)
                                    <span class="{{ $btn(true) }}">{{ $p }}</span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $p }})"
                                        class="{{ $btn() }}">{{ $p }}</button>
                                @endif
                            @endif
                        @endforeach

                        {{-- Next --}}
                        @if ($laporan->hasMorePages())
                            <button type="button" wire:click="nextPage" class="{{ $btn(false, true) }}">Next</button>
                        @else
                            <span class="{{ $btn(false, true) }} cursor-not-allowed opacity-60">Next</span>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>
