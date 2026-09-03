<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Leger Nilai - {{ $legerData['kelas']->nama_kelas }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1e293b; margin: 15px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; margin-bottom: 15px; padding-bottom: 8px; }
        .title { font-size: 16px; font-weight: bold; margin: 0 0 4px 0; text-transform: uppercase; }
        .subtitle { font-size: 12px; margin: 0; color: #475569; }
        .meta-table { width: 100%; border: none; margin-bottom: 12px; font-size: 11px; }
        .meta-table td { border: none; padding: 2px 4px; }
        table.leger { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
        table.leger th, table.leger td { border: 1px solid #94a3b8; padding: 5px 4px; text-align: left; }
        table.leger th { background-color: #f1f5f9; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-gold { background-color: #fef3c7; }
        .bg-remedial { background-color: #ffe4e6; color: #b91c1c; font-weight: bold; }
        .footer { margin-top: 25px; font-size: 9px; color: #64748b; }
        .signature-table { width: 100%; border: none; margin-top: 30px; font-size: 10px; }
        .signature-table td { border: none; text-align: center; padding: 10px; width: 50%; }
    </style>
</head>
<body>
    @php
        $kelas = $legerData['kelas'];
        $mapelList = $legerData['mapelList'];
        $rows = $legerData['rows'];
        $mapelStats = $legerData['mapelStats'];
        $semester = $legerData['semester'];
        $tahunAjaran = $legerData['tahunAjaran'];
    @endphp

    <div class="header">
        <h1 class="title">BUKU LEGER NILAI &amp; PERINGKAT KELAS</h1>
        <p class="subtitle">SIAKAD Yayasan Nurul Jadid Karduluk</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 12%;"><strong>Kelas</strong></td>
            <td style="width: 38%;">: {{ $kelas->nama_lengkap }}</td>
            <td style="width: 15%;"><strong>Semester</strong></td>
            <td style="width: 35%;">: {{ $semester }}</td>
        </tr>
        <tr>
            <td><strong>Wali Kelas</strong></td>
            <td>: {{ $kelas->waliKelas->nama_lengkap ?? '-' }}</td>
            <td><strong>Tahun Ajaran</strong></td>
            <td>: {{ $tahunAjaran }}</td>
        </tr>
    </table>

    <table class="leger">
        <thead>
            <tr>
                <th style="width: 3%;">Rank</th>
                <th style="width: 8%;">NIS</th>
                <th style="width: 20%;">Nama Siswa</th>
                @foreach ($mapelList as $m)
                    <th>
                        {{ $m->kode_mapel ?? Str::limit($m->nama_mapel, 6) }}
                        <br><span style="font-size: 7.5px; font-weight: normal;">(KKM {{ $m->kkm ?? 75 }})</span>
                    </th>
                @endforeach
                <th style="width: 6%;">Total</th>
                <th style="width: 6%;">Rerata</th>
                <th style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $s = $row['siswa'];
                    $rank = $row['rank'];
                @endphp
                <tr class="{{ $rank === 1 ? 'bg-gold' : '' }}">
                    <td class="text-center font-bold">{{ $rank }}</td>
                    <td class="text-center">{{ $s->nis }}</td>
                    <td>{{ $s->nama_lengkap }}</td>
                    @foreach ($mapelList as $m)
                        @php
                            $sc = $row['scores'][$m->id] ?? null;
                            $val = $sc['akhir'] ?? null;
                            $isTuntas = $sc['is_tuntas'] ?? null;
                        @endphp
                        <td class="text-center {{ $isTuntas === false ? 'bg-remedial' : '' }}">
                            {{ $val !== null ? number_format((float) $val, 1) : '-' }}
                        </td>
                    @endforeach
                    <td class="text-center font-bold">{{ $row['total_akhir'] !== null ? number_format((float) $row['total_akhir'], 1) : '-' }}</td>
                    <td class="text-center font-bold">{{ $row['rata_rata'] !== null ? number_format((float) $row['rata_rata'], 2) : '-' }}</td>
                    <td class="text-center">
                        @if ($row['total_akhir'] !== null)
                            {{ $row['belum_tuntas_count'] === 0 ? 'Tuntas' : $row['belum_tuntas_count'] . ' Rem' }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 6 + $mapelList->count() }}" class="text-center">Belum ada data siswa.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">Rata-rata Kelas:</td>
                @foreach ($mapelList as $m)
                    <td class="text-center">{{ $mapelStats[$m->id]['avg'] ?? '-' }}</td>
                @endforeach
                <td colspan="3"></td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">Nilai Tertinggi:</td>
                @foreach ($mapelList as $m)
                    <td class="text-center">{{ $mapelStats[$m->id]['max'] ?? '-' }}</td>
                @endforeach
                <td colspan="3"></td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right">Nilai Terendah:</td>
                @foreach ($mapelList as $m)
                    <td class="text-center">{{ $mapelStats[$m->id]['min'] ?? '-' }}</td>
                @endforeach
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Madrasah / Sekolah
                <br><br><br><br>
                <strong>___________________________</strong>
            </td>
            <td>
                Karduluk, {{ now()->translatedFormat('d F Y') }}<br>
                Wali Kelas {{ $kelas->nama_kelas }}
                <br><br><br><br>
                <strong>{{ $kelas->waliKelas->nama_lengkap ?? '___________________________' }}</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak dari SIAKAD NUJA pada: {{ now()->translatedFormat('d F Y H:i:s') }}
    </div>
</body>
</html>
