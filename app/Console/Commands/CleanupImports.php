<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up old imports.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Retrieve all finished imports
        $imports = \App\Models\User\Import::where('processed', true)->get();
        if (isset($imports) && $imports !== null) {
            foreach ($imports as $import) {
                if ($import->errors === null) {
                    // Only remove imports that had no errors
                    if (Storage::exists('imports/'. $import->user->id .'/'. $import->uuid)) {
                        //If the file directory exists, destroy it
                        Storage::deleteDirectory('imports/'. $import->user->id .'/'. $import->uuid);
                    }

                    // Now delete the database entry
                    $import->delete();
                }
            }
        }
    }
}
