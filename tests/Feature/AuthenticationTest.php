<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Passport\Passport;

class AuthenticationTest extends TestCase
{
    /**
     * Email ve password girilmeme durumunu durumunu test eden method
     *
     * @return void
     */

    use WithoutMiddleware;
    
    public function test_must_enter_email_and_password()
    {
        $this->json('POST', 'api/user/login')
            ->assertStatus(422)
            ->assertJson([
                "error_code" => "validation_error",
                "success_message" => null,
                "error_message" => [
                    "email" => [
                        "The email field is required."
                    ],
                    "password" => [
                        "The password field is required."
                    ]
                ]
            ]);
    }

    public function test_unauthorised_user_login(){
        $user = factory(User::class)->create([
            'fullname' => 'Mehmet Çelik',
            'email' => 'sample@test.com',
            'password' => bcrypt('sample123'),
        ]);

        $loginData = ['email' => 'sample@test.com', 'password' => 'xyz456'];
        $this->json('POST', 'api/user/login', $loginData, ['Accept' => 'application/json'])
            ->assertStatus(404);
    } 

}
