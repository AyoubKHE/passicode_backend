<?php

namespace App\Http\Middleware\MyMiddlewares;

use Closure;
use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $logged_in_user = $request->get('logged_in_user');
        if ($logged_in_user->role !== "Super Admin") {

            Log::channel('is_super_admin_errors')->error(
                "\n\n" .
                "Description: Access denied: Only super admin can access this route.\n\n" .
                "Error message: - .\n\n" .
                "User ID: " . $logged_in_user->id . "\n\n" .
                "User Role: " . $logged_in_user->role . "\n\n" .
                "Ip: " . $request->ip() . "\n\n" .
                "User Agent: " . $request->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                "Access denied: Only super admin can access this route.",
                403
            );
        }

        try {
            $logged_in_admin = $logged_in_user->admin;
        } catch (Throwable $th) {

            Log::channel('is_super_admin_errors')->error(
                "\n\n" .
                "Description: Failed to load User model relations << ->load() function >>.\n\n" .
                "Error message: - .\n\n" .
                "User ID: " . $logged_in_user->id . "\n\n" .
                "User Role: " . $logged_in_user->role . "\n\n" .
                "Ip: " . $request->ip() . "\n\n" .
                "User Agent: " . $request->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        $request->attributes
            ->set(
                'logged_in_admin',
                $logged_in_admin
            );

        return $next($request);
    }
}
