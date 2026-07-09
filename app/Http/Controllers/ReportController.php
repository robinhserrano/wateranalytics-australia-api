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
                'active_reps' => $rowsForMonth->pluck('user_id')->unique()->count(),
            ];
        })->values();

        $usersQuery = $isAdmin ? User::query() : User::whereIn('id', $teamUserIds);

        $requestedMonth = $request->input('month');
        $monthDetail = null;
        if ($requestedMonth && $months->contains($requestedMonth)) {
            $monthDetail = $this->buildMonthDetail($requestedMonth, $scopedUserIds);
        }

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
            'monthDetail' => $monthDetail,
        ]);
    }

    /**
     * Order-level breakdown for a single month, scoped the same way as the
     * main report (never wider than $scopedUserIds).
     */
    private function buildMonthDetail(string $month, ?array $scopedUserIds): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $rows = CommissionCalculation::query()
            ->select('id', 'user_id', 'sales_order_id', 'profit')
            ->whereHas('salesOrder', function ($q) use ($monthStart, $monthEnd) {
                $q->where('state', 'sale')->whereBetween('create_date', [$monthStart, $monthEnd]);
            })
            ->when($scopedUserIds !== null, fn ($q) => $q->whereIn('user_id', $scopedUserIds))
            ->with(['salesOrder:id,name,partner_name,amount_total,create_date,delivery_status', 'user:id,name'])
            ->get();

        return [
            'month' => $month,
            'label' => Carbon::createFromFormat('Y-m', $month)->format("M 'y"),
            'total_sales' => (float) $rows->sum(fn ($r) => $r->salesOrder->amount_total),
            'total_profit' => (float) $rows->sum('profit'),
            'active_reps' => $rows->pluck('user_id')->unique()->count(),
            'orders' => $rows->map(fn ($r) => [
                'id' => $r->salesOrder->id,
                'name' => $r->salesOrder->name,
                'partner_name' => $r->salesOrder->partner_name,
                'owner' => $r->user->name ?? null,
                'amount_total' => (float) $r->salesOrder->amount_total,
                'profit' => (float) $r->profit,
                'delivery_status' => $r->salesOrder->delivery_status,
                'create_date' => optional($r->salesOrder->create_date)->toDateString(),
            ])->sortByDesc('create_date')->values(),
        ];
    }
}
