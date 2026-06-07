<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\DbDumper\Databases\MySql;

class DatabaseController extends Controller
{
    private const ALLOWED_TRUNCATE = [
        'ujian' => ['cbt_soal_siswa', 'cbt_durasi_siswa'],
        'nilai' => ['cbt_nilai', 'cbt_rekap_nilai'],
        'log' => ['activity_log', 'log_ujian'],
        'log_materi' => ['log_materi'],
    ];

    public function index()
    {
        // Only superadmin can access this
        $this->authorize('viewAny', User::class);

        return Inertia::render('Utility/Database/Index', [
            'allowed_modules' => array_keys(self::ALLOWED_TRUNCATE),
        ]);
    }

    public function backup()
    {
        $this->authorize('viewAny', User::class);

        try {
            $fileName = 'backup_'.date('Y-m-d_H-i-s').'.sql';
            $path = storage_path('app/public/backups/'.$fileName);

            if (! Storage::disk('public')->exists('backups')) {
                Storage::disk('public')->makeDirectory('backups');
            }

            MySql::create()
                ->setDbName(config('database.connections.mysql.database'))
                ->setUserName(config('database.connections.mysql.username'))
                ->setPassword(config('database.connections.mysql.password'))
                ->dumpToFile($path);

            return response()->download($path)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan backup: '.$e->getMessage());
        }
    }

    public function truncate(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $request->validate([
            'module' => 'required|string|in:'.implode(',', array_keys(self::ALLOWED_TRUNCATE)),
            'confirmation' => 'required|string|same:module',
        ]);

        $module = $request->module;
        $tables = self::ALLOWED_TRUNCATE[$module];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->back()->with('success', "Data modul {$module} berhasil dikosongkan.");
        } catch (Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->back()->with('error', 'Gagal mengosongkan data: '.$e->getMessage());
        }
    }
}
