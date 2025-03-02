<?php

namespace App\Http\Controllers\Auth;

use Exception;

use Throwable;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Models\Clients\Client;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Mail\UserEmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\Users\UserResource;
use App\Http\Requests\Auth\RegisterRequest;

class RegisterController extends Controller
{
    private RegisterRequest $global_request_object;
    private array $prepared_user;
    private User|null $stored_user;
    private string $email_verification_token;

    private function sendEmailVerificationLink(): void
    {
        try {
            $sent_message = Mail::to($this->stored_user->email)->send(
                new UserEmailVerification(
                    $this->stored_user->first_name,
                    $this->email_verification_token,
                )
            );
        } catch (Throwable $throwable) {
            throw new Exception(
                'Unable to send the confirmation email. Please check the user email address and try again.',
                500
            );
        }

        if (!$sent_message) {
            throw new Exception(
                'Unable to send the confirmation email. Please check the user email address and try again.',
                500
            );
        }
    }

    private function eagerLoadRelations(): void
    {
        try {
            if ($this->stored_user->role === "Super Admin") {
                $this->stored_user->load('admin');
            } else if ($this->stored_user->role === "Client") {
                $this->stored_user->load('client');
            }
        } catch (Throwable $th) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function storeAdmin(): void
    {
        try {
            $stored_admin = Admin::create(
                [
                    "user_id" => $this->stored_user->id
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
    }

    private function storeClient(): void
    {
        try {
            $stored_client = Client::create(
                [
                    "user_id" => $this->stored_user->id
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

    private function storeUser(): void
    {
        try {
            $this->stored_user = User::create($this->prepared_user);

            if (!$this->stored_user) {
                throw new Exception();
            }

        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }
    }

    private function prepareEmailVerificationToken(): void
    {
        $table_status = DB::select("SHOW TABLE STATUS LIKE 'users'");
        $user_id = $table_status[0]->Auto_increment;

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => $user_id,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $this->email_verification_token = $email_verification_token_object->getJwtToken();

        $this->prepared_user['email_verification_token'] = Hash::make($this->email_verification_token);
    }


    private function preparingData(): void
    {
        $this->prepared_user['password'] = Hash::make($this->prepared_user['password']);

        $this->prepared_user['is_active'] = true;

        $this->prepareEmailVerificationToken();

        $this->prepared_user['email_verification_token_sent_at'] = now();
        
        $this->prepared_user['password_reset_token'] = null;

        $this->prepared_user['password_reset_token_sent_at'] = null;

        $this->prepared_user['refresh_token'] = null;

        $this->prepared_user['last_login'] = null;

        $this->prepared_user['created_at'] = now();

        $this->prepared_user['updated_at'] = null;
    }


    private function isSuperAdmin(): void
    {
        if ($this->prepared_user["role"] === "Super Admin") {
            try {
                $users_count = User::count();
            } catch (Throwable $throwable) {
                throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
            }

            if ($users_count > 0) {
                throw new Exception('Super Admin account already created.', 403);
            }
        }
    }


    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $this->global_request_object = $request;

        $this->prepared_user = $this->global_request_object->validated();

        $this->isSuperAdmin();

        $this->preparingData();

        DB::transaction(function () {

            $this->storeUser();

            if ($this->stored_user->role === "Super Admin") {
                $this->storeAdmin();
            } else {
                $this->storeClient();
            }

            $this->eagerLoadRelations();

            $this->sendEmailVerificationLink();
        });

        return response()->json([
            'message' => 'User account is created successfully. A confirmation email has been sent to the user. The confirmation link is valid for 15 minutes only.',
            'user' => new UserResource($this->stored_user),
        ], 201);
    }
}
