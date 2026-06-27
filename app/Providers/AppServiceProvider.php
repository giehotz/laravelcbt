<?php

namespace App\Providers;

use App\Models\Cbt\Jadwal;
use App\Models\Cbt\SoalSiswa;
use App\Models\Master\Ekstra;
use App\Models\Master\Jurusan;
use App\Models\Master\Kelas;
use App\Models\Master\LevelKelas;
use App\Models\Master\Mapel;
use App\Policies\Cbt\JadwalPolicy;
use App\Policies\Cbt\SoalSiswaPolicy;
use App\Policies\EkstraPolicy;
use App\Policies\JurusanPolicy;
use App\Policies\KelasPolicy;
use App\Policies\LevelKelasPolicy;
use App\Policies\MapelPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Register Master Policies explicitly
        Gate::policy(LevelKelas::class, LevelKelasPolicy::class);
        Gate::policy(Jurusan::class, JurusanPolicy::class);
        Gate::policy(Mapel::class, MapelPolicy::class);
        Gate::policy(Kelas::class, KelasPolicy::class);
        Gate::policy(Ekstra::class, EkstraPolicy::class);
        Gate::policy(SoalSiswa::class, SoalSiswaPolicy::class);
        Gate::policy(Jadwal::class, JadwalPolicy::class);

        // Register Rate Limiters
        RateLimiter::for('simpan_jawaban', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('mulai_ujian', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
                return response()->json([
                    'message' => 'Terlalu banyak percobaan. Harap tunggu beberapa saat.',
                ], 429, $headers);
            });
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
