<?php
/**
 * Transaction controller
 *
 * Handles the transaction CRUD.
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class TransactionController
 */
class TransactionController extends Controller
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
        $filters = [];

        // Create accounts filter array
        $filters['accounts'][''] = 'Geen';
        foreach (Auth::user()->accounts as $account) {
            $filters['accounts'][$account->id] = $account->name;
        }

        // Create budgets filter array
        $filters['budgets'][''] = 'Geen';
        foreach (Auth::user()->budgets as $budget) {
            $filters['budgets'][$budget->id] = $budget->name;
        }

        // Create categories filter array
        $filters['categories'][''] = 'Geen';
        foreach (Auth::user()->categories as $category) {
            $filters['categories'][$category->id] = $category->name;
        }

        // Create months filter array
        $filters['months'][''] = 'Geen';
        for ($i = 1; $i <= 12; $i++) {
            $filters['months'][$i] = __('date.months.' . \DateHelper::getMonthName($i));
        }

        // Create the years filter array
        $filters['years'][''] = 'Geen';

        $oldest_transaction = Auth::user()->oldest_transaction->format('Y');
        $latest_transaction = Auth::user()->latest_transaction->format('Y');

        // Loop between the oldest and latest transaction to create a filter item for each year
        for ($i = $oldest_transaction; $i <= $latest_transaction; $i++) {
            $filters['years'][$i] = $i;
        }

        return View('transactions', [
            'filters' => $filters
        ]);
    }

    /**
     * Delete a transaction
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDelete(Request $request, $id)
    {
        // Retrieve the specific transaction and if it exists, delete it
        $transaction = Auth::user()->transactions()->where('id', $id)->first();
        if ($transaction !== null) {
            $transaction->delete();

            return redirect()->route('transactions')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Transactie verwijderd.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Display all duplicates
     *
     * @return \Illuminate\View\View
     */
    public function duplicates()
    {
        return View('duplicates');
    }

    /**
     * Save a duplicate, which makes it a non-duplicate transaction
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDuplicateSave($id)
    {
        $transaction = Auth::user()->transactions()->where('id', $id)->first();
        if ($transaction !== null) {
            // Update the transaction not to be a duplicate
            $transaction->fill([
                'duplicate' => false,
            ])->save();

            return redirect()->route('duplicates')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Transactie bewaard.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Delete a duplicate transaction.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDuplicateDelete($id)
    {
        $transaction = Auth::user()->transactions()->where('id', $id)->first();
        if ($transaction !== null) {
            // Delete the duplicate transaction
            $transaction->delete();

            return redirect()->route('duplicates')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Transactie verwijderd.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Delete all duplicate transactions
     */
    public function doDuplicateDeleteAll()
    {
        // Delete all transactions that are marked as duplicate
        Auth::user()->transactions()->where('duplicate', true)->delete();
        
        return redirect()->route('home')->with([
            '_alert' => [
                'type' => 'success',
                'msg' => 'Alle dubbele transacties zijn verwijderd.'
            ]
        ]);
    }
}
