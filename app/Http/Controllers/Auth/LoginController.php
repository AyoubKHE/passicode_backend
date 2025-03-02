<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserEmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Users\UserResource;

class LoginController extends Controller
{
    private LoginRequest $global_request_object;
    private User|null $user;
    private string $email_verification_token;


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

        $this->user->password_reset_token = null;
        $this->user->password_reset_token_sent_at = null;
        $this->user->refresh_token = Hash::make($refresh_token);
        $this->user->last_login = now();

        try {
            $is_updated = $this->user->save();
        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$is_updated) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
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


    private function prepareEmailVerificationToken(): void
    {
        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => $this->user->id,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $this->email_verification_token = $email_verification_token_object->getJwtToken();

        $this->user->email_verification_token = Hash::make($this->email_verification_token);

        $this->user->email_verification_token_sent_at = now();
    }
    private function addEmailVerificationTokenToUserRecord(): void
    {
        $this->prepareEmailVerificationToken();

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
    private function sendEmailVerificationLink(): void
    {
        try {
            $sentMessage = Mail::to($this->user->email)->send(
                new UserEmailVerification(
                    $this->user->first_name,
                    $this->email_verification_token,
                )
            );
        } catch (Throwable $throwable) {
            throw new Exception(
                'Unable to send the confirmation email. Please check the user email address and try again.',
                500
            );
        }

        if (!$sentMessage) {
            throw new Exception(
                'Unable to send the confirmation email. Please check the user email address and try again.',
                500
            );
        }
    }
    private function checkUserValidity()
    {
        if (!$this->user->is_active) {
            throw new Exception(
                "The logged in user has been suspended.",
                403
            );
        }
        if ($this->user->email_verification_token) {

            if ((new Carbon($this->user->email_verification_token_sent_at))->addMinutes(10) < now()) {

                DB::transaction(function () {

                    $this->addEmailVerificationTokenToUserRecord();

                    $this->sendEmailVerificationLink();
                });

            }
            throw new Exception(
                "The email address is not verified. a valid link has been sent to user email.",
                403
            );
        }
    }


    private function checkUserPassword()
    {
        if (
            !Hash::check(
                $this->global_request_object->input("password"),
                $this->user->password
            )
        ) {
            throw new Exception('Invalid email or password.', 401);
        }
    }


    private function loadUserFromDatabase()
    {
        try {
            $this->user = User::where(
                "email",
                $this->global_request_object->input("email")
            )
                ->first();

        } catch (Throwable $throwable) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->user) {
            throw new Exception('Invalid email or password.', 401);
        }
    }


    public function __invoke(LoginRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->loadUserFromDatabase();

        $this->checkUserPassword();

        $this->checkUserValidity();

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
