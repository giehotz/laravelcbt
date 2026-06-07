<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Show the school settings form.
     */
    public function edit(): Response
    {
        return Inertia::render('Setting/School', [
            'settings' => Setting::get(),
        ]);
    }

    /**
     * Update the school settings.
     */
    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $setting = Setting::first() ?: new Setting;
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            // Store new logo
            $path = $request->file('logo')->store('setting', 'public');
            $data['logo'] = $path;
        }

        $setting->fill($data)->save();

        return redirect()->route('setting.sekolah.edit')
            ->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }
}
