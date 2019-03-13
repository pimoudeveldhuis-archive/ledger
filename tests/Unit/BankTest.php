<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BankTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Testing instance creation.
     *
     * @return void
     */
    public function testCreate()
    {
        factory(\App\Models\Bank::class, 3)->create();
        $this->assertTrue(\App\Models\Bank::count() === 3);
    }

    /**
     * Testing getting and setting the attributes
     * 
     * @return void
     */
    public function testAttributes()
    {
        $bank = factory(\App\Models\Bank::class)->create([
            'name' => 'Testbank',
            'bic' => 'BIC',
            'country' => 'ZZ',
            'bankcode' => 'TEST',
        ]);

        $this->assertTrue($bank->name === 'Testbank');
        $this->assertTrue($bank->bic === 'BIC');
        $this->assertTrue($bank->country === 'ZZ');
        $this->assertTrue($bank->bankcode === 'TEST');
    }
}