<?php
/**
 * Ajax controller
 *
 * Handles the ajax requests from the application.
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

/**
 * Class AjaxController
 */
class AjaxController extends Controller
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
     * Retrieves user budgets by year, and fills up the
     *     months without a budget with the default budget.
     *     Returns it as a json string.
     *
     * @param Request $request
     * @return string
     */
    public function budgetByYear(Request $request)
    {
        // Retrieve the budget
        $budget = Auth::user()->budgets()->where('id', $request->input('budget'))->first();

        if ($budget !== null) {
            // Get an array with months from Januari and December
            $months = \DateHelper::getMonthsArray();

            // Create the empty budgets array
            $budgets = [];

            foreach ($months as $month) {
                // Fill an array with the default budget
                $budgets[$month] = \CurrencyHelper::readable($budget->currency->code, $budget->default_amount);
            }

            foreach ($budget->amounts()
                            ->where('year', $request->input('year'))
                            ->with('currency')
                            ->get() as $budget_amount) {
                // If there is a budget for the specific month use it
                $budgets[$budget_amount->month] = \CurrencyHelper::readable(
                    $budget_amount->currency->code,
                    $budget_amount->amount
                );
            }

            return json_encode($budgets);
        }

        return abort(500);
    }

    /**
     * Ajax function for the endless transaction scroller. Returns
     *     a json string containing an array.
     *
     * @param Request $request
     * @return string
     */
    public function transactionsScroll(Request $request)
    {
        $html = [];

        // Create a carbon object of the datestring
        $carbon = \Carbon\Carbon::createFromFormat('Y-m', $request->input('loaded_till'));

        // Create a try counter to prevent a endless loop
        $tries = 0;

        // Loop while there is no transaction found yet
        while (count($html) === 0) {
            // Substract a month from the date object
            $carbon->subMonth();

            // If there are month and/or year filters active, check whether the
            //     current date object is within that month and/or year
            if ((
                $request->input('filters')['year'] === null
                    || $carbon->year == $request->input('filters')['year']
            ) && ($request->input('filters')['month'] === null
                    || $carbon->month == $request->input('filters')['month']
            )) {
                // Create the query to retrieve the transactions
                $transactions = Auth::user()->transactions()
                                            ->orderBy('book_date', 'DESC')
                                            ->whereYear('book_date', $carbon->year)
                                            ->whereMonth('book_date', $carbon->month)
                                            ->with(['budget', 'category', 'currency', 'account']);

                if ($request->input('filters')['account'] !== null) {
                    // Add the account filter to the query
                    $transactions->where('user_account_id', $request->input('filters')['account']);
                }
                
                if ($request->input('filters')['budget'] !== null) {
                    // Add a budget filter to the transaction
                    $transactions->where('user_budget_id', $request->input('filters')['budget']);
                }

                if ($request->input('filters')['category'] !== null) {
                    // Add a category filter to the transaction
                    $transactions->where('user_category_id', $request->input('filters')['category']);
                }

                foreach ($transactions->get() as $transaction) {
                    // Loop through all transactions and populate the $html array with the result
                    $html[] = View::make('transaction-row', ['transaction' => $transaction])->render();
                }
            }

            // Add one to the try counter
            $tries++;

            // If there were more then 12 tries (a year of data) and no transactions where found, stop searching
            if ($tries > 12) {
                break;
            }
        }

        // Return the result (even if it's empty) as a json string
        return json_encode([
            'amount' => count($html),
            'loaded_till' => $carbon->format('Y-m'),
            'html' => $html,
        ]);
    }

    /**
     * Returns transactions that comply with selected filters.
     *
     * @param Request $request
     * @return string
     */
    public function transactionsFilter(Request $request)
    {
        $html = [];

        $carbon = \Carbon\Carbon::now();
        
        // Set the starting month to 12 if selected a year
        if ($request->input('filters')['year'] !== null) {
            $carbon->month(12);
        }

        // Create a try counter to prevent a endless loop
        $tries = 0;

        while (count($html) === 0) {
            // If a year filter is set, update the date object to it
            if ($request->input('filters')['year'] !== null) {
                $carbon->year($request->input('filters')['year']);
            }
            
            // If a month filter is set, update the date object to it
            if ($request->input('filters')['month'] !== null) {
                $carbon->month($request->input('filters')['month']);
            }

            // Create the transaction query
            $transactions = Auth::user()->transactions()
                                        ->orderBy('book_date', 'DESC')
                                        ->whereYear('book_date', $carbon->year)
                                        ->whereMonth('book_date', $carbon->month)
                                        ->with(['budget', 'category', 'currency', 'account']);

            if ($request->input('filters')['account'] !== null) {
                // Add the account filter to the query
                $transactions->where('user_account_id', $request->input('filters')['account']);
            }
            
            if ($request->input('filters')['budget'] !== null) {
                // Add a budget filter to the transaction
                $transactions->where('user_budget_id', $request->input('filters')['budget']);
            }

            if ($request->input('filters')['category'] !== null) {
                // Add a category filter to the transaction
                $transactions->where('user_category_id', $request->input('filters')['category']);
            }

            foreach ($transactions->get() as $transaction) {
                // Loop through all transactions and populate the $html array with the result
                $html[] = View::make('transaction-row', ['transaction' => $transaction])->render();
            }

            // Add one to the try counter
            $tries++;

            // If there were more then 12 tries (a year of data) and no transactions where found, stop searching
            if ($tries > 12) {
                break;
            }

            // If no transactions where found, try the previous month
            if (count($html) === 0) {
                $carbon->subMonth();
            }
        }

        // Return the result (even if it's empty) as a json string
        return json_encode([
            'amount' => count($html),
            'loaded_till' => $carbon->format('Y-m'),
            'html'=> $html,
        ]);
    }
}
