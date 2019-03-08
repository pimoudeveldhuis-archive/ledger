<?php
/**
 * Account controller
 *
 * Handles the account CRUD.
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AccountController
 */
class AccountController extends Controller
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
     * Displays a list of accounts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return View('account');
    }

    /**
     * Displays the account creation form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return View('account-form');
    }

    /**
     * Create a new account.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doCreate(Request $request)
    {
        // Validate the form
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'account' => 'required',
        ]);

        // Check whether the account number actually belongs to a bank
        $found_bank_by_account = null;
        foreach (\App\Models\Bank::get() as $bank) {
            if (preg_match('/^'.$bank->country.'[0-9]{2}'.$bank->bankcode.'[A-Z0-9]+$/', $request->input('account'))) {
                $found_bank_by_account = $bank;
                break;
            }
        }

        // If no bank is found return to the form with an error
        if ($found_bank_by_account === null) {
            return redirect()->route('install')
                ->withErrors(['account' => 'Geen bank bij IBAN nummer gevonden.'])
                ->withInput();
        }

        // No errors, create the account
        Auth::user()->accounts()->create([
            'bank_id' => $found_bank_by_account->id,
            'account' => $request->input('account'),
            'account_hash' => hash('sha256', $request->input('account')),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        // Redirect the user back to the account overview
        return redirect()->route('accounts')->with([
            '_alert' => [
                'type' => 'success',
                'msg' => 'Nieuwe rekening '. $request->input('account').' is toegevoegd.'
            ]
        ]);
    }

    /**
     * Displays the edit form.
     *
     * @param int $id Account ID
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $account = Auth::user()->accounts()->where('id', $id)->first();
        if ($account !== null) {
            // Account does exist and is owned by the user, display the form
            return View('account-form', [
                'account' => $account,
            ]);
        }

        // No account found, or the account is not owned by the user
        return abort(404);
    }

    /**
     * Update the account data.
     *
     * @param Request $request
     * @param int $id Account ID
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEdit(Request $request, $id)
    {
        $account = Auth::user()->accounts()->where('id', $id)->first();
        if ($account !== null) {
            // Account does exist and is owned by the user, validate the form
            $this->validate($request, [
                'name' => 'required',
                'description' => 'required',
            ]);

            // No validation errors, update the object
            $account->fill([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ])->save();

            // Return the user back to the account overview
            return redirect()->route('accounts')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Rekening '. $request->input('name').' gewijzigd.'
                ]
            ]);
        }

        // No account found, or the account is not owned by the user
        return abort(404);
    }

    /**
     * Delete the account.
     *
     * @param Request $request
     * @param int $id Account ID
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDelete(Request $request, $id)
    {
        $account = Auth::user()->accounts()->where('id', $id)->first();
        if ($account !== null) {
            // Account does exists and is owned by the user, delete the transactions
            //     and then delete the account itself
            $account->transactions()->delete();
            $account->delete();

            // Return the user back to the accounts overview
            return redirect()->route('accounts')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Rekening '. $account->account.' verwijderd.'
                ]
            ]);
        }

        // No account found, or the account is not owned by the user
        return abort(404);
    }
}
