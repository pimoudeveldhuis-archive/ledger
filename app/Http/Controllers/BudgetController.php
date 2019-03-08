<?php
/**
 * Budget controller
 *
 * Handles the budget CRUD.
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class BudgetController
 */
class BudgetController extends Controller
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
     * Displays the budgets overview.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return View('budgets');
    }

    /**
     * Returns a budget details view for a single budget
     *
     * @param int $id Budget ID
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        if ($budget !== null) {
            return View('budget', [
                'budget' => $budget,
            ]);
        }

        return abort(404);
    }

    /**
     * Returns a view with a form to create a new budget
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Retrieve the currencies from the database
        $currencies = [];
        foreach (\App\Models\Currency::get() as $currency) {
            $currencies[] = ['key' => $currency->id, 'value' => $currency->code];
        }

        // Return the form view
        return View('budget-form', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Create the budget
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doCreate(Request $request)
    {
        // Validate the form data
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'currency_id' => 'required',
            'default_amount' => 'required',
        ]);
        
        // Retrieve the currencies and check whether the submitted currency can be found or not
        $currency = \App\Models\Currency::find($request->input('currency_id'));
        if ($currency === null) {
            return redirect()->route('budget-create')
                ->withErrors(['currency_id' => 'Currency not found.'])
                ->withInput();
        }

        // Create the budget
        $budget = new \App\Models\User\Budget();
        $budget->fill([
            'user_id' => Auth::id(),

            'name' => $request->input('name'),
            'description' => $request->input('description'),

            'currency_id' => $request->input('currency_id'),
            'default_amount' => \CurrencyHelper::convert($currency->code, $request->input('default_amount')),

            'icon' => ($request->input('icon') !== '') ? $request->input('icon') : null,
            'conditions' => null,
        ])->save();

        // Return the user to the budget overview
        return redirect()->route('budgets')->with([
            '_alert' => [
                'type' => 'success',
                'msg' => 'Nieuw budget '. $request->input('name').' is toegevoegd.'
            ]
        ]);
    }

    /**
     * Returns a view with a form to edit the budget
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Retrieve the budget from the user and if it exists display it in the form
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        
        if ($budget !== null) {
            // Retrieve the currencies from the database
            $currencies = [];
            foreach (\App\Models\Currency::get() as $currency) {
                $currencies[] = ['key' => $currency->id, 'value' => $currency->code];
            }

            return View('budget-form', [
                'budget' => $budget,
                'currencies' => $currencies,
            ]);
        }

        return abort(404);
    }

    /**
     * Edit the budget
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEdit(Request $request, $id)
    {
        // Retrieve the budget from the user to update it
        $budget = Auth::user()->budgets()->where('id', $id)->first();

        if ($budget !== null) {
            // Validate the form data
            $this->validate($request, [
                'name' => 'required',
                'description' => 'required',
                'currency_id' => 'required',
                'default_amount' => 'required',
            ]);

            // Retrieve the currencies and check whether the submitted currency can be found or not
            $currency = \App\Models\Currency::find($request->input('currency_id'));
            if ($currency === null) {
                return redirect()->route('budget-edit', ['id' => $budget->id])
                    ->withErrors(['currency_id' => 'Currency not found.'])
                    ->withInput();
            }
    
            // Update the budget
            $budget->fill([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
    
                'currency_id' => $request->input('currency_id'),
                'default_amount' => \CurrencyHelper::convert($currency->code, $request->input('default_amount')),
    
                'icon' => ($request->input('icon') !== '') ? $request->input('icon') : null,
            ])->save();

            // Return the user to the budgets overview
            return redirect()->route('budgets')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Budget '. $request->input('name').' gewijzigd.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Do edit the budget conditions with links the transaction
     *     to this budget. This function is used to add a
     *     condition. Deletion of condition happens via doDeleteCondition.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEditConditions(Request $request, $id)
    {
        // Retrieve the budget from the user to edit it's conditions
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        if ($budget !== null) {
            // If there are no conditions yet, create a clean array, else decode the current conditions
            if ($budget->conditions === null) {
                $conditions = [];
            } else {
                $conditions = json_decode($budget->conditions, true);
            }

            // Add the condition to the array
            $conditions[] = ['type' => $request->input('type'), 'data' => $request->input('data')];
            
            // Store the condition array in the budget while using array_values() to remove the iterator key
            $budget->fill(['conditions' => json_encode(array_values($conditions))])->save();

            // Return the user to the budget edit form
            return redirect()->route('budget-edit', [
                'id' => $budget->id
            ])->with([
                '_alert' => [
                    'type' => 'success', 'msg' => 'Regels aangepast.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Updates the budget amounts for a specific year
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEditAmounts(Request $request, $id)
    {
        // Retrieve the budget from the user to edit it's conditions
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        if ($budget !== null) {
            // Check whether the year is specified in the form data and the month array exists
            if ($request->input('year') !== null && $request->input('month') !== null) {
                // Loop through the months
                foreach ($request->input('month') as $key => $value) {
                    // Update or create the budget amounts for each month
                    $budget->amounts()->updateOrCreate(
                        [
                            'year' => $request->input('year'),
                            'month' => $key
                        ],
                        [
                            'currency_id' => $budget->currency->id,
                            'amount' => \CurrencyHelper::convert($budget->currency->code, $value)
                        ]
                    );
                }

                // Update the user cache
                Auth::user()->updateCaches();
            }

            // Return the user to the budget details view
            return redirect()->route('budget', [
                'id' => $budget->id
            ])->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Budget bedragen aangepast voor '. $request->input('year') .'.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Delete a budget
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDelete(Request $request, $id)
    {
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        if ($budget !== null) {
            // Unlink the budget from all transactions
            $budget->transactions()->update(['user_budget_id' => null]);

            // Delete the budget
            $budget->delete();

            // Redirect the user to the budgets overview
            return redirect()->route('budgets')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Budget '. $budget->name.' verwijderd.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Apply the conditions from the budget on all transactions
     *     and link the transactions where it succeeds to this
     *     budget.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doRunConditions(Request $request, $id)
    {
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        if ($budget !== null) {
            // When the conditions are run in the model it returns the number of times it
            //     flagged a success, so it can be returned to the user how many transactions
            //     are not linked to the budget
            $i = $budget->run(Auth::user()->transactions);
            
            // Update the user cache
            Auth::user()->updateCaches();

            // Redirect the user to the budgets overview
            return redirect()->route('budgets')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Budget '. $budget->name.' regels toegepast. '.$i.' transacties toegewezen'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Delete a condition that links transactions to this budget.
     *     Updating conditions happens with the doEditConditions
     *     function. The conditions array keys iterates, with the
     *     $i being the key of the condition to be deleted.
     *
     * @param Request $request
     * @param int $id
     * @param int $i
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDeleteCondition(Request $request, $id, $i)
    {
        $budget = Auth::user()->budgets()->where('id', $id)->first();
        if ($budget !== null) {
            // Check if the conditions are not empty as in that case it is not needed to delete something
            if ($budget->conditions !== null) {
                // Decode the json string to an array
                $conditions = json_decode($budget->conditions, true);

                // If the $i key exists, remove it from the array
                if (isset($conditions[$i])) {
                    unset($conditions[$i]);
                }
            }

            // Store the condition array in the budget while using array_values() to remove the iterator key
            $budget->fill(['conditions' => json_encode(array_values($conditions))])->save();

            // Return the user to the budget edit form
            return redirect()->route('budget-edit', [
                'id' => $budget->id
            ])->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Regels aangepast.'
                ]
            ]);
        }

        return abort(404);
    }
}
