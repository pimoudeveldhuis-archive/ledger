<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;

class SettingsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Testing updating settings
     *
     * @return void
     */
    public function testPasswordUpdate()
    {
        $user = factory(\App\Models\User::class)->create();
        
        $user->authentication()->create([
            'type' => 'password',
            'data' => Hash::make('secret'),
        ]);
        
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user);

            // Try updating the password with the wrong old password
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('pwupdate_password_old', 'wrong')
                    ->type('pwupdate_password_new', 'secret_new')
                    ->type('pwupdate_password_new_check', 'secret_new')
                    ->click('@pwupdate-button')
                    ->assertSee('Huidige wachtwoord is niet correct.');

            // Try updating the password with non identical new passwords
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('pwupdate_password_old', 'secret')
                    ->type('pwupdate_password_new', 'secret_new_wrong')
                    ->type('pwupdate_password_new_check', 'secret_new')
                    ->click('@pwupdate-button')
                    ->assertSee('Wachtwoorden komen niet overeen.');

            // Try a succesfull update of the password
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('pwupdate_password_old', 'secret')
                    ->type('pwupdate_password_new', 'secret_new')
                    ->type('pwupdate_password_new_check', 'secret_new')
                    ->click('@pwupdate-button')
                    ->assertSee('Wachtwoord gewijzigd.');

            // Logout and check whether the new password can be used to login again
            $browser->visit('/logout');
            $browser->visit('/')
                    ->assertSee('Inloggen')
                    ->type('email', $user->email)
                    ->type('password', 'secret_new')
                    ->click('@login-button')
                    ->assertDontSee('The credentials you entered are incorrect.');
        });
    }
}
