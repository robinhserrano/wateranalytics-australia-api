<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('roles', 'salesManager', 'team')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('contacts', 'roles', 'salesManager', 'team');

        $commissionStats = [
            'total_commissions' => \App\Models\CommissionCalculation::where('user_id', $user->id)->count(),
            'pending_amount' => \App\Models\CommissionCalculation::where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('final_commission'),
            'total_earned' => \App\Models\CommissionCalculation::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('final_commission'),
            'this_month_earned' => \App\Models\CommissionCalculation::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereMonth('created_at', now()->month)
                ->sum('final_commission'),
        ];

        return Inertia::render('Users/Show', [
            'user' => $user,
            'commissionStats' => $commissionStats,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Users/Create', [
            'contacts' => Contact::select('id', 'display_name', 'user_id')->get(),
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_name' => 'nullable|exists:roles,name', // Changed from role_id
            'sales_manager_id' => 'nullable|exists:users,id',
            'commission_split' => 'nullable|numeric|min:0|max:100',
            'company_lead_base' => 'nullable|numeric|min:0',
            'self_gen_base' => 'nullable|numeric|min:0',
            'legacy_id' => 'nullable|integer|unique:users,legacy_id',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $user = DB::transaction(function () use ($validated, $request) {
            $contactIds = $validated['contact_ids'] ?? null;
            $roleName = $validated['role_name'] ?? null;
            
            unset($validated['contact_ids']);
            unset($validated['role_name']);

            Log::info('Creating user. Contact IDs received:', ['contact_ids' => $contactIds]);
            
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = true;

            $user = User::create($validated);
            
            if ($roleName) {
                $user->assignRole($roleName);
            }

            if ($contactIds) {
                Log::info('Assigning contacts to new user ' . $user->id, ['contact_ids' => $contactIds]);
                DB::table('contacts')
                    ->whereIn('id', $contactIds)
                    ->update(['user_id' => $user->id]);
            }

            return $user;
        });

        return redirect()->route('users.show', $user)->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
         return Inertia::render('Users/Edit', [
            'user' => $user->load('contacts', 'roles', 'salesManager', 'team'),
            'contacts' => Contact::select('id', 'display_name', 'user_id')->get(),
            'roles' => \Spatie\Permission\Models\Role::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role_name' => 'nullable|exists:roles,name', // Changed from role_id
            'sales_manager_id' => 'nullable|exists:users,id',
            'commission_split' => 'nullable|numeric|min:0|max:100',
            'company_lead_base' => 'nullable|numeric|min:0',
            'self_gen_base' => 'nullable|numeric|min:0',
            'legacy_id' => 'nullable|integer|unique:users,legacy_id,' . $user->id,
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        DB::transaction(function () use ($validated, $request, $user) {
            $contactIds = $validated['contact_ids'] ?? null;
            $roleName = $validated['role_name'] ?? null;
            
            unset($validated['contact_ids']);
            unset($validated['role_name']);

            Log::info('Updating user ' . $user->id . '. Contact IDs received:', ['contact_ids' => $contactIds, 'has_contact_ids' => $request->has('contact_ids')]);
            
            if (filled($request->password)) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);
            
            if ($roleName) {
                $user->syncRoles([$roleName]);
            } else {
                // If role_name is explicitly null/empty in request (meaning removal), verify if we should detach?
                // Typically if field is present but empty, we might unset role?
                // For now assuming if provided, we sync.
            }

            if ($request->has('contact_ids')) {
                Log::info('Updating contacts for user ' . $user->id, ['contact_ids' => $contactIds]);
                
                // First, unassign all contacts currently assigned to this user
                DB::table('contacts')
                    ->where('user_id', $user->id)
                    ->update(['user_id' => null]);
                
                // Then assign the selected contacts
                if (!empty($contactIds)) {
                     DB::table('contacts')
                        ->whereIn('id', $contactIds)
                        ->update(['user_id' => $user->id]);
                }
            }
        });

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user is admin (using spatie permission)
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Only admins can delete users.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
