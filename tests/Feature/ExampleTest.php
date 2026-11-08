<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/mahasiswa');
    }

    public function test_authentication_pages_are_available(): void
    {
        foreach (['/admin/login', '/mahasiswa/login', '/mahasiswa/daftar'] as $path) {
            $this->get($path)->assertOk();
        }
    }
}
