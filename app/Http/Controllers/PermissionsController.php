<?php

namespace App\Http\Controllers;

use App\Models\Permission;

class PermissionsController extends Controller
{
    public function index()
    {
        try {
            $permissions = Permission::all();

            return response()->json([
                'roles' => $permissions,
            ]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            return response()->json([
                'role' => $permission,
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

            $permission = Permission::findOrFail($id);

            $permission->label = request('label');

            $permission->description = request('description');

            $permission->save();

            insert_in_history_table('updated', $permission->id, $permission->getTable());

            return response()->json(['message' => 'Permission updated successfully.']);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
