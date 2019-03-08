<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreate()
    {
        factory(\App\Models\Bank::class, 3)->create();

        $this->assertTrue(\App\Models\Bank::count() === 3);
    }
}