<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = User::staff()->withCount('orders')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $staff = $query->paginate(15)->withQueryString();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'role'     => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_MODERATOR])],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => strtolower(trim($data['email'])),
            'phone'    => $data['phone'] ?? null,
            'role'     => $data['role'],
            'password' => $data['password'],
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', $data['name'] . ' has been added as ' . User::ROLES[$data['role']] . '.');
    }

    public function edit(User $user)
    {
        abort_if($user->isCustomer(), 404);

        return view('admin.staff.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isCustomer(), 404);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'role'     => ['required', Rule::in([User::ROLE_SUPER_ADMIN, User::ROLE_MODERATOR])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Never let the last super admin be downgraded or removed.
        if ($user->isSuperAdmin() && $data['role'] !== User::ROLE_SUPER_ADMIN) {
            if (User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1) {
                return back()->withErrors(['role' => 'This is the last super admin. Create another super admin first.'])->withInput();
            }
        }

        $user->update([
            'name'     => $data['name'],
            'email'    => strtolower(trim($data['email'])),
            'phone'    => $data['phone'] ?? null,
            'role'     => $data['role'],
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
            $user->forceFill(['remember_token' => null])->save();
        }

        return redirect()->route('admin.staff.index')
            ->with('success', $user->name . ' has been updated.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->isCustomer(), 404);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1) {
            return back()->with('error', 'This is the last super admin account and cannot be deleted.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.staff.index')->with('success', "{$name} has been removed from the team.");
    }
}
