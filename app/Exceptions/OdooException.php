<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * An error Odoo itself returned (HTTP 200 with an `error` body): access rights, missing record, bad
 * domain, failed login... Deterministic, so never retried. `$name` is Odoo's exception class, e.g.
 * `odoo.exceptions.AccessError`.
 */
class OdooException extends RuntimeException
{
    public function __construct(string $message, int $code = 0, public readonly ?string $name = null)
    {
        parent::__construct($message, $code);
    }
}
