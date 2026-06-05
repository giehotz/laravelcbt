<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Actions\Cbt\CreateSoalAction;
use App\Actions\Cbt\UpdateSoalAction;
use App\Http\Requests\Cbt\StoreSoalRequest;
use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Soal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

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
