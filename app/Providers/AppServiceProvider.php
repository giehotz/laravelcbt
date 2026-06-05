<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use Illuminate\Support\Facades\Gate;

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
        Gate::policy(\App\Models\Master\LevelKelas::class, \App\Policies\LevelKelasPolicy::class);
        Gate::policy(\App\Models\Master\Jurusan::class, \App\Policies\JurusanPolicy::class);
        Gate::policy(\App\Models\Master\Mapel::class, \App\Policies\MapelPolicy::class);
        Gate::policy(\App\Models\Master\Kelas::class, \App\Policies\KelasPolicy::class);
        Gate::policy(\App\Models\Master\Ekstra::class, \App\Policies\EkstraPolicy::class);
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
