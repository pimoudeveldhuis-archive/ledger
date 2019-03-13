<?php
/**
 * Budget model
 *
 * Model used for budgets like groceries. It contains
 *     the user that owns it, a name and description,
 *     an icon (optional), the currency_id of the currency
 *     used by this budget, a default budget (the budget
 *     can be adjusted for each month) and the rule conditions
 *     which are used to automatically assign transactions
 *     to the budget.
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Budget
 */
class Budget extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_budgets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'icon',
        'currency_id',
        'default_amount',
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
        'default_amount' => 'integer',
    ];

    /**
     * Retrieve the name attribute
     *
     * @param string $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
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
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if (config('app.debug') === true) {
            $this->attributes['name'] = $value;
        } else {
            $this->attributes['name'] = \EncryptionHelper::seal($this->publickey, $value);
        }
    }

    /**
     * Retrieve the description attribute
     *
     * @param string $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
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
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if (config('app.debug') === true) {
            $this->attributes['description'] = $value;
        } else {
            $this->attributes['description'] = \EncryptionHelper::seal($this->publickey, $value);
        }
    }

    /**
     * Retrieve the icon attribute
     *
     * @param string $value
     * @return string|null
     */
    public function getIconAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the icon attribute
     *
     * @param string $value
     * @return void
     */
    public function setIconAttribute($value)
    {
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if ($value === null) {
            $this->attributes['icon'] = null;
        } elseif (config('app.debug') === true) {
            $this->attributes['icon'] = $value;
        } else {
            $this->attributes['icon'] = \EncryptionHelper::seal($this->publickey, $value);
        }
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
     * Retrieve all budget amounts for this budget
     *
     * @return \Illuminate\Support\Collection
     */
    public function amounts()
    {
        return $this->hasMany('\App\Models\User\BudgetAmount', 'user_budget_id');
    }

    /**
     * Returns the currency used by the budget
     *
     * @return \App\Models\Currency
     */
    public function currency()
    {
        return $this->belongsTo('\App\Models\Currency');
    }

    /**
     * Return the transactions linked with this budget
     *
     * @return \App\Models\Transaction
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\User\Transaction', 'user_budget_id');
    }

    /**
     * Returns the user that owns the budget
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Apply the budget conditions to a transaction
     *     and if so associate the transaction with
     *     this budget
     *
     * @param \App\Models\User\Transaction $transaction
     * @return boolean|integer Amount of transactions that
     *     are associated with the budget, or false if no
     *     conditions exists
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
                    // It all checks out, associate the transaction with the budget
                    $transaction->budget()->associate($this)->save();
                
                // Add one to the counter
                $i++;
            }
        }

        // Return the counter that displays the amount of transactions that are associated with the budget
        return $i;
    }

    /**
     * Cache the budget to the user cache
     *
     * @return void
     */
    public function cache()
    {
        // Create the homescreen cache
        $this->user->updateCache('budget-'. $this->id.'-home', self::cacheHome());

        // Create the budget overview cache
        $this->user->updateCache('budget-'. $this->id.'-overview', self::cacheOverview());

        // Create the longterm chart cache
        $this->user->updateCache('budget-'. $this->id.'-longterm', self::cacheLongterm());

        // Create the comparing to previous year cache
        $this->user->updateCache('budget-'. $this->id.'-comparingPreviousYear', self::cacheComparingPreviousYear());
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
            'previousMonthBudget' =>
                $this->amounts()
                     ->where('year', \Carbon\Carbon::now()->subMonth()->year)
                     ->where('month', \Carbon\Carbon::now()->subMonth()->month)
                     ->value('amount'),
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

        // If the previous month budget is not found, set the budget to the default amount
        if ($home['previousMonthBudget'] === null) {
            $home['previousMonthBudget'] = $this->default_amount;
        }

        // Return the array
        return $home;
    }

    /**
     * Retrieve the overview chart array
     *
     * @return array
     */
    public function cacheOverview()
    {
        // Retrieve array with all months and the range to be queried
        list($last12months, $range) = \DateHelper::getLastMonths(12);

        $budgets = $last12months;
        $overdraw = $last12months;

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

        // Set chart labels
        $chartdata = [
            'labels' => [],
            'datasets' => [
                0 => ['label' => 'Gebruikt'],
                1 => ['label' => 'Budget'],
                2 => ['label' => 'Budget over'],
            ]
        ];

        // Loop through the data
        foreach ($last12months as $date => $amount) {
            // Explode the date from month.year to an array
            $date_exploded = explode(".", $date);

            // Create the chart labels
            $chartdata['labels'][] = \Carbon\Carbon::create(
                $date_exploded[0],
                $date_exploded[1],
                1,
                0,
                0,
                0
            )->format('F');

            // Retrieve the budget for the month, if not existing use the default budget
            $budget = $this->amounts()
                           ->where('year', $date_exploded[0])
                           ->where('month', $date_exploded[1])
                           ->value('amount');
            
            if ($budget === null) {
                $budget = $this->default_amount;
            }

            // Mutate the amounts to decimals
            $amount /= pow(10, $this->currency->decimals);
            $budget /= pow(10, $this->currency->decimals);

            if ($amount > $budget) {
                // There is overspending
                $chartdata['datasets'][1]['data'][] = $amount - $budget;
                $chartdata['datasets'][0]['data'][] = $budget;
                $chartdata['datasets'][2]['data'][] = 0;
            } else {
                // No overspending this month, good job :)
                $chartdata['datasets'][0]['data'][] = $amount;
                $chartdata['datasets'][1]['data'][] = 0;
                $chartdata['datasets'][2]['data'][] = $budget - $amount;
            }
        }

        // Return chartdata array
        return $chartdata;
    }

    /**
     * Retrieve the longterm chart array
     *
     * @return array
     */
    public function cacheLongterm()
    {
        // Retrieve the last 6 years in an array
        list($last6years, $range) = \DateHelper::getLastYears(6);

        // Retrieve all transactions within the date range and add it to the array
        $transactions = $this->transactions()
                             ->whereDate('book_date', '>=', $range['start'])
                             ->whereDate('book_date', '<=', $range['end']);

        if ($transactions->count() > 0) {
            foreach ($transactions->get() as $transaction) {
                if ($transaction->dw === 'withdrawal') {
                    $last6years[$transaction->book_date->format('Y')] += $transaction->amount;
                } elseif ($transaction->dw === 'deposit') {
                    $last6years[$transaction->book_date->format('Y')] -= $transaction->amount;
                }
            }
        }

        $chartdata = [];

        foreach ($last6years as $date => &$amount) {
            // Create the chart labels
            $chartdata['labels'][] = \Carbon\Carbon::create($date, 1, 1, 0, 0, 0)->format('Y');

            // Mutate the amounts to decimals
            $amount /= pow(10, $this->currency->decimals);
        }

        // Create the dataset array
        $chartdata['datasets'] = [];

        // Add the data to the dataset array
        $chartdata['datasets'][] = [
            'label' => 'Totale uitgaven',
            'data'  => array_values($last6years),
        ];

        // Return the chartdata array
        return $chartdata;
    }

    /**
     * Retrieve the array that compares 12 months to the previous 12 months
     *
     * @return array
     */
    public function cacheComparingPreviousYear()
    {
        // Retrieve the last 12 months and the transactions within that range
        list($last12months, $range) = \DateHelper::getLastMonths(12);
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

        // Retrieve the previous 12 months and the transactions within that range
        list($previous12months, $range) = \DateHelper::getLastMonths(12, 12);
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

        // Loop through the last 12 months to create the chart labels
        foreach ($last12months as $date => $amount) {
            $date = explode(".", $date);
            $chartdata['labels'][] = \Carbon\Carbon::create($date[0], $date[1], 1, 0, 0, 0)->format('F');
        }

        foreach ($last12months as &$month) {
            // Mutate the amounts to decimals
            $month /= pow(10, $this->currency->decimals);
        }

        foreach ($previous12months as &$month) {
            // Mutate the amounts to decimals
            $month /= pow(10, $this->currency->decimals);
        }

        // Create the chartdata array
        $chartdata['datasets'] = [];

        // Add the data from the last 12 months to it
        $chartdata['datasets'][] = [
            'label' => 'Laatste 12 maanden',
            'data'  => array_values($last12months),
        ];

        // Add the data from the previous 12 months to it
        $chartdata['datasets'][] = [
            'label' => 'Vorige 12 maanden',
            'data'  => array_values($previous12months),
        ];

        // Return the chartdata
        return $chartdata;
    }
}
