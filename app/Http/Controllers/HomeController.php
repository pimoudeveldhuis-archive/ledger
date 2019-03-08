<?php
/**
 * Home controller
 *
 * Handles the dashboard frontpage.
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class HomeController
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Displays the user dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Return the home view with a boolean that is true if the user does not have imports for last month
        return View('home', [
            'import_alert' => (
                \Carbon\Carbon::now()->subMonth()->startOfMonth() > Auth::user()->latest_transaction
            ) ? true : false,
        ]);
    }
}
