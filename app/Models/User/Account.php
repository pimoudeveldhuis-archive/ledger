<?php
/**
 * Account model
 *
 * Basic model for bank accounts. It contains
 *     the user that owns it, the bank the account
 *     is registered, the account number (in IBAN
 *     format), a hash of the account number to be
 *     able to search for it and a name and description
 *     that are picked by the user.
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Account
 */
class Account extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'bank_id',
        'account',
        'account_hash',
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'bank_id' => 'integer',
    ];

    /**
     * Retrieve the account attribute
     *
     * @param string $value
     * @return string
     */
    public function getAccountAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
    }

    /**
     * Save the account attribute
     *
     * @param string $value
     * @return void
     */
    public function setAccountAttribute($value)
    {
        // Seal $value and save it
        $this->attributes['account'] =
            ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

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
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
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
        $this->attributes['name'] = ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
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
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
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
        $this->attributes['description'] =
            ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

    /**
     * Returns the transactions that belong to the account
     *
     * @return \Illuminate\Support\Collection
     */
    public function transactions()
    {
        return $this->hasMany('App\Models\User\Transaction', 'user_account_id');
    }

    /**
     * Return the user that own the account
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Updates all account level caches
     *
     * @return void
     */
    public function cache()
    {
        $this->user->updateCache('account-'. $this->id.'-home', self::cacheHome());
    }

    /**
     * Update the home cache
     *
     * @return array
     */
    public function cacheHome()
    {
        // Get all deposits and withdrawals from the previous month, and the avarage of the 12 past months
        $home = [
            'previousMonth' => [
                'deposit' =>
                    (int) $this->transactions()
                               ->where('dw', 'deposit')
                               ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                               ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                               ->sum('amount'),
                'withdrawal' =>
                    (int) $this->transactions()
                               ->where('dw', 'withdrawal')
                               ->whereYear('book_date', \Carbon\Carbon::now()->subMonth()->year)
                               ->whereMonth('book_date', \Carbon\Carbon::now()->subMonth()->month)
                               ->sum('amount'),
            ],

            'previous12months' => [
                'deposit' =>
                    (int) round(
                        $this->transactions()
                             ->where('dw', 'deposit')
                             ->whereBetween('book_date', [
                                 \Carbon\Carbon::now()->subMonths(12)->startOfMonth(),
                                 \Carbon\Carbon::now()->startOfMonth()->subSecond()
                             ])->sum('amount')
                    ),
                'withdrawal' =>
                    (int) round(
                        $this->transactions()
                             ->where('dw', 'withdrawal')
                             ->whereBetween('book_date', [
                                 \Carbon\Carbon::now()->subMonths(12)->startOfMonth(),
                                 \Carbon\Carbon::now()->startOfMonth()->subSecond()
                             ])->sum('amount')
                    ),
            ],
        ];

        // Calculate the result of both the previous month and 12 month average
        $home['previousMonth']['result'] =
            $home['previousMonth']['deposit'] - $home['previousMonth']['withdrawal'];
        
        $home['previous12months']['result'] =
            $home['previous12months']['deposit'] - $home['previous12months']['withdrawal'];

        return $home;
    }
}
