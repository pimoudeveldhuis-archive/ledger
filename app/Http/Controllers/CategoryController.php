<?php
/**
 * Category controller
 *
 * Handles the category CRUD.
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CategoryController
 */
class CategoryController extends Controller
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
     * Displays the categories overview.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return View('categories');
    }

    /**
     * Displays the category details
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        // Retrieve the category from the user and if it can be found return the view
        $category = Auth::user()->categories()->where('id', $id)->first();
        if ($category !== null) {
            return View('category', [
                'category' => $category,
            ]);
        }

        return abort(404);
    }

    /**
     * Returns a view with a form to create a new category
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
        return View('category-form', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Create the category
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
        ]);

        // Retrieve the currencies and check whether the submitted currency can be found or not
        $currency = \App\Models\Currency::find($request->input('currency_id'));
        if ($currency === null) {
            return redirect()->route('category-create')
                ->withErrors(['currency_id' => 'Currency not found.'])
                ->withInput();
        }

        // Create the category
        $category = new \App\Models\User\Category();
        $category->fill([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'icon' => ($request->input('icon') !== '') ? $request->input('icon') : null,
            'currency_id' => $request->input('currency_id'),
        ])->save();

        // Return the user to the category overview
        return redirect()->route('categories')->with([
            '_alert' => [
                'type' => 'success',
                'msg' => 'Nieuwe categorie '. $request->input('name').' is toegevoegd.'
            ]
        ]);
    }

    /**
     * Returns a view with a form to edit the category
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Retrieve the category from the user and if it exists display it in the form
        $category = Auth::user()->categories()->where('id', $id)->first();
        
        if ($category !== null) {
            // Retrieve the currencies from the database
            $currencies = [];
            foreach (\App\Models\Currency::get() as $currency) {
                $currencies[] = ['key' => $currency->id, 'value' => $currency->code];
            }

            return View('category-form', [
                'category' => $category,
                'currencies' => $currencies,
            ]);
        }

        return abort(404);
    }

    /**
     * Edit the category
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEdit(Request $request, $id)
    {
        // Retrieve the category from the user to update it
        $category = Auth::user()->categories()->where('id', $id)->first();

        if ($category !== null) {
            // Validate the form data
            $this->validate($request, [
                'name' => 'required',
                'description' => 'required',
                'currency_id' => 'required',
            ]);

            // Retrieve the currencies and check whether the submitted currency can be found or not
            $currency = \App\Models\Currency::find($request->input('currency_id'));
            if ($currency === null) {
                return redirect()->route('category-edit', ['id' => $category->id])
                    ->withErrors(['currency_id' => 'Currency not found.'])
                    ->withInput();
            }
    
            // Update the category
            $category->fill([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'icon' => ($request->input('icon') !== '') ? $request->input('icon') : null,
                'currency_id' => $request->input('currency_id'),
            ])->save();

            // Return the user to the category overview
            return redirect()->route('categories')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Categorie '. $request->input('name').' gewijzigd.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Do edit the category conditions with links the transaction
     *     to this category. This function is used to add a
     *     condition. Deletion of condition happens via doDeleteCondition.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEditConditions(Request $request, $id)
    {
        // Retrieve the category from the user to edit it's conditions
        $category = Auth::user()->categories()->where('id', $id)->first();
        if ($category !== null) {
            // If there are no conditions yet, create a clean array, else decode the current conditions
            if ($category->conditions === null) {
                $conditions = [];
            } else {
                $conditions = json_decode($category->conditions, true);
            }

            // Add the condition to the array
            $conditions[] = ['type' => $request->input('type'), 'data' => $request->input('data')];

            // Store the condition array in the category while using array_values() to remove the iterator key
            $category->fill(['conditions' => json_encode(array_values($conditions))])->save();

            // Return the user to the category edit form
            return redirect()->route('category-edit', [
                'id' => $category->id
            ])->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Regels aangepast.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Delete a category
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDelete(Request $request, $id)
    {
        $category = Auth::user()->categories()->where('id', $id)->first();
        if ($category !== null) {
            // Unlink the category from all transactions
            $category->transactions()->update(['user_category_id' => null]);

            // Delete the category
            $category->delete();

            // Redirect the user to the category overview
            return redirect()->route('categories')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Categorie '. $category->name.' verwijderd.'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Apply the conditions from the category on all transactions
     *     and link the transactions where it succeeds to this
     *     category.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doRunConditions(Request $request, $id)
    {
        $category = Auth::user()->categories()->where('id', $id)->first();
        if ($category !== null) {
            // When the conditions are run in the model it returns the number of times it
            //     flagged a success, so it can be returned to the user how many transactions
            //     are not linked to the category
            $i = $category->run(Auth::user()->transactions);
            
            // Update the user cache
            Auth::user()->updateCaches();

            // Redirect the user to the category overview
            return redirect()->route('categories')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Categorie '. $category->name.' regels toegepast. '.$i.' transacties toegewezen'
                ]
            ]);
        }

        return abort(404);
    }

    /**
     * Delete a condition that links transactions to this category.
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
        $category = Auth::user()->categories()->where('id', $id)->first();
        if ($category !== null) {
            // Check if the conditions are not empty as in that case it is not needed to delete something
            if ($category->conditions !== null) {
                // Decode the json string to an array
                $conditions = json_decode($category->conditions, true);

                // If the $i key exists, remove it from the array
                if (isset($conditions[$i])) {
                    unset($conditions[$i]);
                }
            }

            // Store the condition array in the category while using array_values() to remove the iterator key
            $category->fill(['conditions' => json_encode(array_values($conditions))])->save();

            // Return the user to the category edit form
            return redirect()->route('category-edit', [
                'id' => $category->id
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
