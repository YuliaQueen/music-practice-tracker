<?php

namespace Tests\Feature\Auth;

use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_display_registration_form()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Auth/Register')
        );
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /** @test */
    public function it_validates_name_is_required()
    {
        $userData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_validates_email_is_required()
    {
        $userData = [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_email_is_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_password_is_required()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_validates_password_confirmation_matches()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_validates_password_minimum_length()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_redirects_authenticated_users_away_from_registration()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }
}