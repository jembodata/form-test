{{-- resources/views/pdf/laporan.blade.php --}}
@php
    // ---------- Helpers ----------
    $fmtNum = function ($v, $dec = 3) {
        if ($v === null || $v === '' || !is_numeric($v)) {
            return '';
        }
        $v = (float) $v;
        // 3 desimal untuk jam (P/O/R/G), 0 desimal untuk angka besar (output)
        return rtrim(rtrim(number_format($v, $dec, ',', '.'), '0'), ',');
    };
    $fmtInt = function ($v) {
        if ($v === null || $v === '' || !is_numeric($v)) {
            return '';
        }
        return number_format((float) $v, 0, ',', '.');
    };
    $safe = fn($v) => e((string) ($v ?? ''));

    // ---------- Data umum ----------
    $ops = method_exists($laporan, 'getOpsAttribute')
        ? $laporan->ops ?? []
        : collect($laporan->detail_produksi ?? [])
            ->pluck('op')
            ->filter()
            ->values()
            ->all();

    $names = $laporan->karyawans->pluck('nama')->filter()->values();
    $niks = $laporan->karyawans->pluck('nik')->filter()->values();

    $items = collect($laporan->detail_produksi ?? [])->map(function ($r) {
        if (!is_array($r)) {
            return [];
        }
        return [
            'op' => $r['op'] ?? '',
            'proses' => $r['proses'] ?? '',
            'type_size' => $r['type_size'] ?? ($r['type'] ?? '') . ($r['size'] ?? '' ? ' ' . $r['size'] : ''),
            'customers' => $r['customers'] ?? '',
            'line_speed' => $r['line_speed'] ?? '',
            'output' => $r['ouput_per_order'] ?? ($r['output_per_order'] ?? ''),

            // P/O/R/G (jam)
            'P' => $r['persiapan'] ?? '',
            'O' => $r['operation'] ?? '',
            'R' => $r['reloading'] ?? '',
            'G' => $r['gangguan'] ?? '',

            // Kendala (kode list)
            'kendala' => is_array($r['kendala'] ?? null)
                ? $r['kendala']
                : (isset($r['kendala'])
                    ? (array) $r['kendala']
                    : []),
            'keterangan' => $r['keterangan'] ?? '',
            // Opsional lain:
            'warna' => $r['warna'] ?? '',
            'uk_bob' => $r['uk_bob'] ?? '',
            'panjang_tu' => $r['panjang_actual'] ?? '',
        ];
    });

    // ---------- Akumulasi total jam & output ----------
    $sum = [
        'P' => 0.0,
        'O' => 0.0,
        'R' => 0.0,
        'G' => 0.0,
        'output' => 0.0,
    ];
    foreach ($items as $it) {
        foreach (['P', 'O', 'R', 'G'] as $k) {
            if (is_numeric($it[$k])) {
                $sum[$k] += (float) $it[$k];
            }
        }
        if (is_numeric($it['output'])) {
            $sum['output'] += (float) $it['output'];
        }
    }

    // ---------- Akumulasi kendala ----------
    // Map kode → deskripsi (bisa kamu sesuaikan)
    $kodeMap = [
        'TOP' => 'Tidak ada Operator',
        'GO' => 'Gangguan Operasi',
        'GOP' => 'Gangguan Operasi',
        'TBP' => 'Tidak ada Bobin Produksi',
        'TBK' => 'Tidak ada Bobin Kayu',
        'TPS' => 'Tunggu Proses Sebelumnya',
        'MR' => 'Mesin Rusak',
        'TAT' => 'Tidak ada Tools',
        'TAO' => 'Tidak ada Order',
        'TB' => 'Tidak ada Bahan',
    ];

    // total durasi G bisa dikaitkan dgn kode pada baris tsb (kalau ada 1 kode, anggap seluruh G utk kode tsb)
    $kendalaSum = []; // label => jam
    foreach ($items as $it) {
        $codes = $it['kendala'];
        $g = is_numeric($it['G']) ? (float) $it['G'] : 0.0;
        if (!$codes) {
            continue;
        }
        $bagian = $g && count($codes) ? $g / count($codes) : 0.0;
        foreach ($codes as $c) {
            $label = $kodeMap[strtoupper($c)] ?? strtoupper($c);
            $kendalaSum[$label] = ($kendalaSum[$label] ?? 0.0) + $bagian;
        }
    }
@endphp
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Kerja #{{ $laporan->id }}</title>
    <style>
        @page {
            margin: 14mm 10mm;
        }

        /* margin kanan-kiri dipersempit */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
            line-height: 1.35;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 9px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        table.clean,
        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid th,
        table.grid td {
            border: 1px solid #111;
            padding: 4px 5px;
            vertical-align: top;
        }

        table.grid th {
            background: #f3f4f6;
        }

        table.grid {
            table-layout: fixed;
        }

        /* penting supaya fit */

        .wrap {
            word-wrap: break-word;
        }

        .h-note {
            white-space: pre-line;
        }

        .mt-6 {
            margin-top: 6px;
        }

        .mt-10 {
            margin-top: 10px;
        }
    </style>

</head>

<body>

    {{-- Header --}}
    <table class="clean">
        <tr>
            <td class="small">Lampiran JCC-FO-PS-001-F008</td>
            <td class="center bold" style="font-size: 16px;">LAPORAN HASIL KERJA</td>
            <td class="right small">ID: {{ $laporan->id }}@if ($laporan->kode_laporan)
                    • {{ $laporan->kode_laporan }}
                @endif
            </td>
        </tr>
    </table>

    {{-- Info Laporan --}}
    <table class="grid mt-6">
        <tr>
            <th style="width: 110px;">Plant</th>
            <td style="width: 35%">{{ $laporan->mesins->nama_plant ?? '—' }}</td>
            <th style="width: 110px;">Mesin</th>
            <td>{{ $laporan->mesins->nama_mesin ?? '—' }}</td>
        </tr>
        <tr>
            <th>Shift</th>
            <td>{{ $laporan->shift ?? '—' }}</td>
            <th>Hour Meter (Awal–Akhir)</th>
            <td>
                @php
                    $hmA = is_numeric($laporan->hour_meter_awal ?? null) ? (float) $laporan->hour_meter_awal : null;
                    $hmB = is_numeric($laporan->hour_meter_akhir ?? null) ? (float) $laporan->hour_meter_akhir : null;
                    $hmT = $hmA !== null && $hmB !== null ? max($hmB - $hmA, 0) : null;
                    $fmt = function ($v) {
                        return rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
                    };
                @endphp
                {{ $laporan->hour_meter_awal ?? '—' }} – {{ $laporan->hour_meter_akhir ?? '—' }}
                @if ($hmT !== null)
                    (Total: {{ $fmt($hmT) }} jam)
                @endif
            </td>
        </tr>
        <tr>
            <th>Karyawan</th>
            <td colspan="3">
                @php
                    $names = $laporan->karyawans->pluck('nama')->filter()->values();
                    $niks = $laporan->karyawans->pluck('nik')->filter()->values();
                @endphp
                <div>Nama: {{ $names->isNotEmpty() ? $names->implode(', ') : '—' }}</div>
                @if ($niks->isNotEmpty())
                    <div class="mt-6">NIK: {{ $niks->implode(', ') }}</div>
                @endif
            </td>
        </tr>
    </table>


    {{-- Detail Produksi - Semua Field Tersaji --}}
    <table class="grid mt-10">
        {{-- kolom persentase agar fit A4 --}}
        <colgroup>
            <col style="width:12%"><!-- NO ORDER -->
            <col style="width:14%"><!-- PROSES -->
            <col style="width:30%"><!-- TYPE / SIZE -->
            <col style="width:8%"><!-- LINE SPEED -->
            <col style="width:10%"><!-- OUTPUT -->
            <col style="width:6%"><!-- P -->
            <col style="width:6%"><!-- O -->
            <col style="width:6%"><!-- R -->
            <col style="width:8%"><!-- G -->
        </colgroup>
        <thead>
            <tr>
                <th>NO ORDER</th>
                <th>PROSES</th>
                <th>TYPE / SIZE</th>
                <th class="center">LINE<br>SPEED</th>
                <th class="center">OUTPUT<br>(M)</th>
                <th class="center">P</th>
                <th class="center">O</th>
                <th class="center">R</th>
                <th class="center">G</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
            <tr>
                <td class="wrap">{{ $safe($it['op']) }}</td>
                <td class="wrap">{{ $safe($it['proses']) }}</td>
                <td class="wrap">{{ $safe($it['type_size']) }}</td>
                <td class="right">{{ $it['line_speed']!=='' ? $fmtNum($it['line_speed'], 0) : '' }}</td>
                <td class="right">{{ $it['output']!=='' ? $fmtInt($it['output']) : '' }}</td>
                <td class="center">{{ $fmtNum($it['P']) }}</td>
                <td class="center">{{ $fmtNum($it['O']) }}</td>
                <td class="center">{{ $fmtNum($it['R']) }}</td>
                <td class="center">{{ $fmtNum($it['G']) }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="center small muted">Belum ada detail produksi.</td></tr>
            @endforelse
        </tbody>
        @if($items->isNotEmpty())
        <tfoot>
            <tr>
                <th colspan="4" class="right">TOTAL</th>
                <th class="right">{{ $fmtInt($sum['output']) }}</th>
                <th class="center">{{ $fmtNum($sum['P']) }}</th>
                <th class="center">{{ $fmtNum($sum['O']) }}</th>
                <th class="center">{{ $fmtNum($sum['R']) }}</th>
                <th class="center">{{ $fmtNum($sum['G']) }}</th>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Rekap Kendala (per Kode/Label) --}}
    <table class="grid mt-10">
        <thead>
            <tr>
                <th style="width: 200px;">KETERANGAN JENIS GANGGUAN</th>
                <th class="right" style="width: 110px;">TOTAL JAM</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($kendalaSum))
                @foreach ($kendalaSum as $label => $jam)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="right">{{ $fmtNum($jam) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" class="center small muted">Tidak ada gangguan.</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Catatan Umum Laporan --}}
    @if (!empty($laporan->keterangan))
        <table class="grid mt-10">
            <tr>
                <th>CATATAN</th>
            </tr>
            <tr>
                <td class="h-note">{{ $safe($laporan->keterangan) }}</td>
            </tr>
        </table>
    @endif

    {{-- Tanda Tangan --}}
    <table class="grid mt-10">
        <tr>
            <th class="center">Mengetahui</th>
            <th class="center">Disetujui</th>
            <th class="center">Diperiksa</th>
            <th class="center">Di isi</th>
        </tr>
        <tr style="height: 50px;">
            <td><br><br></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td class="center small">( Manager Plant )</td>
            <td class="center small">( Supervisor Plant / Foreman )</td>
            <td class="center small">( — )</td>
            <td class="center small">( Operator )</td>
        </tr>
    </table>

    <div class="small muted mt-10">Tembusan: PI, PLANT</div>
</body>

</html>
