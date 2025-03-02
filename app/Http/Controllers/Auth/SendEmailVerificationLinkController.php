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
use App\Http\Requests\Auth\sendEmailVerificationLinkRequest;

class SendEmailVerificationLinkController extends Controller
{

    private sendEmailVerificationLinkRequest $global_request_object;
    private User|null $user;
    private string $email_verification_token;


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

        if (!$this->user->email_verification_token) {
            throw new Exception('The user email already verified.', 400);
        }

        if ((new Carbon($this->user->email_verification_token_sent_at))->addMinutes(10) >= now()) {
            throw new Exception(
                "A valid link has already been sent to user email.",
                403
            );
        }
    }


    public function __invoke(sendEmailVerificationLinkRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->loadUserFromDatabase();

        DB::transaction(function () {

            $this->addEmailVerificationTokenToUserRecord();

            $this->sendEmailVerificationLink();
        });

        return response()->json([
            'message' => 'The confirmation link has been sent to the user email. The confirmation link is valid for 15 minutes only.'
        ], status: 200);
    }
}
