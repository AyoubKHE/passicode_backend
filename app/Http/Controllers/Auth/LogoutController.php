<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;

class LogoutController extends Controller
{
    private Request $global_request_object;

    private function logoutUser()
    {
        $logged_in_user = $this->global_request_object->get('logged_in_user');

        if (!$logged_in_user->refresh_token) {
            throw new Exception('The user already logged out.', 403);
        }

        $logged_in_user->refresh_token = null;

        try {
            $is_updated = $logged_in_user->save();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_updated) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->logoutUser();

        return response()->json([
            'message' => 'User logged out successfully!',
        ], status: 200)->withCookie(
                cookie("refresh_token", '', httpOnly: true, secure: true, minutes: -1)
            );
    }
}
