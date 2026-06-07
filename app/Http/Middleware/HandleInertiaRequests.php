<?php

namespace App\Http\Middleware;

use App\Models\Semester;
use App\Models\Setting;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $setting = Setting::get();
        $tpActive = Cache::remember('tp_active_array', 3600, function () {
            $tp = TahunPelajaran::where('active', true)->first();

            return $tp ? $tp->only(['id', 'tahun']) : null;
        });
        $semesterActive = Cache::remember('semester_active_array', 3600, function () {
            $semester = Semester::where('active', true)->first();

            return $semester ? $semester->only(['id', 'nama_smt', 'smt']) : null;
        });

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user() ? $request->user()->getRoleNames() : [],
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'info' => session('info'),
            ],
            'setting_sekolah' => [
                'nama_sekolah' => $setting->nama_sekolah ?? 'GarudaCBT',
                'logo' => $setting->logo ? asset('storage/'.$setting->logo) : null,
            ],
            'tp_aktif' => $tpActive,
            'smt_aktif' => $semesterActive,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
