<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call the user cache on all users.';

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
        if (\App\Models\User::count() > 0) {
            foreach (\App\Models\User::get() as $user) {
                // Loop through all users and update their cache
                $user->updateCaches(true);
            }
        }
    }
}
