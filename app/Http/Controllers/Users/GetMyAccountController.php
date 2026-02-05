<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserResource;

class GetMyAccountController extends Controller
{
    private Request $global_request_object;
    private User|null $logged_in_user;

    private function loadLoggedInUser()
    {
        $this->logged_in_user = $this->global_request_object->get('logged_in_user');

        try {
            if ($this->logged_in_user->role === "Super Admin" || $this->logged_in_user->role === "Admin") {
                $this->logged_in_user->load('admin');
            } else if ($this->logged_in_user->role === "Client") {
                $this->logged_in_user->load('client');
            }
        } catch (Throwable $th) {
            try {
                Log::channel('get_my_account_errors')->error(
                    "\n\n" .
                    "Description: Failed to load User model relations << ->load() function >>.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $this->global_request_object->get('logged_in_user')->id . "\n\n" .
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

        $this->loadLoggedInUser();

        return response()->json([
            'user' => new UserResource($this->logged_in_user),
        ], 200);
    }
}
