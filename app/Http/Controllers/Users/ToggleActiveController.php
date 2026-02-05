<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ToggleActiveController extends Controller
{
    private Request $global_request_object;
    private User|null $requested_user;


    private function toggleActive()
    {
        $this->requested_user->is_active = $this->requested_user->is_active === 1 ? 0 : 1;
        $this->requested_user->updated_at = now();
        try {
            $is_updated = $this->requested_user->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('toggle_active_errors')->error(
                    "\n\n" .
                    "Description: Failed to update requested user's is_active status in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Requested User ID: " . $this->global_request_object->user_id . "\n\n" .
                    "Logged In User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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


    private function loadRequestedUser()
    {
        try {

            $this->requested_user = User::where(
                "id",
                $this->global_request_object->user_id
            )->first();
        } catch (Throwable $th) {
            try {
                Log::channel('toggle_active_errors')->error(
                    "\n\n" .
                    "Description: Failed to get requested user from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "Requested User ID: " . $this->global_request_object->user_id . "\n\n" .
                    "Logged In User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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

        if (!$this->requested_user) {
            try {
                Log::channel('toggle_active_errors')->error(
                    "\n\n" .
                    "Description: Requested user not found.\n\n" .
                    "Error message: - .\n\n" .
                    "Requested User ID: " . $this->global_request_object->user_id . "\n\n" .
                    "Logged In User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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
                'Requested user not found.',
                404
            );
        }

        if ($this->requested_user->role === "Super Admin") {
            try {
                Log::channel('toggle_active_errors')->error(
                    "\n\n" .
                    "Description: Attemp to toggle super admin account's is_active status.\n\n" .
                    "Error message: - .\n\n" .
                    "Requested User ID: " . $this->global_request_object->user_id . "\n\n" .
                    "Logged In User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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
                'Access denied',
                403
            );
        }
    }

    private function logRequest()
    {
        try {
            Log::channel('toggle_active_requests')->info(
                "\n\n" .
                "Description: User's active status updated successfully.\n\n" .
                "New Active Status: " . $this->requested_user->is_active . "\n\n" .
                "Requested User ID: " . $this->global_request_object->user_id . "\n\n" .
                "Logged In User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
        }

    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedUser();

        $this->toggleActive();

        $this->logRequest();

        return response()->json([
            'message' => "User's active status updated successfully.",
        ], 200);
    }
}
