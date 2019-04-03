<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;

class SettingsTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function tearDown()
    {
        foreach (static::$browsers as $browser) {
            $browser->driver->manage()->deleteAllCookies();
        }

        parent::tearDown();
    }

    /**
     * Testing updating the email address
     * 
     * @return void
     */
    public function testEmailUpdate()
    {
        $user = factory(\App\Models\User::class)->create();
        
        $user->authentication()->create([
            'type' => 'password',
            'data' => Hash::make('secret'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user);

            // Try updating the email with an invalid email address
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('emupdate_email', 'wrong')
                    ->type('emupdate_password', 'secret')
                    ->click('@emupdate-button')
                    ->assertDontSee('Email adres gewijzigd in');

            // Try updating the password with the wrong password
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('emupdate_email', 'new_email@example.com')
                    ->type('emupdate_password', 'wrong')
                    ->click('@emupdate-button')
                    ->assertSee('Huidige wachtwoord is niet correct.');

            // Try a succesfull update of the password
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('emupdate_email', 'new_email@example.com')
                    ->type('emupdate_password', 'secret')
                    ->click('@emupdate-button')
                    ->assertSee('Email adres gewijzigd in');

            // Logout and check whether the new password can be used to login again
            $browser->visit('/logout');
            $browser->visit('/')
                    ->assertSee('Inloggen')
                    ->type('email', 'new_email@example.com')
                    ->type('password', 'secret')
                    ->click('@login-button')
                    ->assertDontSee('The credentials you entered are incorrect.');
        });
    }

    /**
     * Testing updating password
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

    /**
     * Test resetting the password via the recovery key
     *
     * @return void
     */
    public function testPasswordReset()
    {
        // Create the user public/secret keys and the recovery key
        $keypair = \EncryptionHelper::generateKeyPair();

        // Manually create a recovery key so it can be used during the test
        $recoverykey = \EncryptionHelper::randomString(8).'::'.
                       \EncryptionHelper::randomString(4).'::'.
                       \EncryptionHelper::randomString(4).'::'.
                       \EncryptionHelper::randomString(8);

        $user = factory(\App\Models\User::class)->create([
            'publickey' => $keypair['public'],
            'secretkey' => \EncryptionHelper::encrypt('secret', $keypair['secret']),
            'recoverykey' => \EncryptionHelper::encrypt($recoverykey, $keypair['secret']),
        ]);

        $user->authentication()->create([
            'type' => 'password',
            'data' => Hash::make('secret'),
        ]);

        $this->browse(function (Browser $browser) use ($user, $recoverykey) {
            $browser->loginAs($user);

            // Try updating the password with the wrong old password
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('pwreset_recovery', 'wrong')
                    ->type('pwreset_password_new', 'secret_new')
                    ->type('pwreset_password_new_check', 'secret_new')
                    ->click('@pwreset-button')
                    ->assertSee('De herstelcode is niet correct.');

            // Try updating the password with non identical new passwords
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('pwreset_recovery', $recoverykey)
                    ->type('pwreset_password_new', 'secret_new')
                    ->type('pwreset_password_new_check', 'secret_wrong')
                    ->click('@pwreset-button')
                    ->assertSee('Wachtwoorden komen niet overeen.');

            // Try a succesfull update of the password
            $browser->visit('/settings')
                    ->assertSee('Instellingen')
                    ->type('pwreset_recovery', $recoverykey)
                    ->type('pwreset_password_new', 'secret_new')
                    ->type('pwreset_password_new_check', 'secret_new')
                    ->click('@pwreset-button')
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

    /**
     * Test resetting the recovery key
     * 
     * @return void
     */
    public function testRecoveryReset()
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
        
        $this->browse(function (Browser $browser) use ($user, $keypair) {
            $browser->visit('/')
                ->type('email', 'pim@odvh.nl')
                ->type('password', 'secret')
                ->click('@login-button')
                ->assertDontSee('The credentials you entered are incorrect.');

            // Try resetting the recovery key with a wrong password
            $browser->visit('/settings')
                ->assertSee('Instellingen')
                ->type('recreset_password', 'wrong')
                ->click('@recreset-button')
                ->assertSee('Huidige wachtwoord is niet correct.');

            // Try a succesfull recovery key reset
            $browser->visit('/settings')
                ->assertSee('Instellingen')
                ->type('recreset_password', 'secret')
                ->click('@recreset-button')
                ->assertSee('Nieuwe herstelcode:');
        });
    }
}
