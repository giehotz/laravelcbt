<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CbtSoalSiswa;
use App\Models\Master\Siswa;
use App\Models\Cbt\Jadwal;
use App\Models\CbtDurasiSiswa;
use App\Http\Resources\SoalSiswaResource;
use Illuminate\Http\Request;
use Mockery;

class SoalSiswaResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMockSiswa($status)
    {
        $durasi = new CbtDurasiSiswa(['status' => $status]);
        $durasi->jadwal_id = 1;

        // Mock builder
        $builder = Mockery::mock();
        $builder->shouldReceive('where')->with('jadwal_id', 1)->andReturnSelf();
        $builder->shouldReceive('first')->andReturn($durasi);

        // Mock siswa
        $siswa = Mockery::mock(Siswa::class)->makePartial();
        $siswa->id = 1;
        $siswa->shouldReceive('durasi')->andReturn($builder);

        return $siswa;
    }

    public function test_jawaban_benar_is_hidden_when_exam_is_ongoing()
    {
        $jadwal = new Jadwal(['hasil_tampil' => true]);
        $jadwal->id = 1;

        $siswa = $this->createMockSiswa(1); // Sedang ujian

        $soalSiswa = new CbtSoalSiswa([
            'id' => 'ulid-test-1',
            'jadwal_id' => 1,
            'jawaban_benar' => 'A',
            'jawaban_alias' => 'C',
        ]);
        
        $soalSiswa->setRelation('jadwal', $jadwal);
        $soalSiswa->setRelation('siswa', $siswa);

        $request = Request::create('/api/test', 'GET');
        $resourceArray = (new SoalSiswaResource($soalSiswa))->toArray($request);

        $this->assertNull($resourceArray['jawaban_benar']);
        $this->assertNull($resourceArray['jawaban_alias']);
    }

    public function test_jawaban_benar_is_visible_when_exam_is_finished_and_hasil_tampil_is_true()
    {
        $jadwal = new Jadwal(['hasil_tampil' => true]);
        $jadwal->id = 1;

        $siswa = $this->createMockSiswa(2); // Selesai

        $soalSiswa = new CbtSoalSiswa([
            'id' => 'ulid-test-2',
            'jadwal_id' => 1,
            'jawaban_benar' => 'A',
            'jawaban_alias' => 'C',
        ]);
        
        $soalSiswa->setRelation('jadwal', $jadwal);
        $soalSiswa->setRelation('siswa', $siswa);

        $request = Request::create('/api/test', 'GET');
        $resourceArray = (new SoalSiswaResource($soalSiswa))->toArray($request);

        $this->assertEquals('A', $resourceArray['jawaban_benar']);
        $this->assertEquals('C', $resourceArray['jawaban_alias']);
    }

    public function test_jawaban_benar_is_hidden_when_exam_is_finished_but_hasil_tampil_is_false()
    {
        $jadwal = new Jadwal(['hasil_tampil' => false]);
        $jadwal->id = 1;

        $siswa = $this->createMockSiswa(2); // Selesai

        $soalSiswa = new CbtSoalSiswa([
            'id' => 'ulid-test-3',
            'jadwal_id' => 1,
            'jawaban_benar' => 'A',
            'jawaban_alias' => 'C',
        ]);
        
        $soalSiswa->setRelation('jadwal', $jadwal);
        $soalSiswa->setRelation('siswa', $siswa);

        $request = Request::create('/api/test', 'GET');
        $resourceArray = (new SoalSiswaResource($soalSiswa))->toArray($request);

        $this->assertNull($resourceArray['jawaban_benar']);
        $this->assertNull($resourceArray['jawaban_alias']);
    }
}
