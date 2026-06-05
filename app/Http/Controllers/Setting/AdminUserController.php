<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    /**
     * Display a listing of admin/staff users.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        // Get users who are NOT siswa and NOT guru (or check their roles)
        $users = User::role(['superadmin', 'operator', 'proktor', 'kepsek'])
            ->with('roles')
            ->latest()
            ->paginate(10);

        return Inertia::render('Setting/User/AdminIndex', [
            'users' => $users,
            'roles' => Role::whereIn('name', ['superadmin', 'operator', 'proktor', 'kepsek'])->get(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $data = $request->validated();
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('setting.user.admin.index')
            ->with('success', 'Akun administrator berhasil dibuat.');
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateAdminUserRequest $request, User $admin): RedirectResponse
    {
        Gate::authorize('update', $admin);

        $data = $request->validated();

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->username = $data['username'] ?? null;

        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        $admin->save();

        // Sync role
        $admin->syncRoles([$data['role']]);

        return redirect()->route('setting.user.admin.index')
            ->with('success', 'Akun administrator berhasil diperbarui.');
    }

    /**
     * Delete the specified user.
     */
    public function destroy(User $admin): RedirectResponse
    {
        Gate::authorize('delete', $admin);

        $admin->delete();

        return redirect()->route('setting.user.admin.index')
            ->with('success', 'Akun administrator berhasil dihapus.');
    }
}
