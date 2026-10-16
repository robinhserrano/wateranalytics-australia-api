<?php

namespace App\Services;

use App\Exceptions\OdooException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class OdooClient
{
    // ponytail: sized to Horizon's 60s job timeout (config/horizon.php), not to Odoo: one call's worst
    // case (25s + backoff + 25s) still fits. Longer outages are left to the job's own retries/backoff,
    // which resume from sync_runs.resume_offset. Raise these together with the job timeout.
    protected const TIMEOUT_SECONDS = 25;

    protected const CONNECT_TIMEOUT_SECONDS = 5;

    protected const ATTEMPTS = 2;

    protected const MAX_RETRY_AFTER_MS = 5000;

    // HTTP statuses that mean "try again shortly", not "your request is wrong"
    protected const TRANSIENT_STATUSES = [429, 502, 503, 504];

    protected int $uid = 0;

    protected string $url;

    protected string $db;

    protected string $login;

    protected string $apiKey;

    // decoded results as objects (`$order->partner_id->id`) instead of arrays; the commissions app,
    // ported off obuchmann/odoo-jsonrpc, sets odoo.as_objects so its sync code needn't change
    protected bool $asObjects;

    public function __construct()
    {
        $this->url = rtrim(config('odoo.url'), '/');
        $this->db = config('odoo.db');
        $this->login = config('odoo.login');
        $this->apiKey = config('odoo.api_key');
        $this->asObjects = (bool) config('odoo.as_objects', false);
    }

    public function authenticate(): int
    {
        $uid = $this->call('common', 'authenticate', [$this->db, $this->login, $this->apiKey, []]);

        if (! $uid) {
            throw new OdooException("Odoo authentication failed for '{$this->login}' on database '{$this->db}'");
        }

        return $this->uid = $uid;
    }

    /**
     * @param  array  $fields  dot-notation for relations, e.g. ['name', 'partner_id.display_name']
     */
    public function searchRead(string $model, array $domain = [], array $fields = [], int $offset = 0, int $limit = 0, string $order = ''): array
    {
        return (array) $this->executeKw($model, 'search_read', [$domain], ['fields' => $fields, 'offset' => $offset, 'limit' => $limit, 'order' => $order]);
    }

    /**
     * @param  array  $ids  Odoo record ids
     * @param  array  $fields  dot-notation for relations
     */
    public function read(string $model, array $ids, array $fields = []): array
    {
        return (array) $this->executeKw($model, 'read', [$ids], ['fields' => $fields]);
    }

    public function searchCount(string $model, array $domain = []): int
    {
        return (int) $this->executeKw($model, 'search_count', [$domain]);
    }

    /**
     * Any model method, e.g. executeKw('sale.order', 'web_search_read', [$domain, $specification, 0, 500]).
     */
    public function executeKw(string $model, string $method, array $args = [], array $kwargs = []): mixed
    {
        if (! $this->uid) {
            $this->authenticate();
        }

        return $this->call('object', 'execute_kw', [
            $this->db, $this->uid, $this->apiKey,
            $model, $method,
            $args,
            ...($kwargs ? [$kwargs] : []),
        ]);
    }

    protected function call(string $service, string $method, array $args): mixed
    {
        // every call here is a read, so retrying is safe (no duplicate side effects in Odoo)
        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->retry(self::ATTEMPTS, fn (int $attempt, Throwable $e) => $this->retryDelayMs($attempt, $e), fn (Throwable $e) => $this->isTransient($e))
            ->post("{$this->url}/jsonrpc", [
                'jsonrpc' => '2.0',
                'method' => 'call',
                'params' => ['service' => $service, 'method' => $method, 'args' => $args],
            ]);

        $json = json_decode($response->body(), ! $this->asObjects);

        if (data_get($json, 'error')) {
            throw new OdooException(
                data_get($json, 'error.data.message') ?? data_get($json, 'error.message') ?? 'Odoo RPC error',
                (int) data_get($json, 'error.code', 0),
                data_get($json, 'error.data.name'),
            );
        }

        return data_get($json, 'result');
    }

    protected function isTransient(Throwable $e): bool
    {
        return $e instanceof ConnectionException
            || ($e instanceof RequestException && in_array($e->response->status(), self::TRANSIENT_STATUSES, true));
    }

    // honour Retry-After when Odoo/a proxy sends one, else exponential backoff with jitter
    protected function retryDelayMs(int $attempt, Throwable $e): int
    {
        $retryAfter = $e instanceof RequestException ? $e->response->header('Retry-After') : '';

        if (is_numeric($retryAfter)) {
            return min((int) $retryAfter * 1000, self::MAX_RETRY_AFTER_MS);
        }

        return 250 * 2 ** ($attempt - 1) + random_int(0, 250);
    }
}
