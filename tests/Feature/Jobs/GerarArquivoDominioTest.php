<?php

namespace Tests\Feature\Jobs;

use Tests\TestCase;

class GerarArquivoDominioTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
