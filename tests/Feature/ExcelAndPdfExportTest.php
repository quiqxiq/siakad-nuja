<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExcelAndPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_kehadiran_export_supports_excel_and_pdf_but_rejects_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        // Excel Export
        $responseExcel = $this->actingAs($admin)->get(route('laporan.kehadiran', [
            'kelas_id' => $kelas->id,
            'bulan'    => '2025-07',
            'export'   => 'excel',
        ]));
        $responseExcel->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $responseExcel->headers->get('content-type'));
        $this->assertStringContainsString('.xls', (string) $responseExcel->headers->get('content-disposition'));

        // PDF Export
        $responsePdf = $this->actingAs($admin)->get(route('laporan.kehadiran', [
            'kelas_id' => $kelas->id,
            'bulan'    => '2025-07',
            'export'   => 'pdf',
        ]));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $responsePdf->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $responsePdf->headers->get('content-disposition'));

        // CSV Export should fail validation
        $responseCsv = $this->actingAs($admin)->get(route('laporan.kehadiran', [
            'kelas_id' => $kelas->id,
            'bulan'    => '2025-07',
            'export'   => 'csv',
        ]));
        $responseCsv->assertSessionHasErrors('export');
    }

    public function test_nilai_export_supports_excel_and_pdf_but_rejects_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);
        $mapel = MataPelajaran::factory()->create(['jenjang' => 'MTs']);

        // Excel Export
        $responseExcel = $this->actingAs($admin)->get(route('laporan.nilai', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'export'   => 'excel',
        ]));
        $responseExcel->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $responseExcel->headers->get('content-type'));
        $this->assertStringContainsString('.xls', (string) $responseExcel->headers->get('content-disposition'));

        // PDF Export
        $responsePdf = $this->actingAs($admin)->get(route('laporan.nilai', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'export'   => 'pdf',
        ]));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $responsePdf->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $responsePdf->headers->get('content-disposition'));

        // CSV Export should fail validation
        $responseCsv = $this->actingAs($admin)->get(route('laporan.nilai', [
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'export'   => 'csv',
        ]));
        $responseCsv->assertSessionHasErrors('export');
    }

    public function test_jadwal_export_supports_excel_and_pdf_but_rejects_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Excel Export
        $responseExcel = $this->actingAs($admin)->get(route('laporan.jadwal', [
            'tipe'    => 'keseluruhan',
            'jenjang' => 'MTs',
            'export'  => 'excel',
        ]));
        $responseExcel->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $responseExcel->headers->get('content-type'));
        $this->assertStringContainsString('.xls', (string) $responseExcel->headers->get('content-disposition'));

        // PDF Export
        $responsePdf = $this->actingAs($admin)->get(route('laporan.jadwal', [
            'tipe'    => 'keseluruhan',
            'jenjang' => 'MTs',
            'export'  => 'pdf',
        ]));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $responsePdf->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $responsePdf->headers->get('content-disposition'));

        // CSV Export should fail validation
        $responseCsv = $this->actingAs($admin)->get(route('laporan.jadwal', [
            'tipe'    => 'keseluruhan',
            'jenjang' => 'MTs',
            'export'  => 'csv',
        ]));
        $responseCsv->assertSessionHasErrors('export');
    }

    public function test_leger_export_supports_excel_and_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kelas = Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7', 'jenjang' => 'MTs', 'tahun_ajaran' => '2024/2025']);

        // Excel Export
        $responseExcel = $this->actingAs($admin)->get(route('nilai.leger.export', [
            'kelas_id'     => $kelas->id,
            'semester'     => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'format'       => 'excel',
        ]));
        $responseExcel->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $responseExcel->headers->get('content-type'));
        $this->assertStringContainsString('.xls', (string) $responseExcel->headers->get('content-disposition'));

        // PDF Export
        $responsePdf = $this->actingAs($admin)->get(route('nilai.leger.export', [
            'kelas_id'     => $kelas->id,
            'semester'     => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'format'       => 'pdf',
        ]));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $responsePdf->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $responsePdf->headers->get('content-disposition'));

        // CSV should fail validation on leger export
        $responseCsv = $this->actingAs($admin)->get(route('nilai.leger.export', [
            'kelas_id'     => $kelas->id,
            'semester'     => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
            'format'       => 'csv',
        ]));
        $responseCsv->assertSessionHasErrors('format');
    }
}
