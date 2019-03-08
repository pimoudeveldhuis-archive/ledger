<?php
/**
 * Category model
 *
 * Model used for categories like rent. It contains
 *     the user that owns it, a name and description,
 *     an icon (optional), the currency_id of the currency
 *     used by this category and the rule conditions
 *     which are used to automatically assign transactions
 *     to the category.
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Category
 */
class Category extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'currency_id',

        'name',
        'description',
        'icon',

        'conditions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'currency_id' => 'integer',
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
        $this->attributes['name'] = \EncryptionHelper::seal($this->user->publickey, $value);
    }

    /**
     * Retrieve the description attribute
     *
     * @param string $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the description attribute
     *
     * @param string $value
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        // Seal $value and save it
        $this->attributes['description'] = \EncryptionHelper::seal($this->user->publickey, $value);
    }

    /**
     * Retrieve the icon attribute
     *
     * @param string $value
     * @return string
     */
    public function getIconAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
    }

    /**
     * Save the icon attribute
     *
     * @param string $value
     * @return void
     */
    public function setIconAttribute($value)
    {
        // Seal $value and save it
        $this->attributes['icon'] = ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

    /**
     * Retrieve the conditions attribute
     *
     * @param string $value
     * @return array|null
     */
    public function getConditionsAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return ($value !== null) ? decrypt($value) : null;
    }

    /**
     * Save the conditions attribute
     *
     * @param array|null $value
     * @return void
     */
    public function setConditionsAttribute($value)
    {
        // Encrypt the value
        $this->attributes['conditions'] = ($value !== null) ? encrypt($value) : null;
    }

    /**
     * Returns the currency
     *
     * @return \App\Models\Currency
     */
    public function currency()
    {
        return $this->belongsTo('\App\Models\Currency');
    }

    /**
     * Returns all transactions in this category
     *
     * @return \Illuminate\Support\Collection
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\User\Transaction', 'user_category_id');
    }

    /**
     * Returns the user model
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Apply the category conditions to a transaction
     *     and if so associate the transaction with
     *     this category
     *
     * @param \App\Models\User\Transaction $transaction
     * @return boolean|integer Amount of transactions that
     *     are associated with the category, or false if
     *     no conditions exists
     */
    public function run($transactions)
    {
        // If there are no conditions, return false
        if ($this->conditions === null || json_decode($this->conditions) === null) {
            return false;
        }

        // Create a counter
        $i=0;

        // Loop through all transactions
        foreach ($transactions as $transaction) {
            // Apply the rules on the transaction and if they succeed check if the transaction uses the same currency
            if (\RuleHelper::check($transaction, json_decode($this->conditions))
             && $transaction->currency->id === $this->currency->id) {
                // It all checks out, associate the transaction with the category
                $transaction->category()->associate($this)->save();
                
                // Add one to the counter
                $i++;
            }
        }

        // Return the counter that displays the amount of transactions that are associated with the category
        return $i;
    }

    /**
     * Cache the category to the user cache
     *
     * @return void
     */
    public function cache()
    {
        // Create the homescreen cache
        $this->user->updateCache('category-'. $this->id.'-home', self::cacheHome());

        // Create the category overview cache
        $this->user->updateCache('category-'. $this->id.'-overview', self::cacheOverview());
    }

    /**
     * Retrieve the overview chart array
     *
     * @return array
     */
    public function cacheOverview()
    {
        // Retrieve array with 12 months and the range to be queried
        list($last12months, $range) = \DateHelper::getLastMonths(12);

        // Retrieve all transactions within the range
        $transactions = $this->transactions()
                             ->whereDate('book_date', '>=', $range['start'])
                             ->whereDate('book_date', '<=', $range['end']);

        if ($transactions->count() > 0) {
            foreach ($transactions->get() as $transaction) {
                if ($transaction->dw === 'withdrawal') {
                    $last12months[$transaction->book_date->format('Y') .'.'. $transaction->book_date->format('n')]
                        += $transaction->amount;
                } elseif ($transaction->dw === 'deposit') {
                    $last12months[$transaction->book_date->format('Y') .'.'. $transaction->book_date->format('n')]
                        -= $transaction->amount;
                }
            }
        }

        // Retrieve array with the 12 previous months and the range to be queried
        list($previous12months, $range) = \DateHelper::getLastMonths(12, 12);

        // Retrieve all transactions within the range
        $transactions = $this->transactions()
                             ->whereDate('book_date', '>=', $range['start'])
                             ->whereDate('book_date', '<=', $range['end']);

        if ($transactions->count() > 0) {
            foreach ($transactions->get() as $transaction) {
                if ($transaction->dw === 'withdrawal') {
                    $previous12months[$transaction->book_date->format('Y') .'.'. $transaction->book_date->format('n')]
                        += $transaction->amount;
                } elseif ($transaction->dw === 'deposit') {
                    $previous12months[$transaction->book_date->format('Y') .'.'. $transaction->book_date->format('n')]
                        -= $transaction->amount;
                }
            }
        }

        // Create the chartdata array
        $chartdata = [];

        //If every month turns out below 0, we need to flip the values to positive as this is probably a income category
        $positivityCheck = array_merge($last12months, $previous12months);
        rsort($positivityCheck);

        foreach ($last12months as $date => $amount) {
            // Loop through the months and create the chart labels
            $date = explode(".", $date);
            $chartdata['labels'][] = \Carbon\Carbon::create($date[0], $date[1], 1, 0, 0, 0)->format('F');
        }

        foreach ($last12months as &$month) {
            // Loop through the months and make a human readable amount
            $month /= pow(10, $this->currency->decimals);

            // Flip the amount if it turns out to be a income category
            if ($positivityCheck[0] <= 0) {
                $month *= -1;
            }
        }

        foreach ($previous12months as &$month) {
            // Loop through the months and make a human readable amount
            $month /= pow(10, $this->currency->decimals);

            // Flip the amount if it turns out to be a income category
            if ($positivityCheck[0] <= 0) {
                $month *= -1;
            }
        }

        // Create the datasets array
        $chartdata['datasets'] = [];

        // Create the past 12 months array
        $chartdata['datasets'][] = [
            'label' => 'Vorige 12 maanden',
            'data'  => array_values($previous12months),
        ];

        // Create the previous 12 months array
        $chartdata['datasets'][] = [
            'label' => 'Laatste 12 maanden',
            'data'  => array_values($last12months),
        ];

        // Return the chartdata array
        return $chartdata;
    }

    /**
     * Retrieve the homescreen array
     *
     * @return array
     */
    public function cacheHome()
    {
        // Retrieve the data that should be cached and store it in an array
        $home = [
            'previousMonth' => (
                $this->transactions()
                     ->where('dw', 'withdrawal')
                     ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                     ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                     ->sum('amount')
                - $this->transactions()
                       ->where('dw', 'deposit')
                       ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                       ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                       ->sum('amount')
            ),
            'previous6months' => (
                $this->transactions()
                     ->where('dw', 'withdrawal')
                     ->whereBetween('book_date', [
                         \Carbon\Carbon::now()->subMonths(6)->startOfMonth(),
                         \Carbon\Carbon::now()->startOfMonth()->subSecond()
                     ])->sum('amount')
                - $this->transactions()
                       ->where('dw', 'deposit')
                       ->whereBetween('book_date', [
                           \Carbon\Carbon::now()->subMonths(6)->startOfMonth(),
                           \Carbon\Carbon::now()->startOfMonth()->subSecond()
                       ])->sum('amount')
            ),
            'previous12months' => (
                $this->transactions()
                     ->where('dw', 'withdrawal')
                     ->whereBetween('book_date', [
                         \Carbon\Carbon::now()->subMonths(12)->startOfMonth(),
                         \Carbon\Carbon::now()->startOfMonth()->subSecond()
                     ])->sum('amount')
                - $this->transactions()
                       ->where('dw', 'deposit')
                       ->whereBetween('book_date', [
                           \Carbon\Carbon::now()->subMonths(12)->startOfMonth(),
                           \Carbon\Carbon::now()->startOfMonth()->subSecond()
                       ])->sum('amount')
            ),
        ];

        // Return the array
        return $home;
    }
}
