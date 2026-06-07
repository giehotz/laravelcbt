<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->hasRole('siswa')) {
            return redirect()->route('siswa.dashboard');
        }
        $user = $request->user();
        $tp = TahunPelajaran::where('active', true)->first();
        $smt = Semester::where('active', true)->first();

        // Safety fallback if no active academic year
        if (! $tp || ! $smt) {
            return Inertia::render('Dashboard', [
                'serverTime' => now()->toIsoString(),
                'tp' => null,
                'smt' => null,
                'stats' => [],
                'jadwalHariIni' => [],
            ]);
        }

        $data = $this->dashboardService->getDashboardData($user, $tp, $smt);

        return Inertia::render('Dashboard', array_merge($data, [
            'serverTime' => now()->toIsoString(),
            'tp' => $tp->only('id', 'tahun'),
            'smt' => $smt->only('id', 'nama_smt'),
        ]));
    }
}
