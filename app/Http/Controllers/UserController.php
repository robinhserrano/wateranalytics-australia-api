<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('roles', 'salesManager', 'team', 'contacts:id,user_id,odoo_id,display_name,odoo_user_ids')
            ->select('users.*')
            ->addSelect([
                'latest_sale_date' => SalesOrder::select('create_date')
                    ->whereIn('salesperson_partner_id', function ($query) {
                        $query->select('odoo_id')
                            ->from('contacts')
                            ->whereColumn('contacts.user_id', 'users.id');
                    })
                    ->orderBy('create_date', 'desc')
                    ->limit(1),
            ])
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
        $user = $user->fresh()->load('contacts', 'roles', 'salesManager', 'team');

        $partnerIds = $user->contacts->pluck('odoo_id')->toArray();

        // Fetch Sales activity. Mirrors CommissionCalculator::resolveSalesperson()'s
        // fallback chain so an order attributed to this user via any rung (not just
        // a direct Contact/salesperson_partner_id match) still shows up here.
        $salesOrders = SalesOrder::where(function ($query) use ($partnerIds, $user) {
            $query->whereIn('salesperson_partner_id', $partnerIds);

            if ($user->odoo_user_id) {
                $query->orWhere('user_id', $user->odoo_user_id);
            }

            if ($user->odoo_salesperson_id) {
                $query->orWhere('user_id', $user->odoo_salesperson_id);
            }

            if ($user->name) {
                $query->orWhere('user_name', $user->name);
            }
        })
            ->orderBy('create_date', 'desc')
            ->limit(50)
            ->get();

        $latestSale = $salesOrders->first();

        // Calculate activity status: Active if latest sale is within 6 months
        $isActive = $latestSale && $latestSale->create_date && $latestSale->create_date->gt(now()->subMonths(6));

        $commissionStats = [
            'is_active' => $isActive,
            'latest_sale' => $latestSale ? [
                'name' => $latestSale->name,
                'date' => $latestSale->create_date ? $latestSale->create_date->format('M d, Y') : null,
                'amount' => $latestSale->amount_total,
            ] : null,
        ];

        return Inertia::render('Users/Show', [
            'user' => $user,
            'commissionStats' => $commissionStats,
            'salesOrders' => $salesOrders,
        ]);
    }

    public function create()
    {

        /**
         * Show the form for creating a new resource.
         */
        return Inertia::render('Users/Create', [
            'contacts' => Contact::whereNotNull('odoo_user_ids')
                ->where('odoo_user_ids', '!=', '[]')
                ->select('odoo_id', 'display_name', 'user_id', 'odoo_user_ids', 'email')
                ->get(),
            'roles' => Role::all(),
        ]);

        /**
         * Store a newly created resource in storage.
         */
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role_names' => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
            'sales_manager_id' => 'nullable|exists:users,id',
            'commission_split' => 'nullable|numeric|min:0|max:100',
            'company_lead_base' => 'nullable|numeric|min:0',
            'self_gen_base' => 'nullable|numeric|min:0',
            'legacy_id' => 'nullable|integer|unique:users,legacy_id',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,odoo_id',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $contactIds = $validated['contact_ids'] ?? null;
            $roleNames = $validated['role_names'] ?? [];

            unset($validated['contact_ids']);
            unset($validated['role_names']);

            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = true;

            $user = User::create($validated);

            if (! empty($roleNames)) {
                $user->assignRole($roleNames);
            }

            if ($contactIds) {
                DB::table('contacts')
                    ->whereIn('odoo_id', $contactIds)
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
            'user' => $user->fresh()->load('contacts', 'roles'),
            'contacts' => Contact::whereNotNull('odoo_user_ids')
                ->where('odoo_user_ids', '!=', '[]')
                ->select('odoo_id', 'display_name', 'user_id', 'odoo_user_ids', 'email')
                ->get(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $isAdmin = auth()->user()->hasRole('Admin');

        // Only admins may edit another user's record or change privileged fields
        // (role, manager, commission rates). Everyone else may only update their
        // own name/email/password.
        if (! $isAdmin) {
            if (auth()->id() !== $user->id) {
                abort(403, 'Unauthorized action.');
            }

            $request->request->remove('role_names');
            $request->request->remove('sales_manager_id');
            $request->request->remove('commission_split');
            $request->request->remove('company_lead_base');
            $request->request->remove('self_gen_base');
            $request->request->remove('legacy_id');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'role_names' => 'nullable|array',
            'role_names.*' => 'exists:roles,name',
            'sales_manager_id' => 'nullable|exists:users,id',
            'commission_split' => 'nullable|numeric|min:0|max:100',
            'company_lead_base' => 'nullable|numeric|min:0',
            'self_gen_base' => 'nullable|numeric|min:0',
            'legacy_id' => 'nullable|integer|unique:users,legacy_id,'.$user->id,
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,odoo_id',
        ]);

        DB::transaction(function () use ($validated, $request, $user) {
            $contactIds = $validated['contact_ids'] ?? null;
            $roleNames = $validated['role_names'] ?? [];

            unset($validated['contact_ids']);
            unset($validated['role_names']);

            if (filled($request->password)) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            $user->syncRoles($roleNames);

            // Clear Spatie permission cache so next page load reflects new roles
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            if ($request->has('contact_ids')) {

                // First, unassign all contacts currently assigned to this user
                DB::table('contacts')
                    ->where('user_id', $user->id)
                    ->update(['user_id' => null]);

                // Then assign the selected contacts
                if (! empty($contactIds)) {
                    DB::table('contacts')
                        ->whereIn('odoo_id', $contactIds)
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
        if (! auth()->user()->hasRole('Admin')) {
            abort(403, 'Only admins can delete users.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function export(Request $request)
    {
        // Only allow admins
        if (! auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $filename = $request->query('filename', 'Commission Users.csv');
        if (! str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        $users = User::with('roles', 'contacts')->orderBy('name')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID',
            'Name',
            'Email',
            'Role',
            'Status',
            'Commission Split (%)',
            'Company Lead Base ($)',
            'Self Gen Base ($)',
            'Linked Contacts',
        ];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->join(', ');
                $contacts = $user->contacts->map(function ($c) {
                    $uid = $c->odoo_user_ids[0] ?? 'N/A';

                    return "[{$uid}] {$c->display_name}";
                })->join(' | ');

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $roles ?: 'None',
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->commission_split ?? 0,
                    $user->company_lead_base ?? 0,
                    $user->self_gen_base ?? 0,
                    $contacts ?: 'None',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
