<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Testing the login form with an incorrect email address,
     *     an incorrect password an with correct credentials.
     *
     * @return void
     */
    public function testLogin()
    {
        // Create the user public/secret keys and the recovery key
        $keypair = \EncryptionHelper::generateKeyPair();

        $user = factory(\App\Models\User::class)->create([
            'publickey' => $keypair['public'],
            'name' => 'Pim Oude Veldhuis',
            'email' => 'pim@odvh.nl',
            'email_hash' => hash('sha256', 'pim@odvh.nl'),
            'secretkey' => \EncryptionHelper::encrypt('secret', $keypair['secret']),
        ]);

        $user->authentication()->create([
            'type' => 'password',
            'data' => Hash::make('secret'),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Inloggen');

            $browser->visit('/')
                    ->type('email', 'pimm@odvh.nl')
                    ->type('password', 'secret')
                    ->click('@login-button')
                    ->assertSee('The credentials you entered are incorrect.');

            $browser->visit('/')
                    ->type('email', 'pim@odvh.nl')
                    ->type('password', 'wrong')
                    ->click('@login-button')
                    ->assertSee('The credentials you entered are incorrect.');

            $browser->visit('/')
                    ->type('email', 'pim@odvh.nl')
                    ->type('password', 'secret')
                    ->click('@login-button')
                    ->assertDontSee('The credentials you entered are incorrect.');
        });
    }
}
