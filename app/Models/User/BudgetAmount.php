<?php
/**
 * BudgetAmount model
 *
 * Stores the budget for a specific month
 *     and year combination
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BudgetAmount
 */
class BudgetAmount extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_budget_amounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_budget_id',
        'currency_id',
        'amount',
        'year',
        'month',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'currency_id' => 'integer',
        'amount' => 'integer',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Returns the currency
     *
     * @return \App\Models\Currency
     */
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }
}
