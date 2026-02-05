<?php

namespace App\Http\Middleware\MyMiddlewares;

use Closure;
use Exception;
use Throwable;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class UsersJwtAuthentication
{
    private Request $global_request_object;
    private string $logged_in_user_id;
    private string|null $new_access_token;
    private bool $is_session_expired;

    private function buildNewAccessToken()
    {
        $bearer_token = $this->global_request_object->header('Authorization');

        $access_token = substr($bearer_token, 7);

        $access_token_payload = JWTService::getTokenPayload($access_token);

        $this->logged_in_user_id = $access_token_payload["user_data"]->user_id;

        $new_access_token_payload = [
            "iat" => time(),
            "exp" => time() + 3600,
            "user_data" => array(
                "user_id" => $this->logged_in_user_id,
            )
        ];

        $new_access_token_object = new JWTService($new_access_token_payload);

        $this->new_access_token = $new_access_token_object->getJwtToken();
    }


    private function logoutUser()
    {
        $refresh_token_payload = JWTService::getTokenPayload(
            $this->global_request_object->cookie("refresh_token")
        );

        try {
            $user = User::where("id", $refresh_token_payload["user_data"]->user_id)->first();

        } catch (Throwable $th) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
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
            } catch (Throwable $th) {
                //throw $th;
            }

            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$user) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Logged in user not found.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
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
                'Logged in user not found.',
                404
            );
        }

        $user->refresh_token = null;

        try {
            $is_updated = $user->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }
        } catch (Throwable $th) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Failed to update user's refresh token in database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $user->id . "\n\n" .
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
    private function manageExpiredToken()
    {
        $refresh_token = $this->global_request_object->cookie("refresh_token");

        if (!$refresh_token) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: The refresh token is missing.\n\n" .
                    "Error message: - .\n\n" .
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
                "The refresh token is missing.",
                400
            );
        }

        try {

            JWTService::checkTokenValidity($refresh_token);

            $this->buildNewAccessToken();

        } catch (Exception $e) {

            if (get_class($e) === "Firebase\JWT\ExpiredException") {

                $refresh_token_payload = JWTService::getTokenPayload(
                    $refresh_token
                );

                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: The refresh token is expired.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: " . $refresh_token_payload["user_data"]->user_id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                $this->logoutUser();
            } else {

                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: The refresh token is invalid.\n\n" .
                    "Error message: - .\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            }

            $this->is_session_expired = true;
        }

    }

    private function loadLoggedInUser()
    {
        try {
            $logged_in_user = User::find($this->logged_in_user_id);

        } catch (Throwable $th) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Failed to get user from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: " . $this->logged_in_user_id . "\n\n" .
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

        if (!$logged_in_user) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Logged in user not found.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: " . $this->logged_in_user_id . "\n\n" .
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
                'Logged in user not found.',
                404
            );
        }

        if (!$logged_in_user->is_active) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: The logged in user has been suspended.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: " . $this->logged_in_user_id . "\n\n" .
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
                "The logged in user has been suspended.",
                403
            );
        }

        $this->global_request_object->attributes
            ->set(
                'logged_in_user',
                $logged_in_user
            );
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $bearer_token = $request->header('Authorization');

        if (!$bearer_token || !str_starts_with($bearer_token, 'Bearer ')) {
            try {
                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Authorization token is missing or invalid format.\n\n" .
                    "Error message: - .\n\n" .
                    "Ip: " . $request->ip() . "\n\n" .
                    "User Agent: " . $request->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );
            } catch (Throwable $th) {
                //throw $th;
            }

            throw new Exception(
                "Authorization token is missing or invalid format.",
                400
            );
        }

        $this->global_request_object = $request;

        $this->new_access_token = null;

        $this->is_session_expired = false;

        $access_token = substr($bearer_token, 7);

        try {

            $access_token_payload = JWTService::checkTokenValidity($access_token);

            $this->logged_in_user_id = $access_token_payload["user_data"]->user_id;

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {

                $access_token_payload = JWTService::getTokenPayload(
                    $access_token
                );

                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Authorization token is expired.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: " . $access_token_payload["user_data"]->user_id . "\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                $this->manageExpiredToken();

            } else {

                Log::channel('users_jwt_authentication_errors')->error(
                    "\n\n" .
                    "Description: Authorization token is invalid.\n\n" .
                    "Error message: - .\n\n" .
                    "Ip: " . $this->global_request_object->ip() . "\n\n" .
                    "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                    "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n" .
                    "----------------------------------------------------------------------------------------------------------------------------------\n\n"
                );

                $this->is_session_expired = true;
            }
        }

        if ($this->is_session_expired) {
            return response()->json(
                [
                    "error" => "User session has expired. Authentication required.",
                ]
                ,
                401
            )->withCookie(
                    cookie("refresh_token", '', httpOnly: true, secure: true, minutes: -1)
                );
        }

        $this->loadLoggedInUser();

        if ($this->new_access_token) {

            $response = $next($request);
            $data = $response->getData(true);

            if ($response->getStatusCode() === 200) {
                if (
                    array_key_exists('message', $data) &&
                    $data['message'] === 'User logged out successfully!'
                ) {
                    return response()->json($data, $response->getStatusCode())
                        ->withCookie(
                            cookie("refresh_token", '', httpOnly: true, secure: true, minutes: -1)
                        );
                }
            }

            $data['new_access_token'] = $this->new_access_token;

            return response()->json($data, $response->getStatusCode());
        }

        return $next($request);
    }
}
