<?php

namespace App\Http\Controllers\Users;

use Exception;
use Throwable;
use App\Models\Users\User;
use Illuminate\Http\Request;
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


    private function loadRequestedUser()
    {
        try {

            $this->requested_user = User::where(
                "id",
                $this->global_request_object->user_id
            )->first();
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->requested_user) {
            throw new Exception('Requested user not found.', 404);
        }

        if ($this->requested_user->role === "Super Admin") {
            throw new Exception('Access denied', 403);
        }
    }


    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedUser();

        $this->toggleActive();

        return response()->json([
            'message' => "User's active status updated successfully.",
        ], 200);
    }
}
