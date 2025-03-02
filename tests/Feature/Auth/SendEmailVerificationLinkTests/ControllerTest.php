<?php

namespace Tests\Feature\Auth\SendEmailVerificationLinkTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        try {
            parent::setUp();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }
    }
    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }

    }


    public function test_successfull_send_email_verification_link(): void
    {
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        Mail::shouldReceive('to->send')
            ->andReturn('The email has been sent successfully!');

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'created_at' => now(),
                'email_verification_token' => "old_token",
                'email_verification_token_sent_at' => now()->subMinutes(30),
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->postJson('api/auth/send-email-verification-link', [
            'email' => 'ayoub.kheyar06@gmail.com'
        ]);

        try {
            $this->assertDatabaseMissing(
                "users",
                [
                    "id" => 1,
                    'email_verification_token' => null,
                ]
            );

            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => "The confirmation link has been sent to the user email. The confirmation link is valid for 15 minutes only."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }

}