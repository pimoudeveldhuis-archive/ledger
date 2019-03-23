<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SettingsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Testing updating settings
     *
     * @return void
     */
    public function testEmailUpdate()
    {
        $user = factory(\App\Models\User::class)->create();
        
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/settings')
                    ->assertSee('Instellingen');
        });
    }
}
