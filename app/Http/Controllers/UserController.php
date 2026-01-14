<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        // Eager load roles and branch to prevent N+1 query performance issues
        $users = User::with(['roles', 'branch'])->latest()->paginate(10);
        $roles = Role::all();
        $branches = Branch::all();

        return view('users.index', compact('users', 'roles', 'branches'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        $branches = Branch::all();
        return view('users.create', compact('roles', 'branches'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        // Create User (Activity Log records this automatically via Model trait)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'branch_id' => $request->branch_id,
        ]);

        // Assign Role
        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', __('users.created'));
    }

    /**
     * Show the form for editing the user's profile (Name, Email, Password).
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Update User (Activity Log records this automatically)
        $user->update($data);

        return redirect()->route('users.index')->with('success', __('users.updated'));
    }

    /**
     * Special Method: Update Role & Branch (Admin Action)
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|exists:roles,name',
        ]);

        // 1. Update Branch (Model Log handles this)
        $user->update([
            'branch_id' => $request->branch_id
        ]);

        // 2. Update Role
        $user->syncRoles([$request->role]);

        // 3. Manually Log the Role Change
        // (Because roles are in a separate table, we log this manually to be sure)
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log("Updated access: Role set to {$request->role}");

        // 4. Return success using Translation file
        return back()->with('success', __('users.updated'));
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // 1. Prevent deleting yourself
        if (auth()->id() == $user->id) {
            return back()->with('error', __('users.cannot_delete_self'));
        }

        // 2. The Activity Log Trait on User Model will automatically log this!
        $user->delete();

        // 3. Send the Success Notification
        return back()->with('success', __('users.deleted'));
    }
}