<?php

namespace App\Http\Middleware\MyMiddlewares;

use Closure;
use Exception;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $logged_in_user = $request->get('logged_in_user');
        if ($logged_in_user->role !== "Client") {

            throw new Exception(
                "Access denied: Only clients can access this route.",
                403
            );
        }

        try {
            $logged_in_client = $logged_in_user->client;
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        $request->attributes
            ->set(
                'logged_in_client',
                $logged_in_client
            );

        return $next($request);
    }
}
