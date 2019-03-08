<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreate()
    {
        $user = factory(\App\Models\User::class)->create([
            'name' => 'Pim Oude Veldhuis',
        ]);
        
        $this->assertDatabaseHas('users', ['name' => 'Pim Oude Veldhuis']);
    }

    public function testSoftDelete()
    {
        $user = factory(\App\Models\User::class)->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['name' => $user->name]);
    }
}