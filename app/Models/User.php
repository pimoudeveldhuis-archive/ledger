<?php
/**
 * User model
 *
 * Contains most information about the user, including
 *     their email address, language, oldest and latest
 *     transaction and their public, private and recovery
 *     keys to encrypt and decrypt information.
 *
 * @package App\Models
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class User
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'email_hash',
        'twofactor',
        'language',

        'oldest_transaction',
        'latest_transaction',

        'publickey',
        'secretkey',
        'recoverykey',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'oldest_transaction' => 'date',
        'latest_transaction' => 'date',
    ];

    /**
     * Retrieve the name attribute
     *
     * @param string $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the name attribute
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        // Seal $value and save it
        $this->attributes['name'] = \EncryptionHelper::seal($this->publickey, $value);
    }

    /**
     * Retrieve the email attribute
     *
     * @param string $value
     * @return string
     */
    public function getEmailAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return decrypt($value);
    }

    /**
     * Save the email attribute
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        // Encrypt the value
        $this->attributes['email'] = encrypt($value);
    }

    /**
     * Retrieve the publickey attribute
     *
     * @param string $value
     * @return string
     */
    public function getPublicKeyAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return decrypt($value);
    }

    /**
     * Save the publickey attribute
     *
     * @param string $value
     * @return void
     */
    public function setPublicKeyAttribute($value)
    {
        // Encrypt the value
        $this->attributes['publickey'] = encrypt($value);
    }

    /**
     * Returns the user authentication data
     *
     * @return \App\Models\User\Authentication
     */
    public function authentication()
    {
        return $this->hasOne('App\Models\User\Authentication');
    }

    /**
     * Returns the users bank accounts
     *
     * @return \Illuminate\Support\Collection
     */
    public function accounts()
    {
        return $this->hasMany('App\Models\User\Account');
    }

    /**
     * Returns all transactions owned by this user
     *
     * @return \Illuminate\Support\Collection
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\User\Transaction');
    }

    /**
     * Returns all user specific configurations
     *
     * @return \Illuminate\Support\Collection
     */
    public function configurations()
    {
        return $this->hasMany('App\Models\User\Configuration');
    }

    /**
     * Returns all users import requests
     *
     * @return \Illuminate\Support\Collection
     */
    public function imports()
    {
        return $this->hasMany('App\Models\User\Import');
    }

    /**
     * Returns all the categories owned by the user
     *
     * @return \Illuminate\Support\Collection
     */
    public function categories()
    {
        return $this->hasMany('App\Models\User\Category');
    }

    /**
     * Returns all the budgets owned by the user
     *
     * @return \Illuminate\Support\Collection
     */
    public function budgets()
    {
        return $this->hasMany('App\Models\User\Budget');
    }

    /**
     * Returns all caches from this user
     *
     * @return \Illuminate\Support\Collection
     */
    public function caches()
    {
        return $this->hasMany('App\Models\User\Cache');
    }

    /**
     * Retrieve data from the cache, if the data is
     *     not in the cache facade then try to download
     *     it from the database and store it in the cache
     *     facade for the next time it is requested
     *
     * @param string $key
     * @param boolean $convert
     */
    public function cache($key, $convert = true)
    {
        // Retrieve the value from the cache facade
        $value = \Illuminate\Support\Facades\Cache::get('config-user-'. $this->id .'-'. $key);
        if ($value === null) {
            // Value is null, retrieve it from the database
            $value = $this->caches()->where('key', $key)->value('value');
            
            // If convert is true (default) then decode the value to an array
            if ($convert === true && $value !== null) {
                $value = json_decode($value);
            }

            // Store it in the cache facede forever so it doesn't need to query the database until it changes
            \Illuminate\Support\Facades\Cache::forever('config-user-'. $this->id .'-'. $key, $value);
        }

        return $value;
    }

    /**
     * Cache some user data, this is cached both
     *     in the database and via the Cache facade
     *     so that another session is able to
     *     retrieve the database cache without the
     *     need to run all the queries again (but only
     *     a single query to retrieve the cache)
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $convert
     */
    public function updateCache($key, $value, $convert = true)
    {
        // If convert is true (default) then convert to array to a JSON string
        if ($convert === true) {
            $value = json_encode($value);
        }

        // Update the row if it already exists, if not, create it
        $this->caches()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Clear this item out of the cache facade so that it can be recached
        \Illuminate\Support\Facades\Cache::forget('config-user-'. $this->id .'-'. $key);
    }

    /**
     * Updates all the user caches in the caching database. This
     *     function can also clean it by deleting all user caches
     *     first to clean caches that are not in use anymore (for
     *     example; budgets or categories that are no longer existing
     *
     * @param boolean $cleanup
     * @return void
     */
    public function updateCaches($cleanup = false)
    {
        // Cleanup the cache
        if ($cleanup === true) {
            $this->caches()->delete();
        }

        // Create a budgets array for the barchart
        $budgets = [];
        foreach ($this->budgets as $budget) {
            // Cache the budget itself
            $budget->cache();
            
            // Check whether the budget has any transactions or not
            if ($budget->transactions()->count() > 0) {
                // Add the budget to the array with the sums of previous month
                $budgets[$budget->id] = [
                    'name' => $budget->name,
                    'used' => (
                        $budget->transactions()
                               ->where('dw', 'withdrawal')
                               ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                               ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                               ->sum('amount')
                        - $budget->transactions()
                                 ->where('dw', 'deposit')
                                 ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                                 ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                                 ->sum('amount')
                    ),
                    'budget' => $budget->amounts()
                                       ->where('year', \Carbon\Carbon::now()->subMonth()->year)
                                       ->where('month', \Carbon\Carbon::now()->subMonth()->month)
                                       ->value('amount'),
                ];

                // Replace the used value with a readable format
                $budgets[$budget->id]['used'] = round(
                    ($budgets[$budget->id]['used'] / pow(10, $budget->currency->decimals)),
                    $budget->currency->decimals
                );
                
                // If the budget is null, then take the default amount
                if ($budgets[$budget->id]['budget'] === null) {
                    $budgets[$budget->id]['budget'] = $budget->default_amount;
                }

                // Replace the budget value with a readable format
                $budgets[$budget->id]['budget'] =round(
                    ($budgets[$budget->id]['budget'] / pow(10, $budget->currency->decimals)),
                    $budget->currency->decimals
                );
            }
        }

        if (count($budgets) > 0) {
            // If the array is not empty, create the chartdata array and insert the labels
            $chartdata = [
                'labels' => [],
                'datasets' => [
                    0 => ['label' => 'Gebruikt'],
                    1 => ['label' => 'Budget'],
                    2 => ['label' => 'Budget over'],
                ]
            ];

            foreach ($budgets as $budget) {
                // Loop through the budgets
                $chartdata['labels'][] = $budget['name'];

                if ($budget['used'] > $budget['budget']) {
                    // More money is spent then the budget allowed
                    $chartdata['datasets'][1]['data'][] = $budget['used'] - $budget['budget'];
                    $chartdata['datasets'][0]['data'][] = $budget['budget'];
                    $chartdata['datasets'][2]['data'][] = 0;
                } else {
                    // The budget is not fully spent (or is spent exactly)
                    $chartdata['datasets'][0]['data'][] = $budget['used'];
                    $chartdata['datasets'][1]['data'][] = 0;
                    $chartdata['datasets'][2]['data'][] = $budget['budget'] - $budget['used'];
                }
            }

            // Update the barchart cache
            self::updateCache('budgets-barchart', $chartdata);
        }

        // Create a categories array for the barchart
        $categories = [];
        foreach ($this->categories as $category) {
            // Cache the category itself
            $category->cache();

            if ($category->transactions()->count() > 0) {
                // If the category has any transactions, get all transactions from previous month and sum the spending
                $categories[$category->id] = [
                    'name' => $category->name,
                    'spend' => (
                        $category->transactions()
                                 ->where('dw', 'withdrawal')
                                 ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                                 ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                                 ->sum('amount')
                        - $category->transactions()
                                   ->where('dw', 'deposit')
                                   ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                                   ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                                   ->sum('amount')
                    ),
                ];

                if ($categories[$category->id]['spend'] < 0) {
                    $categories[$category->id]['spend'] *= -1;
                }

                $categories[$category->id]['spend'] = round(
                    ($categories[$category->id]['spend'] / pow(10, $category->currency->decimals)),
                    $category->currency->decimals
                );
            }
        }

        if (count($categories) > 0) {
            // If there are categories with transactions, create the chartdata variable and set the labels
            $chartdata = [
                'labels' => [],
                'datasets' => [
                    0 => ['label' => 'Uitgegeven'],
                ]
            ];

            foreach ($categories as $category) {
                // Fill the chartdata array
                $chartdata['labels'][] = $category['name'];
                $chartdata['datasets'][0]['data'][] = $category['spend'];
            }

            // Update the barchart cache
            self::updateCache('categories-barchart', $chartdata);
        }

        // Update the accounts
        foreach ($this->accounts as $account) {
            $account->cache();
        }
    }

    /**
     * Normally retrieves the remember token, but it's disabled
     *     because you should not want to have remember tokens in
     *     an app that stores your financial data
     *
     * @return null
     */
    public function getRememberToken()
    {
        // Always return null
        return null;
    }
    
    /**
     * Normally this would set the remember token, but it is disabled
     *    because you should not want to have remember tokens in an
     *    app that stores your financial data.
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        // Do nothing
    }

    /**
     * Normally retrieves the remember token name, but it's disabled
     *     because you should not want to have remember tokens in
     *     an app that stores your financial data
     *
     * @return null
     */
    public function getRememberTokenName()
    {
        // Always return null
        return null;
    }
}
