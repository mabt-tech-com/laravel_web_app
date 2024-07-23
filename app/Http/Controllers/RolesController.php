<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RolesController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::all();

            return response()->json([
                'roles' => $roles,
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $role = Role::findOrFail($id);

            $permissions = $role->permissions()->where('company_id', request('company_id'))->get();

            return response()->json([
                'role' => $role,
                'permissions' => $permissions,
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update($id)
    {
        try {
            request()->validate([
                'label' => 'required|string|min:1|max:255',
                'description' => 'required|string|min:1|max:255',
            ]);

            $role = Role::findOrFail($id);

            $role->label = request('label');

            $role->description = request('description');

            $role->save();

            insert_in_history_table('updated', $role->id, $role->getTable());

            return response()->json(['message' => 'Role updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
