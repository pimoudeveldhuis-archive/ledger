<?php
/**
 * Import controller
 *
 * Handles the imports
 *
 * @package App\Http\Controllers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Ramsey\Uuid\Uuid;

/**
 * Class ImportController
 */
class ImportController extends Controller
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
     * Displays the import form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $import_configurations = [];
        if (\App\Models\ImportConfiguration::count() > 0) {
            foreach (\App\Models\ImportConfiguration::get() as $import_configuration) {
                $import_configurations[] = ['key' => $import_configuration->id, 'value' => $import_configuration->name];
            }
        }
        return View('import-create', [
            'import_configurations' => $import_configurations,
        ]);
    }

    /**
     * Creates a import task
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doImport(Request $request)
    {
        // Validate the form
        $this->validate($request, [
            'import_configuration_id' => 'required',
        ]);

        // If no file was uploaded, return to the form with an error
        if ($request->file('csv') === null) {
            return redirect()->route('import-create')
                ->withErrors(['csv' => 'Kies een bestand om te uploaden.'])
                ->withInput();
        }

        // Retrieve the import configuration ID, if it cannot be found then return to the form with an error
        $import_configuration = \App\Models\ImportConfiguration::find($request->input('import_configuration_id'));
        if ($import_configuration === null) {
            return redirect()->route('import-create')
                ->withErrors(['import_configuration_id' => 'Probleem met bank configuratie.'])
                ->withInput();
        }

        // Create the import task in the database
        $import = Auth::user()->imports()->create([
            'uuid' => Uuid::uuid4()->toString(),
            'import_configuration_id' => $import_configuration->id,
            'filename' => $request->file('csv')->getClientOriginalName(),
            'errors' => null,
        ]);

        // Store the import file
        Storage::putFileAs(
            'imports/'. Auth::user()->id .'/'. $import->uuid .'/',
            $request->file('csv'),
            $request->file('csv')->getClientOriginalName()
        );

        // Return to the form with a success msg
        return redirect()->route('import-create')->with([
            '_alert' => [
                'type' => 'success',
                'msg' => 'Bestand geupload en in de wachtrij gezet om verwerkt te worden.'
            ]
        ]);
    }

    /**
     * Displays a import that contains an error
     *
     * @param Request $request
     * @param string $uuid
     *
     * @return \Illuminate\View\View
     */
    public function error(Request $request, $uuid)
    {
        $import = Auth::user()->imports()->where('uuid', $uuid)->first();
        if ($import !== null && $import->errors !== null) {
            // If the import can be found and has errors, display the error view
            return View('import-error', [
                'import' => $import,
            ]);
        }

        return abort(404);
    }

    /**
     * Delete a failed import
     *
     * @param Request $request
     * @param string $uuid
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doDelete(Request $request, $uuid)
    {
        $import = Auth::user()->imports()->where('uuid', $uuid)->first();

        // Check whether the import can be found or not and whether there are errors or not
        if ($import !== null && $import->errors !== null) {
            // Clean the errors so the scheduler will clean up and remove both the uploaded file
            //     and the database row from the import
            $import->fill(['errors' => null])->save();

            // Redirect the user back to the imports view
            return redirect()->route('import-create')->with([
                '_alert' => [
                    'type' => 'success',
                    'msg' => 'Bestand staat in de wachtrij en zal spoedig verwijderd worden.'
                ]
            ]);
        }

        return abort(404);
    }
}
