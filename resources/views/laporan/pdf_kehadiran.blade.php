<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Rekap Kehadiran - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; margin: 0 0 5px 0; }
        .subtitle { font-size: 14px; margin: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 40px; font-size: 10px; color: #666; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">REKAPITULASI KEHADIRAN KELAS</h1>
        <p class="subtitle">SIAKAD NUJA - Nurul Jadid</p>
    </div>

    <table style="width: auto; border: none; margin-bottom: 20px;">
        <tr>
            <td style="border: none; padding: 2px 10px 2px 0;"><strong>Kelas</strong></td>
            <td style="border: none; padding: 2px;">: {{ $kelas->nama_kelas }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 2px 10px 2px 0;"><strong>Bulan</strong></td>
            <td style="border: none; padding: 2px;">: {{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}</td>
        </tr>
    </table>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">NIS</th>
                <th style="width: 40%;">Nama Siswa</th>
                <th style="width: 10%;">Hadir</th>
                <th style="width: 10%;">Sakit</th>
                <th style="width: 10%;">Izin</th>
                <th style="width: 10%;">Alpa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswa as $i => $s)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $s->nis }}</td>
                <td>{{ $s->nama_lengkap }}</td>
                <td class="text-center">{{ $rekap[$s->id]['Hadir'] }}</td>
                <td class="text-center">{{ $rekap[$s->id]['Sakit'] }}</td>
                <td class="text-center">{{ $rekap[$s->id]['Izin'] }}</td>
                <td class="text-center">{{ $rekap[$s->id]['Alpa'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data siswa untuk kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
