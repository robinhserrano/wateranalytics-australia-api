<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstallationTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query()
            ->whereNotNull('installation_date')
            ->orderBy('installation_date', 'asc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%");
            });
        }

        $tasks = $query->paginate(80)->withQueryString();

        return Inertia::render('InstallationTasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['search']),
        ]);
    }
}
