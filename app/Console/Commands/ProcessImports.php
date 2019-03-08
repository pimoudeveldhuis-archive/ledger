<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the queued imports.';

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
        // Retrieve all queued imports
        $imports = \App\Models\User\Import::where('processed', false)->get();
        if (isset($imports) && $imports !== null) {
            foreach ($imports as $import) {
                // Retrieve the file
                if (Storage::exists('imports/'. $import->user->id .'/'. $import->uuid .'/'. $import->filename)) {
                    $file = Storage::get('imports/'. $import->user->id .'/'. $import->uuid .'/'. $import->filename);
                }

                if (isset($file)) {
                    // Get the transactions array
                    list(
                        $transactions,
                        $errors
                    ) = \IOHelper::import(
                        $import->user,
                        $file,
                        $import->importConfiguration->data
                    );
                    
                    // Process the array
                    if (count($errors) > 0) {
                        // Errors found, hold all actions
                        $import->fill(['processed' => true, 'errors' => json_encode($errors)])->save();
                    } elseif ($transactions !== null && count($transactions) > 0) {
                        // Everything went well, create a transaction and start the importing
                        DB::beginTransaction();

                        $_transactions = [];
                        foreach ($transactions as $transaction) {
                            $transaction['user_import_id'] = $import->id;

                            //Check for possible duplicates
                            if ($import->user
                                       ->transactions()
                                       ->where('duplicate_hash', $transaction['duplicate_hash'])
                                       ->count() > 0
                            ) {
                                $transaction['duplicate'] = true;
                            }

                            $import->user->transactions()->create($transaction);
                        }

                        $import->fill(['processed' => true, 'errors' => null])->save();
                        DB::commit();
                    }
                }

                $import->user->fill([
                    'oldest_transaction' => $import->user
                                                   ->transactions()
                                                   ->orderBy('book_date', 'ASC')
                                                   ->value('book_date'),
                    'latest_transaction' => $import->user
                                                   ->transactions()
                                                   ->orderBy('book_date', 'DESC')
                                                   ->value('book_date'),
                ])->save();
            }
        }
    }
}
