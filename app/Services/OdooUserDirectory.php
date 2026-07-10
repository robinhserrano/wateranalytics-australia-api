<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Obuchmann\OdooJsonRpc\Odoo;

/**
 * Fetches and caches the Odoo res.users -> res.partner mapping.
 *
 * SyncContacts and SyncOdooSalesOrders both need this same data; fetching it
 * once here avoids paging through every Odoo user twice per sync cycle.
 */
class OdooUserDirectory
{
    private const CACHE_KEY = 'odoo_users_directory';

    // ponytail: 5min TTL, shorter than the 1-min sync tick so it still refreshes regularly
    private const CACHE_TTL = 300;

    /**
     * @return array<int, array{id: int, active: bool, partner_id: ?int, partner_name: string}>
     */
    public function users(Odoo $odoo): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () use ($odoo) {
            $users = [];
            $offset = 0;
            $limit = 1000;

            do {
                $response = retry(3, fn () => $odoo->executeKw('res.users', 'web_search_read', [
                    [['active', 'in', [true, false]]],
                    [
                        'partner_id' => (object) ['fields' => (object) ['id' => (object) [], 'display_name' => (object) []]],
                        'active' => (object) [],
                    ],
                    $offset,
                    $limit,
                    'id asc',
                ]), 500);

                $batch = $response->records ?? (is_array($response) ? ($response['records'] ?? []) : []);

                foreach ($batch as $u) {
                    $u = (object) $u;
                    $partnerRaw = $u->partner_id ?? null;
                    $partnerId = null;
                    $partnerName = 'Unknown (Ghost)';

                    if (is_array($partnerRaw)) {
                        $partnerId = $partnerRaw[0] ?? null;
                        $partnerName = $partnerRaw[1] ?? 'Unknown (Ghost)';
                    } elseif (is_object($partnerRaw)) {
                        $partnerId = $partnerRaw->id ?? null;
                        $partnerName = $partnerRaw->display_name ?? 'Unknown (Ghost)';
                    }

                    $users[] = [
                        'id' => $u->id,
                        'active' => $u->active ?? true,
                        'partner_id' => $partnerId,
                        'partner_name' => $partnerName,
                    ];
                }

                $offset += $limit;
            } while (count($batch) === $limit);

            return $users;
        });
    }
}
