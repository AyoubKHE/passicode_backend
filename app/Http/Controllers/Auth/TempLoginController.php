<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\Request;
use Throwable;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Users\UserResource;

class TempLoginController extends Controller
{
    private User|null $user;

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

    private function loadUser()
    {
        // itsayoubkheyar06@gmail.com => super admin account
        // ayoub.kheyar06@gmail.com => client account

        try {
            $this->user = User::where(
                "email",
                "itsayoubkheyar06@gmail.com"
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

    public function __invoke(Request $request): JsonResponse
    {
        $this->user = $this->loadUser();

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
