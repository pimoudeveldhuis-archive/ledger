<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** Auto Deployment */
Route::get('deploy', function() {
    if(request()->header('token') !== config('torpedo.token')) return 'err_token';
    
    if(request()->input('action') === 'heartbeat') {
        return 'success';
    } elseif(request()->input('action') === 'pull') {
        chdir('../');
        
        $gitpull = exec('git pull 2>&1');
        if(strpos($gitpull, 'fatal: could not read Password for') !== false) {
            return 'err_git_no_ssh';
        }

        $migration = exec('php artisan migrate --force');
        
        return 'success';
    }
    
    return 'err_no_valid_action';
});