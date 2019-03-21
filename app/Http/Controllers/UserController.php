<?php
/**
 * User controller
 *
 * Displays the login form, handles the logging in and out and the user recovery.
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
 * Class UserController
 */
class UserController extends Controller
{
    /**
     * Checks (and caches) whether the installer is needed, if so then redirect
     *     and if not then show the login screen.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check whether there are accounts or not, if not the installer
        //     should be called. This can be cached for a long long time.
        $install_needed = Cache::remember('install-needed', 44640, function () {
            return ((\App\Models\User::count() > 0) ? false : true);
        });

        // If the install needed check comes out positive redirect the user to the login
        if ($install_needed === true) {
            return redirect()->route('install');
        }

        return View('login');
    }

    /**
     * After the user has submitted their email address this login handler will
     *     find their primary authentication method en redirect.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doLogin(Request $request)
    {
        // Lookup user by email address
        $user = \UserHelper::find($request->input('email'));
        if ($user !== null) {
            if ($user !== null
                && $user->authentication->type === 'password'
                && $user->authentication->data !== null
                && Hash::check($request->input('password'), $user->authentication->data)
            ) {
                // Store the users secret key in the session
                session(['secretkey' => \EncryptionHelper::decrypt($request->input('password'), $user->secretkey)]);

                // Authenticate the user
                Auth::login($user);

                // Redirect the user to the dashboard
                return redirect()->intended('/');
            }
        }

        // Emailaddress not found or password incorrect, return to the email address form
        return redirect()->route('login')
            ->withErrors(['account' => 'The credentials you entered are incorrect.'])
            ->withInput();
    }

    /**
     * Logs out the user and redirects it back to the login page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doLogout()
    {
        // Ensure user is logged in
        if (Auth::id() === null) {
            abort(403);
        }
    
        // Logout
        Auth::logout();

        // Redirect to login
        return redirect()->route('login');
    }
    
    /**
     * Displays the recovery view
     *
     * @return \Illuminate\View\View
     */
    public function recovery()
    {
        return View('recovery');
    }

    /**
     * Authenticates the user via the recovery key. If the recovery key
     *     is not correct it will redirect the user back with an error.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doRecovery(Request $request)
    {
        // Lookup user by email address
        $user = \UserHelper::find($request->input('email'));
        if ($user !== null) {
            // Retrieve the secret key using the recovery key instead of the password
            $secretkey = \EncryptionHelper::decrypt($request->input('recovery'), $user->recoverykey);

            if ($secretkey === false) {
                return redirect()->route('recovery')
                    ->withErrors(['account' => 'De herstelcode is niet correct.'])
                    ->withInput();
            }

            // Store the users secret key in the session
            session(['secretkey' => $secretkey]);

            // Authenticate the user
            Auth::login($user);

            // Redirect the user to the dashboard
            return redirect()->intended('/');
        }
    }

    /**
     * Displays the settings view
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        // Ensure user is logged in
        if (Auth::id() === null) {
            abort(403);
        }

        return View('settings');
    }

    /**
     * Validate and save setting updates.
     *
     * @param Request $request
     * @param string $key
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doSettings(Request $request, $key)
    {
        // Ensure user is logged in
        if (Auth::id() === null) {
            abort(403);
        }

        if ($key === 'email') {
            // Update the email address
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            // Before updating the email address, make sure the password is correct
            if (Hash::check($request->input('password'), Auth::user()->authentication->data)) {
                // Update the user email
                Auth::user()->fill([
                    'email' => $request->input('email')
                ])->save();

                // Redirect back with a success msg
                return redirect()->route('settings')->with([
                    '_alert' => [
                        'type' => 'success',
                        'msg' => 'Email adres gewijzigd in '. $request->input('email') .'.'
                    ]
                ]);
            } else {
                // Return with a error, as the password wasn't correct
                return redirect()->route('settings')
                    ->withErrors(['password' => 'Huidige wachtwoord is niet correct.'])
                    ->withInput();
            }
        } elseif ($key === 'password') {
            // Update the password
            $this->validate($request, [
                'password_old' => 'required',
                'password_new' => 'required',
                'password_new_check' => 'required',
            ]);

            if (!Hash::check($request->input('password_old'), Auth::user()->authentication->data)) {
                // The old password is not correct, redirect with an error msg
                return redirect()->route('settings')
                    ->withErrors(['password_old' => 'Huidige wachtwoord is niet correct.'])
                    ->withInput();
            } elseif ($request->input('password_new') !== $request->input('password_new_check')) {
                // The new password and the check are not equal, redirect with an error msg
                return redirect()->route('settings')
                    ->withErrors([
                        'password_new' => 'Wachtwoorden komen niet overeen.',
                        'password_new_check' => 'Wachtwoorden komen niet overeen.',
                    ])
                    ->withInput();
            } else {
                // Everything is correct, update the user password
                Auth::user()->authentication->fill([
                    'data' => Hash::make($request->input('password_new'))
                ])->save();

                // Redirect back with the success msg
                return redirect()->route('settings')->with([
                    '_alert' => [
                        'type' => 'success',
                        'msg' => 'Wachtwoord gewijzigd.'
                    ]
                ]);
            }
        } elseif ($key === 'recovery_reset') {
            // Using the password to authenticate, create a new recovery key
            $this->validate($request, [
                'password' => 'required',
            ]);
    
            // Check whether the current password is correct and whether there is a secretkey in session currently
            if (Hash::check($request->input('password'), Auth::user()->authentication->data) && session('secretkey') !== null) {
                // Create a new recovery key
                $recoverykey = \EncryptionHelper::randomString(8).'::'.
                               \EncryptionHelper::randomString(4).'::'.
                               \EncryptionHelper::randomString(4).'::'.
                               \EncryptionHelper::randomString(8);

                // Encrypt and save secretkey with the new recovery key
                Auth::user()->fill([
                    'recoverykey' => \EncryptionHelper::encrypt($recoverykey, session('secretkey')),
                ])->save();

                // Redirect back and show the new recovery key in the alert msg
                return redirect()->route('settings')->with([
                    '_alert' => [
                        'type' => 'success',
                        'msg' => 'Nieuwe herstelcode: '.$recoverykey,
                    ]
                ]);
            } else {
                // The current password is not correct
                return redirect()->route('settings')
                    ->withErrors(['password' => 'Huidige wachtwoord is niet correct.'])
                    ->withInput();
            }
        } elseif ($key === 'password_reset') {
            $this->validate($request, [
                'recovery' => 'required',
                'password_new' => 'required',
                'password_new_check' => 'required',
            ]);

            //Using the recovery key to authenticate to reset the password
            $secretkey = \EncryptionHelper::decrypt($request->input('recovery'), Auth::user()->recoverykey);
            if($secretkey === false) {
                // The secret key is not valid, return with an error msg
                return redirect()->route('settings')
                    ->withErrors([
                        'recovery' => 'De herstelcode is niet correct.',
                    ])
                    ->withInput();
            } elseif ($request->input('password_new') !== $request->input('password_new_check')) {
                // The new password and the check are not equal, return with an error msg
                return redirect()->route('settings')
                    ->withErrors([
                        'password_new' => 'Wachtwoorden komen niet overeen.',
                        'password_new_check' => 'Wachtwoorden komen niet overeen.',
                    ])
                    ->withInput();
            } else {
                // No errors, hash and store the new password
                Auth::user()->authentication->fill([
                    'data' => Hash::make($request->input('password_new'))
                ])->save();

                // Return with success msg
                return redirect()->route('settings')->with([
                    '_alert' => [
                        'type' => 'success',
                        'msg' => 'Wachtwoord gewijzigd.'
                    ]
                ]);
            }
        }

        // No action found ($key not correct or empty), return to settings
        return redirect()->route('settings');
    }
}
