<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddAuthorizationHeader
{
    /**
     * When Apache strips the Authorization header, it can be passed via .htaccess
     * as HTTP_AUTHORIZATION or REDIRECT_HTTP_AUTHORIZATION. Copy into the request
     * so Sanctum can authenticate.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasHeader('Authorization') && ! $request->headers->has('Authorization')) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
            if (($auth === null || $auth === '') && $request->hasHeader('X-Authorization')) {
                $auth = $request->header('X-Authorization');
                $auth = str_starts_with($auth, 'Bearer ') ? $auth : 'Bearer ' . trim($auth);
            }
            if ($auth !== null && $auth !== '') {
                $request->headers->set('Authorization', $auth, false);
            }
        }

        return $next($request);
    }
}
