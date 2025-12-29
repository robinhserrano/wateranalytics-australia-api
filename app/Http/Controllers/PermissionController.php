<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->back()->with('success', 'Permission created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        // Optional: Check if permission is assigned to roles? Spatie handles detachment automatically typically, but maybe warn?
        // For simplicity, allow delete.
        
        $permission->delete();

        return redirect()->back()->with('success', 'Permission deleted successfully.');
    }
}
