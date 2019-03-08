<?php
/**
 * Transaction model
 *
 * The actual transaction model containing all
 *     information about the transaction.
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 */
class Transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'user_account_id',
        'user_import_id',

        'user_category_id',
        'user_budget_id',
        
        'currency_id',

        'book_date',
        'type',
        'dw',

        'description',
        'reference',

        'contra_account',
        'contra_account_hash',
        'contra_account_name',

        'amount',

        'duplicate_hash',
        'duplicate',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'user_account_id' => 'integer',
        'user_import_id' => 'integer',

        'user_category_id' => 'integer',
        'user_budget_id' => 'integer',

        'currency_id' => 'integer',

        'book_date' => 'date',
        
        'amount' => 'integer',

        'duplicate' => 'boolean',
    ];

    /**
     * Retrieve the description attribute
     *
     * @param string $value
     * @return string|null
     */
    public function getDescriptionAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // If $value is not null unseal it and return it, else return null
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
        // If the $value is not null, seal and save it, else save null
        $this->attributes['description'] =
            ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

    /**
     * Retrieve the reference attribute
     *
     * @param string $value
     * @return string|null
     */
    public function getReferenceAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // If $value is not null unseal it and return it, else return null
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
    }

    /**
     * Save the reference attribute
     *
     * @param string $value
     * @return void
     */
    public function setReferenceAttribute($value)
    {
        // If the $value is not null, seal and save it, else save null
        $this->attributes['reference'] =
            ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

    /**
     * Retrieve the contra_account attribute
     *
     * @param string $value
     * @return string|null
     */
    public function getContraAccountAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // If $value is not null unseal it and return it, else return null
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
    }

    /**
     * Save the contra_account attribute
     *
     * @param string $value
     * @return void
     */
    public function setContraAccountAttribute($value)
    {
        // If the $value is not null, seal and save it, else save null
        $this->attributes['contra_account'] =
            ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

    /**
     * Retrieve the contra_account_name attribute
     *
     * @param string $value
     * @return string|null
     */
    public function getContraAccountNameAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // If $value is not null unseal it and return it, else return null
        return ($value !== null) ? \EncryptionHelper::unseal(session('secretkey'), $value) : null;
    }

    /**
     * Save the contra_account_name attribute
     *
     * @param string $value
     * @return void
     */
    public function setContraAccountNameAttribute($value)
    {
        // If the $value is not null, seal and save it, else save null
        $this->attributes['contra_account_name'] =
            ($value !== null) ? \EncryptionHelper::seal($this->user->publickey, $value) : null;
    }

    public function original()
    {
        return $this->belongsTo('App\Models\User\Transaction', 'duplicate_hash', 'duplicate_hash')
                    ->with(['currency', 'account']);
    }

    /**
     * Returns the user that imported the transaction
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Returns the bank account the transaction
     *     is linked too
     *
     * @return \App\Models\User\Account
     */
    public function account()
    {
        return $this->belongsTo('App\Models\User\Account', 'user_account_id');
    }

    /**
     * Returns the transaction currency
     *
     * @return \App\Models\Currency
     */
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    /**
     * Returns the budget this transaction
     *     is linked too (can be null if the
     *     transaction isn't linked to a
     *     budget)
     *
     * @return \App\Models\User\Budget|null
     */
    public function budget()
    {
        return $this->belongsTo('App\Models\User\Budget', 'user_budget_id');
    }

    /**
     * Returns the category this transaction
     *     is linked too (can be null if the
     *     transactaction isn't linked to a
     *     category)
     *
     * @return \App\Models\User\Category|null
     */
    public function category()
    {
        return $this->belongsTo('App\Models\User\Category', 'user_category_id');
    }

    /**
     * If the current transaction is not a
     *     duplicate, get all transactions
     *     that are a duplicate of this
     *     transacties
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getDuplicatesAttribute()
    {
        if ($this->duplicate === false) {
            return $this->user->transactions()
                              ->where('duplicate_hash', $this->duplicate_hash)
                              ->where('duplicate', true)
                              ->get();
        }

        return null;
    }
}
