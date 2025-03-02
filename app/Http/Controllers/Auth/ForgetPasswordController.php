<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserPasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\ForgetPasswordRequest;

class ForgetPasswordController extends Controller
{

    private ForgetPasswordRequest $global_request_object;
    private User|null $user;
    private string $password_reset_token;


    private function preparePasswordResetToken(): void
    {
        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => $this->user->id,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $this->password_reset_token = $password_reset_token_object->getJwtToken();

        $this->user->password_reset_token = Hash::make($this->password_reset_token);

        $this->user->password_reset_token_sent_at = now();
    }

    private function addPasswordResetTokenToUserRecord(): void
    {
        $this->preparePasswordResetToken();

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

    private function sendPasswordResetLink(): void
    {
        try {
            $sentMessage = Mail::to($this->user->email)->send(
                new UserPasswordReset(
                    $this->user->first_name,
                    $this->password_reset_token,
                )
            );
        } catch (Throwable $throwable) {
            throw new Exception(
                'Unable to send the password reset link. Please check the user email address and try again.',
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

    private function loadUserFromDatabase()
    {
        try {
            $this->user = User::where(
                "email",
                $this->global_request_object->input("email")
            )->first();

        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$this->user) {
            throw new Exception('User not found', 404);
        }

        if ($this->user->password_reset_token_sent_at) {
            if ((new Carbon($this->user->password_reset_token_sent_at))->addMinutes(10) >= now()) {
                throw new Exception(
                    "A valid link has already been sent to user email.",
                    403
                );
            }
        }
    }


    public function __invoke(ForgetPasswordRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->loadUserFromDatabase();

        DB::transaction(function () {

            $this->addPasswordResetTokenToUserRecord();

            $this->sendPasswordResetLink();
        });

        return response()->json([
            'message' => 'The password reset link has been sent to the user email. The link is valid for 15 minutes only.'
        ], status: 200);
    }
}
