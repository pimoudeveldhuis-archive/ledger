<?php
/**
 * Install controller
 *
 * Handles the installing of the application
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

/**
 * Class InstallController
 */
class InstallController extends Controller
{
    /**
     * Displays the installer.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // If there is already a user, exit the installer
        if (\App\Models\User::count() > 0) {
            exit();
        }

        return View('install');
    }

    /**
     * Installs the application and supplies the database
     *     with the default data, like the bank and
     *     import configurations.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doInstall(Request $request)
    {
        // If there is already a user, exit the installer
        if (\App\Models\User::count() > 0) {
            exit();
        }

        // Validate the form
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'password_check' => 'required',
            'account' => 'required',
            'account_name' => 'required',
        ]);

        // Making sure that the passwords are equal
        if ($request->input('password') !== $request->input('password_check')) {
            return redirect()->route('install')
                ->withErrors(['password_check' => 'De wachtwoorden komen niet overeen.'])
                ->withInput();
        }

        // Create the banks
        $banks = json_decode(
            @file_get_contents('https://innospan.github.io/ledger-data/banks.txt'),
            true
        );
        
        if ($banks === null || !is_array($banks)) {
            return redirect()->route('install')
                ->with(['_alert' => [
                    'type' => 'danger',
                    'msg' => 'De bank configuraties konden niet worden geladen. Controleer uw internetverbinding.'
                ]])
                ->withInput();
        }

        foreach ($banks as $bank) {
            try {
                \App\Models\Bank::updateOrCreate([
                    'country' => $bank['country'],
                    'bankcode' => $bank['bankcode'],
                ], [
                    'name' => $bank['name'],
                    'bic' => $bank['bic'],
                ]);
            } catch (PDOException $e) {
            }
        }

        // Create the importconfigurations
        $importconfigurations = json_decode(
            @file_get_contents('https://innospan.github.io/ledger-data/importconfigurations.txt'),
            true
        );
        
        if ($importconfigurations === null || !is_array($importconfigurations)) {
            return redirect()->route('install')
                ->with(['_alert' => [
                    'type' => 'danger',
                    'msg' => 'De importconfiguraties konden niet worden geladen. Controleer uw internetverbinding.'
                ]])
                ->withInput();
        }

        foreach ($importconfigurations as $importconfiguration) {
            try {
                \App\Models\ImportConfiguration::updateOrCreate([
                    'name' => $importconfiguration['name'],
                ], [
                    'data' => $importconfiguration['data'],
                ]);
            } catch (PDOException $e) {
            }
        }

        // Check whether the account number actually belongs to a bank
        $found_bank_by_account = null;
        foreach (\App\Models\Bank::get() as $bank) {
            if (preg_match('/^'.$bank->country.'[0-9]{2}'.$bank->bankcode.'[A-Z0-9]+$/', $request->input('account'))) {
                $found_bank_by_account = $bank;
                break;
            }
        }

        if ($found_bank_by_account === null) {
            return redirect()->route('install')
                ->withErrors(['account' => 'Geen bank bij IBAN nummer gevonden.'])
                ->withInput();
        }

        // Create the currencies, this might be retrieved from some API in the future
        $currencies = [
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro', 'decimals' => 2],
        ];

        foreach ($currencies as $currency) {
            try {
                \App\Models\Currency::updateOrCreate([
                    'code' => $currency['code'],
                ], [
                    'symbol' => $currency['symbol'],
                    'name' => $currency['name'],
                    'decimals' => $currency['decimals'],
                ]);
            } catch (PDOException $e) {
            }
        }

        // Create the rule keys
        \Conf::set('rules', [
            'account_match',
            'account_match_blocking',
            'account_not_match_blocking',
            'contra_account_match',
            'contra_account_not_match_blocking',
            'contra_account_name_match',
            'contra_account_name_contains',
            'contra_account_name_not_contains_blocking',
            'amount_smaller_blocking',
            'amount_bigger_blocking',
            'type_match',
            'type_no_match_blocking',
            'dw_match',
            'dw_not_match_blocking',
            'currency_match_blocking',
            'currency_not_match_blocking',
            'description_contains',
            'description_not_contains_blocking',
            'reference_match',
            'reference_not_match_blocking',
        ]);

        // Create the fa-icons config
        \Conf::set('fa-icons', [
            'home',
            'cutlery',
            'birthday-cake',
            'money',
            'credit-card',
            'industry',
        ]);

        // Set the current version
        \Conf::set('version', '0.1.0');

        // Set whether the applications should automatically check for updates
        \Conf::set('check_for_updates', true);

        // Create the user public/secret keys and the recovery key
        $recoverykey = \EncryptionHelper::randomString(8).'::'.
                       \EncryptionHelper::randomString(4).'::'.
                       \EncryptionHelper::randomString(4).'::'.
                       \EncryptionHelper::randomString(8);
        
        $keypair = \EncryptionHelper::generateKeyPair();

        // Create the user
        $user = new \App\Models\User([
            'publickey' => $keypair['public'],
        ]);

        $user->fill([
            'name' => $request->input('name'),
            
            'email' => $request->input('email'),
            'email_hash' => hash('sha256', $request->input('email')),
            
            'twofactor' => null,
            'language' => 'nl',
            
            'oldest_transaction' => null,
            'newest_transaction' => null,

            'secretkey' => \EncryptionHelper::encrypt($request->input('password'), $keypair['secret']),
            'recoverykey' => \EncryptionHelper::encrypt($recoverykey, $keypair['secret']),
        ])->save();
        
        // Create the user authentication
        $user->authentication()->create([
            'type' => 'password',
            'data' => Hash::make($request->input('password')),
        ]);

        // Create the users first bank account
        $account = new \App\Models\User\Account([
            'user_id' => $user->id,
        ]);

        $account->fill([
            'bank_id' => $found_bank_by_account->id,
            'account' => $request->input('account'),
            'account_hash' => hash('sha256', $request->input('account')),
            'name' => $request->input('account_name'),
            'description' => $request->input('account_description'),
        ])->save();

        // Forget the install needed cache, so the user will not be redirect to the installer again
        Cache::forget('install-needed');

        // Return to the login page
        return redirect()->route('login')->with([
            '_alert' => [
                'type' => 'success',
                'msg' => 'Installatie afgerond, u kunt hieronder inloggen. De recovery key 
                    voor uw installatie is: '. $recoverykey .'. Sla deze veilig op, want 
                    wanneer u uw wachtwoord kwijt raakt is dit de enige manier om uw data 
                    terug te halen.'
            ]
        ]);
    }
}
