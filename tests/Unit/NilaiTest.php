<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Nilai;
use PHPUnit\Framework\TestCase;

class NilaiTest extends TestCase
{
    public function test_hitung_nilai_akhir_with_floats(): void
    {
        $result = Nilai::hitungNilaiAkhir(80.0, 90.0, 100.0);
        // (80*0.3) + (90*0.3) + (100*0.4) = 24 + 27 + 40 = 91.0
        $this->assertEquals(91.0, $result);
    }

    public function test_hitung_nilai_akhir_with_strings(): void
    {
        $result = Nilai::hitungNilaiAkhir('80.5', '90.0', '100');
        // (80.5*0.3) + (90*0.3) + (100*0.4) = 24.15 + 27 + 40 = 91.15
        $this->assertEquals(91.15, $result);
    }

    public function test_hitung_nilai_akhir_with_null_and_empty_string(): void
    {
        $result = Nilai::hitungNilaiAkhir('', null, null);
        $this->assertNull($result);
    }

    public function test_hitung_predikat(): void
    {
        $this->assertEquals('A', Nilai::hitungPredikat(95.0));
        $this->assertEquals('A', Nilai::hitungPredikat('90.0'));
        $this->assertEquals('B', Nilai::hitungPredikat(85.0));
        $this->assertEquals('C', Nilai::hitungPredikat(75.0));
        $this->assertEquals('D', Nilai::hitungPredikat(65.0));
        $this->assertEquals('E', Nilai::hitungPredikat(50.0));
        $this->assertNull(Nilai::hitungPredikat(null));
        $this->assertNull(Nilai::hitungPredikat(''));
    }
}
