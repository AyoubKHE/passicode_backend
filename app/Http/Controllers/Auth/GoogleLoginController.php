<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Clients\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
                $users_count = User::where('role', 'Super Admin')
                    ->count();
            } catch (Throwable $throwable) {
                throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
            }

            if ($users_count > 0) {
                throw new Exception('Unexpected error.', 500);
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
                    throw new Exception();
                }

            } catch (Throwable $throwable) {
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
                        throw new Exception();
                    }

                } catch (Throwable $throwable) {
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
                        throw new Exception();
                    }

                } catch (Throwable $throwable) {
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

        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        return $this->user;
    }

    private function validateIdToken()
    {
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $this->global_request_object->input("id_token"),
        ]);

        if ($response->failed()) {
            throw new Exception('Invalid Google Token.', 401);
        }

        $this->google_response = $response->json();

        if (
            $this->google_response['exp'] < time() ||
            $this->google_response['aud'] !== config('app.GOOGLE_CLIENT_ID')
        ) {
            throw new Exception('Invalid Google Token.', 401);
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

        return response()->json([
            'message' => 'User logged in successfully!',
            'user' => new UserResource($this->user),
            'access_token' => $access_token,
        ], status: 200)->withCookie(
                cookie("refresh_token", $refresh_token, httpOnly: true, secure: true, minutes: 60 * 24 * 30)
            );
    }
}

// {
//   "iss": "https://accounts.google.com",
//   "azp": "388644099266-3gdatqgc15bgiv5794vbtudkbevpt5ho.apps.googleusercontent.com",
//   "aud": "388644099266-3gdatqgc15bgiv5794vbtudkbevpt5ho.apps.googleusercontent.com",
//   "sub": "118205228411259759598",
//   "email": "ayoub.kheyar06@gmail.com",
//   "email_verified": "true",
//   "nbf": "1747576413",
//   "name": "Ayoub Kheyar",
//   "picture": "https://lh3.googleusercontent.com/a/ACg8ocKL1cZrAafoxBI-br3KPFNnOzL5K0tjo4YGf6JrLLTtblyr=s96-c",
//   "given_name": "Ayoub",
//   "family_name": "Kheyar",
//   "iat": "1747576713",
//   "exp": "1747580313",
//   "jti": "10d52d7d8d1a888c630d1d1c5f8f0eec04dac2cb",
//   "alg": "RS256",
//   "kid": "660ef3b9784bdf56ebe859f577f7fb2e8c1ceffb",
//   "typ": "JWT"
// }
