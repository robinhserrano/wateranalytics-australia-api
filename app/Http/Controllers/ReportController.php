<?php

namespace App\Http\Controllers;

use App\Models\CommissionCalculation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Monthly sales/profit report.
     * - Admin: sees all users.
     * - Sales Manager: scoped to their own team (User::getTeamUserIds()).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('Admin');
        $teamUserIds = $isAdmin ? null : $user->getTeamUserIds();

        // Server-side scoping: a Sales Manager can only ever query within their
        // own team, even if they tamper with the user_ids request param.
        $requestedUserIds = array_map('intval', (array) $request->input('user_ids', []));
        $scopedUserIds = $isAdmin
            ? ($requestedUserIds ?: null)
            : ($requestedUserIds ? array_values(array_intersect($requestedUserIds, $teamUserIds)) : $teamUserIds);

        $start = now()->subMonths(11)->startOfMonth();

        $rows = CommissionCalculation::query()
            ->select('id', 'user_id', 'sales_order_id', 'profit')
            ->whereHas('salesOrder', function ($q) use ($start) {
                $q->where('state', 'sale')->where('create_date', '>=', $start);
            })
            ->when($scopedUserIds !== null, fn ($q) => $q->whereIn('user_id', $scopedUserIds))
            ->with('salesOrder:id,amount_total,create_date')
            ->get();

        // Bucket in PHP (not raw SQL) to stay portable across MySQL (prod) and SQLite (tests).
        $months = collect(range(0, 11))->map(fn ($i) => now()->subMonths(11 - $i)->format('Y-m'));
        $byMonth = $rows->groupBy(fn ($row) => $row->salesOrder->create_date->format('Y-m'));

        $monthly = $months->map(function ($month) use ($byMonth) {
            $rowsForMonth = $byMonth->get($month, collect());

            return [
                'month' => $month,
                'label' => Carbon::createFromFormat('Y-m', $month)->format("M 'y"),
                'total_sales' => (float) $rowsForMonth->sum(fn ($r) => $r->salesOrder->amount_total),
                'total_profit' => (float) $rowsForMonth->sum('profit'),
            ];
        })->values();

        $usersQuery = $isAdmin ? User::query() : User::whereIn('id', $teamUserIds);

        return Inertia::render('Reports/Index', [
            'monthly' => $monthly,
            'totals' => [
                'total_sales' => (float) $monthly->sum('total_sales'),
                'total_profit' => (float) $monthly->sum('total_profit'),
            ],
            'users' => $usersQuery->whereHas('commissionCalculations')
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'filters' => [
                'user_ids' => $requestedUserIds,
            ],
        ]);
    }
}
