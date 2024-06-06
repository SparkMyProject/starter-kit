<?php

namespace App\Http\Controllers\Admin\Settings;

use Illuminate\Routing\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
  public function index()
  {
    $roles = Role::withCount('users')->withCount('permissions')->orderBy('name')->get();
    $permissions = Permission::orderBy('name')->get();
    return view('web.admin.settings.roles.index', ['roles' => $roles, 'permissions' => $permissions]);
  }

  public function edit($id)
  {
    $role = Role::find($id);
    if (!$role) {
      session()->flash('error', 'Role not found.');
      return redirect()->route('routes.web.admin.settings.roles.index');
    }
    if ($role->is_system) {
      session()->flash('error', 'Cannot edit system roles.');
      return redirect()->route('routes.web.admin.settings.roles.index');
    }
    $validated = request()->validate([
      'name' => 'nullable|string|max:20',
      'description' => 'nullable|string|max:30',
      'icon' => 'nullable|string|max:30',
      'priority' => 'nullable|integer',
    ]);

    $role->update(array_filter($validated));
    session()->flash('success', 'Role updated.');
    return redirect()->route('routes.web.admin.settings.roles.index');
  }

  public function edit_permissions($id)
  {
    $role = Role::find($id);
    if (!$role) {
      session()->flash('error', 'Role not found.');
      return redirect()->route('routes.web.admin.settings.roles.index');
    }
    if ($role->is_system) {
      session()->flash('error', 'Cannot edit system roles.');
      return redirect()->route('routes.web.admin.settings.roles.index');
    }
    $validated = request()->validate([
      'permissions' => 'nullable|array',
    ]);

    // Use the get method to avoid "Undefined array key" errors
    $permissions = $validated['permissions'] ?? [];

    $role->syncPermissions($permissions);
    session()->flash('success', 'Role permissions updated.');
    return redirect()->route('routes.web.admin.settings.roles.index');
  }

  public function delete($id)
  {
    $role = Role::find($id);
    $current_user = auth()->user()->load('roles');
    $response = ["message" => "error"];

    if (!$role) {
      return response()->json($response);
    }
    if ($role->is_system) {
      $response = ["message" => "Cannot delete system roles.", "code" => 403];
      return response()->json($response);
    }

    // Check if role is higher or lower than current highest role
    if ($role->priority >= $current_user->roles->sortByDesc('priority')->first()->priority) {
      $response = ["message" => "insufficient_permissions", "code" => 403];
      return response()->json($response);
    } else {
      $role->delete();
      $response = ["message" => "success", "code" => 200];
    }
    return response()->json($response);
  }

  public function create() {
    $validated = request()->validate([
      'name' => 'required|string|max:20',
      'description' => 'nullable|string|max:30',
      'icon' => 'nullable|string|max:30',
      'priority' => 'nullable|integer',
    ]);

    if (Role::where('name', $validated['name'])->exists()) {
      session()->flash('error', 'Role already exists.');
      return redirect()->route('routes.web.admin.settings.roles.index');
    }

    $role = Role::create(array_filter($validated));
    session()->flash('success', 'Role created.');
    return redirect()->route('routes.web.admin.settings.roles.index');
  }
}
