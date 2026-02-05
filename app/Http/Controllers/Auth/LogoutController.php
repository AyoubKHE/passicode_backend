<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class LogoutController extends Controller
{
    private Request $global_request_object;

    private function logoutUser()
    {
        $logged_in_user = $this->global_request_object->get('logged_in_user');

        $logged_in_user->refresh_token = null;

        try {
            $is_updated = $logged_in_user->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }

            try {
                Log::channel('logout_requests')->info(
                    "\n\n" .
                    "Description: User logged out successfully.\n\n" .
                    "User ID: " . $logged_in_user->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }


        } catch (Throwable $th) {
            try {
                Log::channel('logout_errors')->error(
                    "\n\n" .
                    "Description: Failed to logout user.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $logged_in_user->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

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
