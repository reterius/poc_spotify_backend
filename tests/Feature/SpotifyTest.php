<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Passport\Passport;

class SpotifyTest extends TestCase
{
    /**
     * Email ve password girilmeme durumunu durumunu test eden method
     *
     * @return void
     */

    use WithoutMiddleware;
    
    public function test_invalid_per_page_value()
    {
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=11']
        );

        
        $this->json('GET', 'api/spotify?per_page=11', [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'error_code' => 'per_page_validation_error'
            ]);
        #$this->assertAuthenticated();
    } 

    public function test_invalid_page_value()
    {
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=10&page=dfsdfds']
        );

        $this->json('GET', 'api/spotify?per_page=10&page=dfsdfds', [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'error_code' => 'page_validation_error'
            ]);
    } 

    public function test_spotify_api_limitation_error()
    {
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=10&page=41']
        );

        $this->json('GET', 'api/spotify?per_page=50&page=41', [], ['Accept' => 'application/json'])
            ->assertStatus(429)
            ->assertJsonFragment([
                'error_code' => 'spotify_api_limitation_error'
            ]);
    } 

    public function test_keyword_empty()
    {
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=10&page=2&keyword=']
        );

        $this->json('GET', 'api/spotify?per_page=10&page=2&keyword=', [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'error_code' => 'keyword_validation_error'
            ]);
    } 

    public function test_keyword_short(){
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=10&page=2&keyword=']
        );

        $this->json('GET', 'api/spotify?per_page=10&page=2&keyword=ab', [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'error_code' => 'keyword_validation_error'
            ]);
    } 

    


    public function test_type_invalid(){
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=10&page=2&keyword=michael&type=test']
        );

        $this->json('GET', 'api/spotify?per_page=10&page=2&keyword=michael&type=test', [], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'error_code' => 'type_invalid_validation_error'
            ]);
    } 


    public function test_type_valid(){
        Passport::actingAs(
            factory(User::class)->create(),
            ['api/spotify?per_page=10&page=2&keyword=michael&type=artist,playlist,track,album']
        );

        $this->json('GET', 'api/spotify?per_page=10&page=2&keyword=michael&type=artist,playlist,track,album', [], ['Accept' => 'application/json'])
            ->assertStatus(200) ;
    } 

}
