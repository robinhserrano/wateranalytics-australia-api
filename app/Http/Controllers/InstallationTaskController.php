<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InstallationTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query()
            ->whereNotNull('installation_date')
            ->whereNotNull('installer_id')   // exclude supply-only / unassigned orders
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
            'tasks'   => $tasks,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        abort_unless($salesOrder->installation_date !== null, 404);

        $salesOrder->load(['lines', 'partner']);

        return Inertia::render('InstallationTasks/Show', [
            'task' => $salesOrder,
        ]);
    }

    /**
     * Proxy Odoo mail/thread/messages for a project.task.
     * Called via AJAX from the Show page.
     */
    public function messages(SalesOrder $salesOrder)
    {
        abort_unless($salesOrder->odoo_task_id !== null, 404, 'No Odoo task linked to this order.');

        $odooHost = rtrim(config('odoo.host'), '/');
        $odooUsername = config('odoo.username');
        $odooPassword = config('odoo.password');
        $odooDB = config('odoo.database');

        try {
            $cookieJar = new \GuzzleHttp\Cookie\CookieJar();

            // Step 1: authenticate to get a session cookie
            $authResponse = Http::withOptions(['cookies' => $cookieJar])
                ->post("{$odooHost}/web/session/authenticate", [
                    'jsonrpc' => '2.0',
                    'method'  => 'call',
                    'id'      => 1,
                    'params'  => [
                        'db'       => $odooDB,
                        'login'    => $odooUsername,
                        'password' => $odooPassword,
                    ],
                ]);

            if ($authResponse->failed()) {
                return response()->json(['error' => 'Could not authenticate with Odoo'], 500);
            }

            // Step 2: fetch messages using the same cookie jar
            $messagesResponse = Http::withOptions(['cookies' => $cookieJar])
                ->post("{$odooHost}/mail/thread/messages", [
                    'jsonrpc' => '2.0',
                    'method'  => 'call',
                    'id'      => 1,
                    'params'  => [
                        'thread_id'    => (int)$salesOrder->odoo_task_id,
                        'thread_model' => 'project.task',
                        'limit'        => 50,
                    ]
                ]);

            return response()->json($messagesResponse->json());

        } catch (\Exception $e) {
            Log::error('Failed to fetch Odoo task messages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch messages from Odoo'], 500);
        }
    }

    /**
     * Proxy Odoo attachments so images load properly without third-party cookie restrictions.
     */
    public function attachment($attachmentId)
    {
        $odooHost = rtrim(config('odoo.host'), '/');
        $odooUsername = config('odoo.username');
        $odooPassword = config('odoo.password');
        $odooDB = config('odoo.database');

        try {
            $cookieJar = new \GuzzleHttp\Cookie\CookieJar();

            // Step 1: authenticate
            $authResponse = Http::withOptions(['cookies' => $cookieJar])
                ->post("{$odooHost}/web/session/authenticate", [
                    'jsonrpc' => '2.0',
                    'method'  => 'call',
                    'id'      => 1,
                    'params'  => [
                        'db'       => $odooDB,
                        'login'    => $odooUsername,
                        'password' => $odooPassword,
                    ],
                ]);

            if ($authResponse->failed()) {
                abort(500, 'Could not authenticate with Odoo');
            }

            // Step 2: fetch image
            $imageResponse = Http::withOptions(['cookies' => $cookieJar])
                ->get("{$odooHost}/web/image/{$attachmentId}");

            if ($imageResponse->failed()) {
                abort($imageResponse->status(), 'Failed to fetch image from Odoo');
            }

            return response($imageResponse->body())
                ->header('Content-Type', $imageResponse->header('Content-Type'));

        } catch (\Exception $e) {
            Log::error('Failed to proxy Odoo attachment: ' . $e->getMessage());
            abort(500, 'Error fetching attachment');
        }
    }
}
