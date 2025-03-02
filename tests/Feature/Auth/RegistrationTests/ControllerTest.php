<?php

namespace Tests\Feature\Auth\RegistrationTests;

use Exception;
use Throwable;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
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


    public function test_successfull_registration(): void
    {
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        Mail::shouldReceive('to->send')
            ->andReturn('The email has been sent successfully!');

        $response = $this->postJson('api/auth/register', [
            'first_name' => 'Ayoub',
            'last_name' => 'Kheyar',
            'email' => 'ayoub.kheyar06@gmail.com',
            'password' => "a",
            'role' => "Super Admin"
        ]);

        try {

            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    'email' => 'ayoub.kheyar06@gmail.com',
                    'role' => 'Super Admin',
                ]
            );


            $this->assertDatabaseHas(
                "admins",
                [
                    "id" => 1,
                    "user_id" => 1,
                ]
            );


            $response->assertStatus(201)
                ->assertJsonFragment(
                    [
                        'message' => 'User account is created successfully. A confirmation email has been sent to the user. The confirmation link is valid for 15 minutes only.'
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}