<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use Inertia\Inertia;

class SyncLogController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/SyncLogs/Index', [
            'logs' => SyncLog::orderBy('started_at', 'desc')->paginate(15)
        ]);
    }
}
