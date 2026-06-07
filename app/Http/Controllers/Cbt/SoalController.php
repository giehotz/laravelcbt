<?php

namespace App\Http\Controllers\Cbt;

use App\Actions\Cbt\CreateSoalAction;
use App\Actions\Cbt\ImportSoalFromExcelAction;
use App\Actions\Cbt\UpdateSoalAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cbt\ImportSoalRequest;
use App\Http\Requests\Cbt\StoreSoalRequest;
use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Soal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SoalController extends Controller
{
    public function index(BankSoal $bank): Response
    {
        $soals = Soal::with('pairs')->where('bank_id', $bank->id)->orderBy('jenis')->orderBy('nomor_soal')->get();

        return Inertia::render('Cbt/Soal/Index', [
            'bank' => $bank,
            'soals' => $soals,
        ]);
    }

    public function create(BankSoal $bank, Request $request): Response
    {
        $soals = Soal::with('pairs')->where('bank_id', $bank->id)->orderBy('jenis')->orderBy('nomor_soal')->get();
        $jenis = (int) $request->query('jenis', 1);

        return Inertia::render('Cbt/Soal/Workspace', [
            'bank' => $bank,
            'soals' => $soals,
            'active_jenis' => $jenis,
            'active_soal_id' => null,
        ]);
    }

    public function store(StoreSoalRequest $request, BankSoal $bank, CreateSoalAction $action): RedirectResponse
    {
        $action->execute($bank, $request->validated());

        return redirect()->back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(BankSoal $bank, Soal $soal): Response
    {
        $soals = Soal::with('pairs')->where('bank_id', $bank->id)->orderBy('jenis')->orderBy('nomor_soal')->get();

        return Inertia::render('Cbt/Soal/Workspace', [
            'bank' => $bank,
            'soals' => $soals,
            'active_jenis' => $soal->jenis,
            'active_soal_id' => $soal->id,
        ]);
    }

    public function update(StoreSoalRequest $request, BankSoal $bank, Soal $soal, UpdateSoalAction $action): RedirectResponse
    {
        $action->execute($soal, $request->validated());

        return redirect()->back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(BankSoal $bank, Soal $soal): RedirectResponse
    {
        $soal->delete();

        return redirect()->back()->with('success', 'Soal berhasil dihapus.');
    }

    public function importForm(BankSoal $bank): Response
    {
        return Inertia::render('Cbt/Soal/Import', [
            'bank' => $bank,
            'preview' => null,
        ]);
    }

    public function import(ImportSoalRequest $request, BankSoal $bank): Response|RedirectResponse
    {
        $file = $request->file('file');

        $action = app(ImportSoalFromExcelAction::class);

        try {
            $preview = $action->parse($file, $bank);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: '.$e->getMessage());
        }

        if ($preview['total_rows'] === 0 && ! empty($preview['warnings'])) {
            return back()->with('error', 'File Excel tidak mengandung data soal yang valid.');
        }

        $cacheKey = 'import_preview_'.auth()->id().'_'.$bank->id.'_'.Str::random(8);
        Cache::put($cacheKey, $preview['sheets'], now()->addMinutes(30));

        return Inertia::render('Cbt/Soal/Import', [
            'bank' => $bank,
            'preview' => $this->buildSafePreview($preview),
            'cache_key' => $cacheKey,
        ]);
    }

    public function confirmImport(Request $request, BankSoal $bank): RedirectResponse
    {
        $request->validate(['cache_key' => 'required|string']);

        $cacheKey = $request->input('cache_key');

        $expectedPrefix = 'import_preview_'.auth()->id().'_'.$bank->id;
        if (! str_starts_with($cacheKey, $expectedPrefix)) {
            abort(403, 'Invalid cache key.');
        }

        $sheetsData = Cache::pull($cacheKey);

        if (! $sheetsData) {
            return back()->with('error', 'Sesi preview sudah expired (30 menit). Silakan upload ulang file.');
        }

        $action = app(ImportSoalFromExcelAction::class);
        $result = $action->execute($sheetsData, $bank);

        return redirect()->route('cbt.bank-soal.soal.index', ['bank' => $bank->id])
            ->with('success', "Import berhasil: {$result['total']} soal (".
                implode(', ', array_map(fn ($k, $v) => "$k: $v", array_keys($result['details']), $result['details'])).
                ')');
    }

    public function downloadTemplate(BankSoal $bank): BinaryFileResponse
    {
        $spreadsheet = IOFactory::load(storage_path('app/templates/soal_import_template.xlsx'));

        if ($bank->opsi < 5) {
            $sheet = $spreadsheet->getSheetByName('PG');
            if ($sheet) {
                $sheet->getColumnDimension('G')->setVisible(false);
            }
            $sheet = $spreadsheet->getSheetByName('Kompleks');
            if ($sheet) {
                $sheet->getColumnDimension('G')->setVisible(false);
            }
        }
        if ($bank->opsi < 4) {
            $sheet = $spreadsheet->getSheetByName('PG');
            if ($sheet) {
                $sheet->getColumnDimension('F')->setVisible(false);
            }
            $sheet = $spreadsheet->getSheetByName('Kompleks');
            if ($sheet) {
                $sheet->getColumnDimension('F')->setVisible(false);
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'import_template_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_import_soal.xlsx')->deleteFileAfterSend(true);
    }

    private function buildSafePreview(array $raw): array
    {
        return [
            'sheets' => array_map(fn ($s) => [
                'name' => $s['name'],
                'jenis' => $s['jenis'],
                'count' => $s['count'],
                'errors' => $s['errors'] ?? [],
                'rows' => array_map(fn ($r) => [
                    'nomor_soal' => $r['nomor_soal'] ?? 0,
                    'soal' => Str::limit(strip_tags($r['soal'] ?? ''), 120),
                    'jawaban' => is_array($r['jawaban'] ?? null)
                        ? implode(', ', $r['jawaban'])
                        : ($r['jawaban'] ?? '-'),
                    'valid' => $r['valid'] ?? true,
                    'error_msg' => $r['error_msg'] ?? null,
                ], $s['rows'] ?? []),
            ], $raw['sheets'] ?? []),
            'total_rows' => $raw['total_rows'] ?? 0,
            'errors' => $raw['errors'] ?? [],
            'warnings' => $raw['warnings'] ?? [],
        ];
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $request->file('image');
        $path = $file->store('public/soal');
        $url = Storage::url($path);

        DB::table('cbt_soal_uploads')->insert([
            'user_id' => $request->user()->id,
            'path' => $path,
            'soal_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['url' => $url]);
    }
}
