<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Clients\Client;
use App\Models\Settings\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\Users\UserResource;
use App\Http\Requests\Auth\GoogleLoginRequest;

class GoogleLoginController extends Controller
{
    private GoogleLoginRequest $global_request_object;
    private User|null $user;
    private array $google_response;


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

                Log::channel('google_login_errors')->error(
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

    private function prepareRefreshToken(): string
    {
        $refresh_token_payload = [
            "iat" => time(),
            "exp" => time() + 2592000, // 1 mois
            "user_data" => array(
                "user_id" => $this->user->id,
            )
        ];

        $refresh_token_object = new JWTService($refresh_token_payload);

        $refresh_token = $refresh_token_object->getJwtToken();

        $this->user->refresh_token = Hash::make($refresh_token);
        $this->user->last_login = now();

        try {
            $is_updated = $this->user->save();

            if (!$is_updated) {
                throw new Exception(
                    "- .",
                    500
                );
            }

        } catch (Throwable $th) {

            Log::channel('google_login_errors')->error(
                "\n\n" .
                "Description: Failed to store user's new refresh token after successful login.\n\n" .
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

        return $refresh_token;
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

    private function eagerLoadUserRelations(): void
    {
        try {
            if ($this->user->role === "Super Admin" || $this->user->role === "Admin") {
                $this->user->load('admin');

            } else if ($this->user->role === "Client") {
                $this->user->load('client');
            }
        } catch (Throwable $th) {

            Log::channel('google_login_errors')->error(
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

    private function storeUser()
    {

        if ($this->global_request_object->input("role") === "Super Admin") {
            try {
                $super_admin_count = User::where('role', 'Super Admin')
                    ->count();
            } catch (Throwable $th) {

                Log::channel('google_login_errors')->error(
                    "\n\n" .
                    "Description: Failed to get super admin's count from database.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: - .\n\n" .
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

            if ($super_admin_count > 0) {

                Log::channel('google_login_errors')->error(
                    "\n\n" .
                    "Description: A super admin account already exists.\n\n" .
                    "Error message: - .\n\n" .
                    "User ID: - .\n\n" .
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

        DB::transaction(function () {
            try {
                $this->user = User::create([
                    'email' => $this->google_response['email'],
                    'first_name' => $this->google_response['given_name'],
                    'last_name' => $this->google_response['family_name'],
                    'image_url' => $this->google_response['picture'] ? $this->google_response['picture'] : null,
                    'role' => $this->global_request_object->input("role"),
                    'is_active' => true,
                    'refresh_token' => null,
                    'last_login' => null,
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                if (!$this->user) {
                    throw new Exception(
                        "- .",
                        500
                    );
                }

            } catch (Throwable $th) {

                Log::channel('google_login_errors')->error(
                    "\n\n" .
                    "Description: Failed to store new user in users table.\n\n" .
                    "Error message: " . $th->getMessage() . "\n\n" .
                    "User ID: - .\n\n" .
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

            if ($this->user->role === "Super Admin") {
                try {
                    $stored_admin = Admin::create(
                        [
                            "user_id" => $this->user->id
                        ]
                    );

                    if (!$stored_admin) {
                        throw new Exception(
                            "- .",
                            500
                        );
                    }

                } catch (Throwable $th) {

                    Log::channel('google_login_errors')->error(
                        "\n\n" .
                        "Description: Failed to store new user in admins table.\n\n" .
                        "Error message: " . $th->getMessage() . "\n\n" .
                        "User ID: - .\n\n" .
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
            } else {
                try {
                    $stored_client = Client::create(
                        [
                            "user_id" => $this->user->id
                        ]
                    );

                    if (!$stored_client) {
                        throw new Exception(
                            "- .",
                            500
                        );
                    }

                } catch (Throwable $th) {

                    Log::channel('google_login_errors')->error(
                        "\n\n" .
                        "Description: Failed to store new user in clients table.\n\n" .
                        "Error message: " . $th->getMessage() . "\n\n" .
                        "User ID: - .\n\n" .
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
        });
    }

    private function checkUserValidity()
    {
        if (!$this->user->is_active) {

            Log::channel('google_login_errors')->error(
                "\n\n" .
                "Description: User tried to log in but his account has been suspended.\n\n" .
                "Error message: - .\n\n" .
                "User ID: " . $this->user->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            throw new Exception(
                "The logged in user has been suspended.",
                403
            );
        }
    }

    private function checkUserExistance()
    {
        try {
            $this->user = User::where(
                "email",
                $this->google_response["email"]
            )
                ->first();

        } catch (Throwable $th) {

            Log::channel('google_login_errors')->error(
                "\n\n" .
                "Description: Failed to get user from database.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "User Email: " . $this->google_response["email"] . "\n\n" .
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

        return $this->user;
    }

    private function validateIdToken()
    {
        try {
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $this->global_request_object->input("id_token"),
            ]);

            if ($response->failed()) {
                throw new Exception(
                    'Invalid Google Token.',
                    401
                );
            }

            $this->google_response = $response->json();

            if (
                $this->google_response['exp'] < time() ||
                $this->google_response['aud'] !== config('app.GOOGLE_CLIENT_ID')
            ) {
                throw new Exception(
                    'Invalid Google Token.',
                    401
                );
            }
        } catch (Throwable $th) {

            Log::channel('google_login_errors')->error(
                "\n\n" .
                "Description: Invalid Google Token.\n\n" .
                "Error message: " . $th->getMessage() . "\n\n" .
                "User ID: - .\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "File: " . __FILE__ . ". Line: " . __LINE__ . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );

            if ($th->getMessage() === 'Invalid Google Token.') {
                throw $th;
            } else {
                throw new Exception(
                    'Google Login failed. Please try again later.',
                    500
                );
            }
        }

    }

    private function logRequest()
    {
        try {
            Log::channel('google_login_requests')->info(
                "\n\n" .
                "Description: User logged in successfully via google login.\n\n" .
                "User ID: " . $this->user->id . "\n\n" .
                "Ip: " . $this->global_request_object->ip() . "\n\n" .
                "User Agent: " . $this->global_request_object->userAgent() . "\n\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n" .
                "----------------------------------------------------------------------------------------------------------------------------------\n\n"
            );
        } catch (Throwable $th) {
            //throw $th;
        }

    }

    public function __invoke(GoogleLoginRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->validateIdToken();

        $this->user = $this->checkUserExistance();

        if ($this->user) {
            $this->checkUserValidity();
        } else {
            $this->storeUser();
        }

        $this->eagerLoadUserRelations();

        $access_token = $this->prepareAccessToken();

        $refresh_token = $this->prepareRefreshToken();

        $this->loadIsAdminAvailableForBackorder();

        $this->logRequest();

        return response()->json([
            'message' => 'User logged in successfully!',
            'user' => new UserResource($this->user),
            'access_token' => $access_token,
        ], status: 200)->withCookie(
                cookie("refresh_token", $refresh_token, httpOnly: true, secure: false, minutes: 60 * 24 * 30)
            );
    }
}