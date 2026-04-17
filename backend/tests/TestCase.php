<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    /**
     * Make a JSON request scoped to the given org subdomain.
     * Equivalent to calling {subdomain}.localhost as the host.
     */
    protected function orgJson(string $method, string $path, array $data = [], string $subdomain = 'river'): \Illuminate\Testing\TestResponse
    {
        return $this->withServerVariables(['HTTP_HOST' => "{$subdomain}.localhost"])
                    ->{$method . 'Json'}('/api' . $path, $data);
    }
}
