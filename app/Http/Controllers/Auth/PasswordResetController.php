<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Exception;
use Throwable;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\ResetPasswordRequest;

class PasswordResetController extends Controller
{

    private ResetPasswordRequest $global_request_object;
    private User|null $user;

    private function updateUserPassword()
    {
        $this->user->password = Hash::make(
            $this->global_request_object->input("new_password")
        );
        $this->user->password_reset_token = null;
        $this->user->password_reset_token_sent_at = null;
        $this->user->updated_at = now();

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

    private function passwordResetTokenValidation()
    {
        try {
            $password_reset_token_payload = JWTService::checkTokenValidity(
                $this->global_request_object->input("password_reset_token")
            );

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {
                throw new Exception("The password reset token has expired.", 400);

            } else {
                throw new Exception("The password reset token is invalid.", 400);
            }
        }

        try {
            $this->user = User::where(
                "id",
                $password_reset_token_payload["user_data"]->user_id
            )->first();

        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->user) {
            throw new Exception(
                'User not found',
                404
            );
        }

        if (!$this->user->password_reset_token) {
            throw new Exception(
                'The user has not requested a token to reset the password.',
                400
            );
        }

        if (
            !Hash::check(
                $this->global_request_object->input("password_reset_token"),
                $this->user->password_reset_token
            )
        ) {
            throw new Exception("The password reset token is invalid.", 400);
        }

    }

    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->passwordResetTokenValidation();

        $this->updateUserPassword();

        return response()->json([
            'message' => "User's password has been reset successfully!"
        ], 200);
    }
}
