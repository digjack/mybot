<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/api/login',
        'api/vbot/send',
        '/api/task',
        '/api/task/*',
        '/api/label/*',
        '/api/label',
        '/api/plan',
        '/api/plan/*'
    ];
}
