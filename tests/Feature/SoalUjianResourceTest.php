<?php

namespace Tests\Feature;

use App\Http\Resources\SoalKoreksiResource;
use App\Http\Resources\SoalUjianResource;
use App\Models\Cbt\Soal;
use App\Models\Cbt\SoalSiswa;
use Illuminate\Http\Request;
use Tests\TestCase;

class SoalUjianResourceTest extends TestCase
{
    public function test_soal_ujian_resource_never_exposes_answers_or_description()
    {
        $soal = new Soal([
            'soal' => 'Siapa penemu gravitasi?',
            'deskripsi' => 'Isaac Newton adalah penemu gravitasi.',
            'opsi_a' => 'Newton',
            'opsi_b' => 'Einstein',
        ]);

        $soalSiswa = new SoalSiswa;
        $soalSiswa->id = 'ulid-test-1';
        $soalSiswa->jenis_soal = 1;
        $soalSiswa->no_soal_alias = 1;
        $soalSiswa->jawaban_benar = 'A';
        $soalSiswa->jawaban_alias = 'C';
        $soalSiswa->opsi_alias_a = 'C';
        $soalSiswa->opsi_alias_b = 'A';
        $soalSiswa->setRelation('soal', $soal);

        $request = Request::create('/api/test', 'GET');
        $resourceArray = (new SoalUjianResource($soalSiswa))->toArray($request);

        $this->assertEquals('ulid-test-1', $resourceArray['id']);
        $this->assertEquals(1, $resourceArray['no_soal_alias']);
        $this->assertEquals('C', $resourceArray['opsi_alias_a']);

        // Assert keys are hidden
        $this->assertArrayNotHasKey('jawaban_benar', $resourceArray);
        $this->assertArrayNotHasKey('jawaban_alias', $resourceArray);

        // Assert description is hidden
        $this->assertArrayNotHasKey('deskripsi', $resourceArray['soal']);
        $this->assertEquals('Siapa penemu gravitasi?', $resourceArray['soal']['soal']);
    }

    public function test_soal_koreksi_resource_exposes_answers_and_description()
    {
        $soal = new Soal([
            'soal' => 'Siapa penemu gravitasi?',
            'deskripsi' => 'Isaac Newton adalah penemu gravitasi.',
            'opsi_a' => 'Newton',
            'opsi_b' => 'Einstein',
        ]);

        $soalSiswa = new SoalSiswa;
        $soalSiswa->id = 'ulid-test-2';
        $soalSiswa->jenis_soal = 1;
        $soalSiswa->no_soal_alias = 1;
        $soalSiswa->jawaban_benar = 'A';
        $soalSiswa->jawaban_alias = 'C';
        $soalSiswa->opsi_alias_a = 'C';
        $soalSiswa->opsi_alias_b = 'A';
        $soalSiswa->setRelation('soal', $soal);

        $request = Request::create('/api/test', 'GET');
        $resourceArray = (new SoalKoreksiResource($soalSiswa))->toArray($request);

        $this->assertEquals('ulid-test-2', $resourceArray['id']);
        $this->assertEquals('A', $resourceArray['jawaban_benar']);
        $this->assertEquals('C', $resourceArray['jawaban_alias']);
        $this->assertEquals('Isaac Newton adalah penemu gravitasi.', $resourceArray['soal']['deskripsi']);
    }
}
