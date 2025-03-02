<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Exception;
use Throwable;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{

    private User|null $user;
    private string $email_verification_token;

    private function updateUserRecordInDatabase()
    {
        $this->user->email_verification_token = null;

        $this->user->email_verification_token_sent_at = null;

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

    private function emailVerificationTokenValidation()
    {
        try {
            $email_verification_token_payload = JWTService::checkTokenValidity($this->email_verification_token);

        } catch (Exception $e) {
            if (get_class($e) === "Firebase\JWT\ExpiredException") {
                throw new Exception("The verification token has expired.", 400);

            } else {
                throw new Exception("The verification token is invalid.", 400);
            }
        }

        try {
            $this->user = User::where(
                "id",
                $email_verification_token_payload["user_data"]->user_id
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

        if (!$this->user->email_verification_token) {
            throw new Exception(
                'The user email already verified.',
                400
            );
        }


        if (!Hash::check($this->email_verification_token, $this->user->email_verification_token)) {
            throw new Exception(
                "The verification token is invalid.",
                400
            );
        }

    }

    public function __invoke(string $email_verification_token): JsonResponse
    {
        $this->email_verification_token = $email_verification_token;

        $this->emailVerificationTokenValidation();

        $this->updateUserRecordInDatabase();

        return response()->json([
            'message' => "User's email has been successfully verified!"
        ], 200);
    }
}
