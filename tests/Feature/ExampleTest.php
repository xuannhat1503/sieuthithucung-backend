<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_client_auth_routes_are_registered()
    {
        $this->assertTrue(Route::has('api.auth.register'));
        $this->assertTrue(Route::has('api.auth.login'));
        $this->assertTrue(Route::has('api.account.summary'));
    }
}
