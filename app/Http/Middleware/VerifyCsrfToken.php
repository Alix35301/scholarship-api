<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;
use Illuminate\Http\Request;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        // Add debugging
        \Log::info('CSRF Token from request:', [
            'token' => $request->header('X-CSRF-TOKEN'),
            'xsrf' => $request->header('X-XSRF-TOKEN'),
            'cookie' => $request->cookie('XSRF-TOKEN'),
        ]);

        return parent::handle($request, $next);
    }
}
