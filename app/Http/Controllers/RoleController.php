<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        // Eager load permissions to optimize performance
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Show form to create a new role.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name'   => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ], [
            'role_name.required' => __('roles.name_required'),
            'role_name.unique'   => __('roles.name_taken'),
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Create Role
                $role = Role::create([
                    'name'       => $request->role_name,
                    'guard_name' => 'web' 
                ]);

                // 2. Assign Permissions
                if ($request->has('permissions')) {
                    $role->syncPermissions($request->permissions);
                }
            });

            return redirect()->route('roles.index')
                ->with('success', __('roles.created'));

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit a role.
     */
    public function edit(Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', __('roles.cannot_edit_admin'));
        }

        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing super-admin name
        if ($role->name === 'super-admin') {
            return back()->with('error', __('roles.cannot_edit_admin'));
        }

        $request->validate([
            'role_name'   => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'required|array'
        ], [
            'role_name.required' => __('roles.name_required'),
            'role_name.unique'   => __('roles.name_taken'),
        ]);

        try {
            DB::transaction(function () use ($request, $role) {
                $role->update(['name' => $request->role_name]);
                $role->syncPermissions($request->permissions);
            });

            return redirect()->route('roles.index')
                ->with('success', __('roles.updated'));

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', __('roles.cannot_delete_admin'));
        }

        $role->delete();

        return back()->with('success', __('roles.deleted'));
    }
}