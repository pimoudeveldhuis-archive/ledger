<?php
/**
 * Login controller
 *
 * Displays the login form and handles the logging in and out.
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
 * Class LoginController
 */
class LoginController extends Controller
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
        // Logout
        Auth::logout();

        // Redirect to login
        return redirect()->route('login');
    }
}
