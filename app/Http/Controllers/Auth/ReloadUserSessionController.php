<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ReloadUserSessionController extends Controller
{
    private Request $global_request_object;
    private User|null $user;

    private function eagerLoadUserRelations(): void
    {
        try {
            if ($this->user->role === "Super Admin" || $this->user->role === "Admin") {
                $this->user->load('admin');
            } else if ($this->user->role === "Client") {
                $this->user->load('client');
            }
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function prepareAccessToken(): string
    {
        $access_token_payload = [
            "iat" => time(),
            "exp" => time() + 3600,
            "user_data" => array(
                "user_id" => $this->user->id,
            )
        ];

        $access_token_object = new JWTService($access_token_payload);
        return $access_token_object->getJwtToken();
    }

    private function logoutUser()
    {
        $refresh_token_payload = JWTService::getTokenPayload(
            $this->global_request_object->cookie("refresh_token")
        );

        try {
            $this->user = User::where("id", $refresh_token_payload["user_data"]->user_id)->first();

        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->user) {
            throw new Exception('User not found', 404);
        }

        $this->user->refresh_token = null;

        try {
            $is_updated = $this->user->save();
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
    private function checkRefreshTokenValidity()
    {
        $refresh_token = $this->global_request_object->cookie("refresh_token");

        if (!$refresh_token) {
            throw new Exception(
                "The refresh token is missing or invalid format.",
                400
            );
        }

        try {
            $refresh_token_payload = JWTService::checkTokenValidity(
                $this->global_request_object->cookie("refresh_token")
            );

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {

                $this->logoutUser();

                throw new Exception(
                    "The refresh token has expired. Authentication required.",
                    401
                );
            } else {
                throw new Exception(
                    "The refresh token is invalid. Authentication required.",
                    401
                );
            }
        }

        try {
            $this->user = User::where("id", $refresh_token_payload["user_data"]->user_id)->first();

        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->user) {
            throw new Exception('User not found', 404);
        }

        if (
            !Hash::check(
                $this->global_request_object->cookie("refresh_token"),
                $this->user->refresh_token
            )
        ) {
            throw new Exception(
                "The refresh token is invalid. Authentication required.",
                401
            );
        }
    }

    public function __invoke(Request $request)
    {
        $this->global_request_object = $request;

        $this->checkRefreshTokenValidity();

        $this->eagerLoadUserRelations();

        $access_token = $this->prepareAccessToken();

        return response()->json([
            'access_token' => $access_token,
            'user' => $this->user
        ], status: 200);
    }
}
