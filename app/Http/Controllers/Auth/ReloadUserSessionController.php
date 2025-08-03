<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\Request;
use App\Models\Settings\Setting;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ReloadUserSessionController extends Controller
{
    private Request $global_request_object;
    private User|null $user;

    private function loadIsAdminAvailableForBackorder()
    {
        if ($this->user->role === "Super Admin" || $this->user->role === "Admin") {
            try {

                $is_admin_available_for_backorder = Setting::where(
                    "key",
                    "is_admin_available_for_backorder"
                )
                    ->value('value');
            } catch (Throwable $th) {

                Log::channel('reload_user_session_errors')->error(
                    "\n\n" .
                    "Description: Failed to get << is_admin_available_for_backorder >> information from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $this->user->id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                throw new Exception(
                    'An error occurred while accessing the database. Please try again later.',
                    500
                );
            }

            $this->user->is_admin_available_for_backorder = $is_admin_available_for_backorder;
        }
    }

    private function eagerLoadUserRelations(): void
    {
        try {
            if ($this->user->role === "Super Admin" || $this->user->role === "Admin") {
                $this->user->load('admin');
            } else if ($this->user->role === "Client") {
                $this->user->load('client');
            }
        } catch (Throwable $th) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: Failed to load User model relations << ->load() function >>.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "User ID: " . $this->user->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

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

        } catch (Throwable $th) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: Failed to get user from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->user) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: User not found.\n\n" .
                "Error message: - .\n\n" .
                "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception('User not found', 404);
        }

        $this->user->refresh_token = null;

        try {
            $is_updated = $this->user->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: Failed to logout user.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "User ID: " . $this->user->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

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

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: Failed to reload user session: The refresh token is missing.\n\n" .
                "Error message: - .\n\n" .
                "User ID: - .\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                "The refresh token is missing.",
                400
            );
        }

        try {
            $refresh_token_payload = JWTService::checkTokenValidity(
                $this->global_request_object->cookie("refresh_token")
            );

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {

                Log::channel('reload_user_session_errors')->error(
                    "\n\n" .
                    "Description: Failed to reload user session: The refresh token is expired.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: - .\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                $this->logoutUser();

                throw new Exception(
                    "The refresh token has expired. Authentication required.",
                    401
                );
            } else {

                Log::channel('reload_user_session_errors')->error(
                    "\n\n" .
                    "Description: Failed to reload user session: The refresh token is invalid.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: - .\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                throw new Exception(
                    "The refresh token is invalid. Authentication required.",
                    401
                );
            }
        }

        try {
            $this->user = User::where(
                "id",
                $refresh_token_payload["user_data"]->user_id
            )
                ->first();

        } catch (Throwable $th) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: Failed to get user from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->user) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: User not found.\n\n" .
                "Error message: - .\n\n" .
                "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception('User not found', 404);
        }

        if (
            !Hash::check(
                $this->global_request_object->cookie("refresh_token"),
                $this->user->refresh_token
            )
        ) {

            Log::channel('reload_user_session_errors')->error(
                "\n\n" .
                "Description: Failed to reload user session: The refresh token received from the request does not match the refresh token stored in the database.\n\n" .
                "Error message: - .\n\n" .
                "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                "The refresh token is invalid. Authentication required.",
                401
            );
        }
    }

    private function logRequest()
    {
        try {
            if ($this->user) {

                if ($this->user->role !== "Super Admin") {
                    Log::channel('reload_user_session_requests')->info(
                        "\n\n" .
                        "Description: New reload user session request from an existing user.\n\n" .
                        "User ID: " . $this->user->id . "\n\n" .
                        "Ip: " . $this->global_request_object->ip() . "\n\n" .
                        "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                        "----------------------------------------------------------------------------------------------------------------------------------\n" .
                        "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                    );
                }
            } else {
                Log::channel('reload_user_session_requests')->info(
                    "\n\n" .
                    "Description: New reload user session request.\n\n" .
                    "User ID: - .\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            }
        } catch (Throwable $th) {
            //throw $th;
        }

    }

    public function __invoke(Request $request)
    {

        try {

            $this->global_request_object = $request;

            $this->user = null;

            $this->checkRefreshTokenValidity();

            $this->eagerLoadUserRelations();

            $access_token = $this->prepareAccessToken();

            $this->loadIsAdminAvailableForBackorder();

            $this->logRequest();

            return response()->json([
                'access_token' => $access_token,
                'user' => $this->user
            ], status: 200);
        } catch (Throwable $th) {

            $this->logRequest();

            throw $th;
        }
    }
}
