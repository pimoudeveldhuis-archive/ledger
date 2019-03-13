<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAccountTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Testing instance creation.
     *
     * @return void
     */
    public function testCreate()
    {
        $user = factory(\App\Models\User::class)->create();
        $account = factory(\App\Models\User\Account::class)->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue(\App\Models\User\Account::where('id', $account->id)->value('user_id') === $user->id);
    }
}